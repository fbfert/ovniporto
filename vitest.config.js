import react from '@vitejs/plugin-react';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vitest/config';

// Component tests for resources/js (jsdom). Kept apart from vite.config.js so the
// Laravel plugin and Tailwind don't load for tests.
export default defineConfig({
    plugins: [react()],
    resolve: {
        alias: { '@': fileURLToPath(new URL('./resources/js', import.meta.url)) },
    },
    test: {
        environment: 'jsdom',
        // The manual reminder hook (.claude/hooks) is plain Node, tested here too.
        include: ['resources/js/**/*.test.{ts,tsx}', '.claude/hooks/**/*.test.mjs'],
        restoreMocks: true,
        setupFiles: ['resources/js/test/setup.ts'],
    },
});
