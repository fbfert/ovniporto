import { execFileSync } from 'node:child_process';
import { writeFileSync } from 'node:fs';
import { E2E_ENV } from './support/env';

/** A fresh database with the base seeds, the demo content and the e2e members. */
export default function globalSetup(): void {
    writeFileSync(E2E_ENV.DB_DATABASE as string, '');
    const env = { ...process.env, ...E2E_ENV };
    const artisan = (...args: string[]) => execFileSync('php', ['artisan', ...args], { env, stdio: 'inherit' });

    artisan('migrate:fresh', '--force', '--seed');
    artisan('dev:seed-demo');
    artisan('db:seed', '--class=E2eSeeder', '--force');
    artisan('cache:clear');
}
