import { defineConfig, devices } from '@playwright/test';
import { E2E_ENV, E2E_PORT, E2E_URL } from './e2e/support/env';

/**
 * End-to-end suite (`make e2e` / `npm run e2e`): runs against the built assets
 * (`npm run build`) on PHP's built-in server with an isolated SQLite database.
 * One worker: the flows share one database and change it on purpose.
 */
export default defineConfig({
    testDir: './e2e',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    timeout: 60_000,
    // The single-threaded PHP server runs jobs inside requests: give assertions and full-page captures time.
    expect: { timeout: 15_000, toHaveScreenshot: { maxDiffPixelRatio: 0.005, animations: 'disabled' } },
    reporter: [['list'], ['html', { open: 'never', outputFolder: 'storage/e2e-report' }]],
    outputDir: 'storage/e2e-results',
    globalSetup: './e2e/global-setup.ts',
    use: {
        baseURL: E2E_URL,
        locale: 'pt-BR',
        timezoneId: 'America/Sao_Paulo',
        trace: 'retain-on-failure',
        reducedMotion: 'reduce',
    },
    projects: [{ name: 'mobile', use: { ...devices['Pixel 7'], viewport: { width: 390, height: 844 } } }],
    webServer: {
        // Laravel's router script expects to run from public/ (what `artisan serve` does).
        command: `php -S 127.0.0.1:${E2E_PORT} ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`,
        cwd: 'public',
        url: `${E2E_URL}/up`,
        env: E2E_ENV,
        reuseExistingServer: false,
        stdout: 'ignore',
        timeout: 60_000,
    },
});
