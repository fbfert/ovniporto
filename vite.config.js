import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    ssr: {
        noExternal: ['motion', 'lenis'],
    },
    build: {
        rolldownOptions: {
            output: {
                codeSplitting: {
                    // What every public page loads (layout, primitives, icons, hooks, copy) travels as one
                    // file instead of ~20 tiny ones: fewer requests on a weak 4G signal. Page-specific
                    // components and the map (dynamic import) keep their own chunks.
                    groups: [
                        {
                            name: 'ui',
                            test: /resources[\\/]js[\\/](Components[\\/](Ui|Layout|Brand|Scene|Icons|Content|Members|Store)|hooks|lib|i18n)[\\/]/,
                        },
                    ],
                },
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**', '**/.claude/**'],
        },
    },
});
