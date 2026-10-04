#!/usr/bin/env node
/**
 * WCAG AA contrast of every text color the components use (`text-{token}` or
 * `text-{token}/{opacity}`), blended over the backgrounds of the tone it is meant for:
 * light sections (moonlight) for night/horizon text, dark sections (night, night-blue)
 * for moonlight/beam/car text. Run: `npm run contrast`. Exits 1 when a pair fails.
 *
 * Tokens come from resources/css/tokens.css; opacity changes are the only fix allowed
 * (CLAUDE.md: no new colors).
 */
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';

const AA_NORMAL = 4.5;

const tokens = Object.fromEntries(
    [...readFileSync('resources/css/tokens.css', 'utf8').matchAll(/--color-([a-z-]+):\s*(#[0-9a-f]{6})/gi)].map(([, name, hex]) => [
        name,
        hex,
    ]),
);

/** Which section backgrounds each text token is designed for. */
const BACKGROUNDS = {
    night: ['moonlight'],
    horizon: ['moonlight'],
    'night-blue': ['moonlight'],
    moonlight: ['night', 'night-blue'],
    'beam-glow': ['night', 'night-blue'],
    beam: ['night', 'night-blue'],
    car: ['night', 'night-blue'],
};

/** Usages reviewed by hand that are not text a person needs to read (decorative, disabled, placeholder). */
const EXEMPT = new Map([
    // Large (text-2xl) decorative "@" before the nickname input: passes the 3:1 large-text rule.
    ['text-moonlight/40', 'decorative "@" prefix in large type'],
]);

const rgb = (hex) => [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16));
const blend = (fg, bg, alpha) => fg.map((c, i) => Math.round(c * alpha + bg[i] * (1 - alpha)));
const luminance = (c) =>
    c
        .map((v) => v / 255)
        .map((v) => (v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4))
        .reduce((sum, v, i) => sum + v * [0.2126, 0.7152, 0.0722][i], 0);
const ratio = (a, b) => {
    const [l1, l2] = [luminance(a), luminance(b)].sort((x, y) => y - x);
    return (l1 + 0.05) / (l2 + 0.05);
};

function files(dir) {
    return readdirSync(dir).flatMap((name) => {
        const path = join(dir, name);
        return statSync(path).isDirectory() ? files(path) : path.endsWith('.tsx') ? [path] : [];
    });
}

const used = new Map();
for (const file of files('resources/js')) {
    if (file.includes(`${join('Pages', 'Panel')}`) || file.endsWith('.test.tsx')) continue;
    for (const [cls, token, opacity] of readFileSync(file, 'utf8').matchAll(/\btext-(night-blue|night|moonlight|horizon|beam-glow|beam|car)(?:\/(\d+))?\b/g)) {
        if (!used.has(cls)) used.set(cls, { token, alpha: opacity ? Number(opacity) / 100 : 1, files: new Set() });
        used.get(cls).files.add(file);
    }
}

let failures = 0;
const rows = [];
for (const [cls, { token, alpha, files: where }] of [...used].sort()) {
    for (const bgName of BACKGROUNDS[token]) {
        const bg = rgb(tokens[bgName]);
        const value = ratio(blend(rgb(tokens[token]), bg, alpha), bg);
        const exempt = EXEMPT.get(cls);
        const pass = value >= AA_NORMAL;
        if (!pass && !exempt) failures++;
        rows.push(`${pass ? 'ok  ' : exempt ? 'skip' : 'FAIL'}  ${cls.padEnd(22)} on ${bgName.padEnd(10)} ${value.toFixed(2)}:1${exempt && !pass ? `  (${exempt})` : ''}${!pass && !exempt ? `  ← ${[...where].slice(0, 3).join(', ')}` : ''}`);
    }
}

console.log(rows.join('\n'));
console.log(failures === 0 ? '\nTodos os pares de texto passam em AA (4,5:1).' : `\n${failures} par(es) abaixo de AA.`);
process.exit(failures === 0 ? 0 : 1);
