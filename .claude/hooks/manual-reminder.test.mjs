import { describe, expect, it } from 'vitest';
import { chaptersFor, normalize, onEdit, onStop } from './manual-reminder.mjs';

const edit = (file_path, cwd = 'C:\\repo') => ({ hook_event_name: 'PostToolUse', cwd, tool_input: { file_path } });

describe('manual reminder', () => {
    it('maps panel files to the chapter that explains them', () => {
        expect(chaptersFor('app/Http/Controllers/Panel/ProductAdminController.php')).toEqual(['produtos']);
        expect(chaptersFor('resources/js/Pages/Panel/Content/RegionPartner.tsx')).toEqual(['regiao']);
        expect(chaptersFor('resources/js/Pages/Panel/Orders/Show.tsx')).toEqual(['pedidos']);
        expect(chaptersFor('app/Application/Sightings/UseCases/ApproveSighting.php')).toEqual(['relatos']);
        expect(chaptersFor('app/Domain/Panel/PanelArea.php')).toEqual(['papeis', 'primeiros-passos']);
        expect(chaptersFor('config/horizon.php')).toEqual(['monitoramento']);
        expect(chaptersFor('routes/web.php')[0]).toMatch(/área da rota/);
    });

    it('stays quiet for the manual itself, tests and unrelated files', () => {
        expect(chaptersFor('resources/content/manual/produtos.json')).toEqual([]);
        expect(chaptersFor('resources/js/Pages/Panel/Manual/Chapter.tsx')).toEqual([]);
        expect(chaptersFor('tests/Feature/StoreOperationsTest.php')).toEqual([]);
        expect(chaptersFor('resources/js/Pages/Store/Product.tsx')).toEqual([]);
        expect(chaptersFor('app/Application/Manual/UseCases/ListManualChapters.php')).toEqual([]);
    });

    it('reads Windows and POSIX absolute paths relative to the project', () => {
        expect(normalize('C:\\repo\\app\\Mail\\OrderShipped.php', 'C:\\repo')).toBe('app/Mail/OrderShipped.php');
        expect(normalize('./routes/web.php', '/repo')).toBe('routes/web.php');
    });

    it('reminds after a panel edit and blocks the stop once until the chapter is touched', () => {
        const state = { pending: {} };

        const output = onEdit(edit('C:\\repo\\app\\Http\\Controllers\\Panel\\OrderAdminController.php'), state);
        expect(output.hookSpecificOutput.hookEventName).toBe('PostToolUse');
        expect(output.hookSpecificOutput.additionalContext).toContain('resources/content/manual/pedidos.json');

        const stop = onStop({ hook_event_name: 'Stop' }, state);
        expect(stop.decision).toBe('block');
        expect(stop.reason).toContain('pedidos: por causa de app/Http/Controllers/Panel/OrderAdminController.php');
        expect(onStop({ hook_event_name: 'Stop' }, state)).toBeNull();
    });

    it('clears a chapter when its file is edited, and never blocks a stop that is already a retry', () => {
        const state = { pending: {} };
        onEdit(edit('C:\\repo\\routes\\web.php'), state);
        onEdit(edit('C:\\repo\\app\\Http\\Controllers\\Panel\\AuditController.php'), state);

        onEdit(edit('C:\\repo\\resources\\content\\manual\\auditoria.json'), state);
        expect(state.pending).toEqual({});

        onEdit(edit('C:\\repo\\app\\Mail\\OrderShipped.php'), state);
        expect(onStop({ hook_event_name: 'Stop', stop_hook_active: true }, state)).toBeNull();
    });
});
