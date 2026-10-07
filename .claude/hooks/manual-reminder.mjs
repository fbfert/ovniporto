#!/usr/bin/env node
/**
 * Keeps the panel's operations manual (resources/content/manual) in step with the code.
 *
 * PostToolUse (Edit|Write|MultiEdit): when a file that shapes the panel changes, tell Claude which
 * chapter probably needs updating and remember it as pending for this session. Editing that
 * chapter's JSON clears it.
 * Stop: if chapters are still pending, block the stop once and list them, so a turn does not end
 * with panel code changed and the manual untouched. The second stop always goes through
 * (stop_hook_active), so a change that really needs no manual edit is never stuck.
 *
 * Plain Node, no dependencies: runs the same from Git Bash and PowerShell.
 */
import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, relative } from 'node:path';
import { pathToFileURL } from 'node:url';

const MANUAL_DIR = 'resources/content/manual/';

/** Panel controllers → chapter. */
const CONTROLLERS = {
    SightingModeration: ['relatos'],
    MemberAdmin: ['membros'],
    OrderAdmin: ['pedidos'],
    ProductAdmin: ['produtos'],
    ContentHub: ['conteudo'],
    Settings: ['conteudo'],
    PlaceAdmin: ['lugar-e-obra'],
    RegionAdmin: ['regiao'],
    CampaignAdmin: ['campanha'],
    CollaboratorAdmin: ['campanha'],
    Audit: ['auditoria'],
    PanelHome: ['inicio'],
    Manual: ['primeiros-passos'],
};

/** Panel pages (resources/js/Pages/Panel/...) → chapter. */
const PAGES = {
    Home: ['inicio'],
    Sightings: ['relatos'],
    Members: ['membros'],
    Orders: ['pedidos'],
    Products: ['produtos'],
    Audit: ['auditoria'],
    'Content/Hub': ['conteudo'],
    'Content/Settings': ['conteudo'],
    'Content/Place': ['lugar-e-obra'],
    'Content/Diary': ['lugar-e-obra'],
    'Content/DiaryPost': ['lugar-e-obra'],
    'Content/Region': ['regiao'],
    'Content/RegionPartner': ['regiao'],
    'Content/Campaign': ['campanha'],
    'Content/Waitlist': ['campanha'],
    'Content/Collaborators': ['campanha'],
};

/** Application / Domain modules → chapters whose rules they hold. */
const MODULES = {
    Sightings: ['relatos'],
    Members: ['membros', 'papeis'],
    Orders: ['pedidos'],
    Payments: ['pedidos', 'problemas'],
    Shipping: ['pedidos', 'problemas'],
    Catalog: ['produtos'],
    Content: ['conteudo'],
    Place: ['lugar-e-obra'],
    Region: ['regiao'],
    Campaign: ['campanha'],
    Origin: ['campanha'],
    Panel: ['inicio', 'papeis'],
    Audit: ['auditoria'],
    Privacy: ['privacidade'],
};

/** Other files that shape what operators see or do. */
const FILES = [
    [/^routes\/web\.php$/, ['(o capítulo da área da rota alterada)']],
    [/^routes\/console\.php$/, ['rotina', 'monitoramento']],
    [/^app\/Console\/Commands\//, ['rotina', 'monitoramento']],
    [/^app\/Mail\//, ['(o capítulo da área que envia este e-mail)']],
    [/^app\/Infrastructure\/Monitoring\//, ['monitoramento']],
    [/^config\/(horizon|pulse)\.php$/, ['monitoramento']],
    [/^DEPLOY\.md$/, ['primeiros-passos', 'monitoramento']],
    [/^resources\/js\/Layouts\/PanelLayout\.tsx$/, ['primeiros-passos']],
    [/^resources\/js\/i18n\/pt-BR\.ts$/, ['(os capítulos que citam em negrito o texto alterado)']],
];

const unique = (list) => [...new Set(list)];

/** Repo-relative, forward-slash path. */
export function normalize(filePath, cwd) {
    const rel = cwd && /^([a-zA-Z]:)?[\\/]/.test(filePath) ? relative(cwd, filePath) : filePath;
    return rel.replace(/\\/g, '/').replace(/^\.\//, '');
}

/** The manual chapter a file is, if it is one. */
export function chapterFile(path) {
    return path.startsWith(MANUAL_DIR) && path.endsWith('.json') ? path.slice(MANUAL_DIR.length, -5) : null;
}

/** Chapters that probably explain what this file changes ([] when the panel is not affected). */
export function chaptersFor(path) {
    if (path.startsWith(MANUAL_DIR) || /(^|\/)tests?\//.test(path) || /\.test\.(ts|tsx|mjs)$/.test(path)) return [];

    const controller = path.match(/^app\/Http\/Controllers\/Panel\/(\w+)Controller\.php$/);
    if (controller) return CONTROLLERS[controller[1]] ?? ['(o capítulo da área deste controller)'];

    const page = path.match(/^resources\/js\/Pages\/Panel\/(.+)\.tsx$/);
    if (page) {
        if (page[1].startsWith('Manual/')) return [];
        const key = Object.keys(PAGES).find((k) => page[1] === k || page[1].startsWith(`${k}/`));
        return key ? PAGES[key] : ['(o capítulo da área desta tela)'];
    }

    if (/^resources\/js\/Components\/Panel\//.test(path) && !/MindMap|mindMapLayout|reviewedOn/.test(path)) {
        return ['(os capítulos das telas que usam este componente)'];
    }

    const module = path.match(/^app\/(Application|Domain)\/(\w+)\//);
    if (module) {
        if (module[2] === 'Manual') return [];
        if (path === 'app/Domain/Panel/PanelArea.php') return ['papeis', 'primeiros-passos'];
        return MODULES[module[2]] ?? [];
    }

    for (const [pattern, chapters] of FILES) if (pattern.test(path)) return chapters;
    return [];
}

/** The message Claude reads after an edit. */
export function reminder(path, chapters) {
    const list = chapters.map((c) => (c.startsWith('(') ? c.slice(1, -1) : `resources/content/manual/${c}.json`));
    return [
        `Manual do painel: ${path} mudou algo que operadores veem ou fazem.`,
        `Atualize ${list.join(', ')} se o texto, o mapa ou as rotas citadas mudaram, e troque o "reviewedAt" para a data de hoje.`,
        'Escreva a partir do código real (sem inventar) e rode `php artisan test --filter=PanelManual`.',
    ].join(' ');
}

const statePath = (sessionId) => join(tmpdir(), `ovniporto-manual-${String(sessionId || 'default').replace(/[^\w-]/g, '')}.json`);

function readState(sessionId) {
    try {
        return JSON.parse(readFileSync(statePath(sessionId), 'utf8'));
    } catch {
        return { pending: {} };
    }
}

const saveState = (sessionId, state) => writeFileSync(statePath(sessionId), JSON.stringify(state));

/** PostToolUse: returns the hook output (or null) and updates the pending list. */
export function onEdit(input, state) {
    const raw = input.tool_input?.file_path ?? input.tool_input?.path;
    if (!raw) return null;
    const path = normalize(raw, input.cwd);

    const edited = chapterFile(path);
    if (edited) {
        // That chapter is done; reminders that named no chapter are taken as handled by any manual edit.
        for (const chapter of Object.keys(state.pending)) {
            if (chapter === edited || chapter.startsWith('(')) delete state.pending[chapter];
        }
        return null;
    }

    const chapters = chaptersFor(path);
    if (chapters.length === 0) return null;
    for (const chapter of chapters) state.pending[chapter] = unique([...(state.pending[chapter] ?? []), path]);
    return {
        hookSpecificOutput: { hookEventName: 'PostToolUse', additionalContext: reminder(path, chapters) },
    };
}

/** Stop: block once while concrete chapters are pending. */
export function onStop(input, state) {
    if (input.stop_hook_active) {
        state.pending = {};
        return null;
    }
    const named = Object.keys(state.pending).filter((c) => !c.startsWith('('));
    const vague = Object.keys(state.pending).filter((c) => c.startsWith('('));
    if (named.length === 0 && vague.length === 0) return null;
    const lines = [...named.map((c) => `- ${c}: por causa de ${state.pending[c].join(', ')}`), ...vague.map((c) => `- ${c.slice(1, -1)}: ${state.pending[c].join(', ')}`)];
    state.pending = {};
    return {
        decision: 'block',
        reason: [
            'O painel mudou e o manual de operação ainda não foi revisto nesta sessão:',
            ...lines,
            'Atualize os capítulos em resources/content/manual (texto, mapa, rotas e reviewedAt) ou, se nada do que operadores veem mudou, diga isso ao usuário em uma linha.',
        ].join('\n'),
    };
}

async function main() {
    let raw = '';
    for await (const chunk of process.stdin) raw += chunk;
    // PowerShell pipes a byte-order mark first.
    raw = raw.replace(/^﻿/, '');
    const input = raw.trim() ? JSON.parse(raw) : {};
    const state = readState(input.session_id);
    const output = input.hook_event_name === 'Stop' ? onStop(input, state) : onEdit(input, state);
    saveState(input.session_id, state);
    if (output) process.stdout.write(JSON.stringify(output));
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href && existsSync(process.argv[1])) {
    main().catch((error) => {
        // A broken reminder must never break the edit it follows.
        process.stderr.write(`manual-reminder: ${error.message}\n`);
        process.exit(0);
    });
}
