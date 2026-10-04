import { resolve } from 'node:path';

/** The e2e server: PHP's built-in server with its own SQLite file, synchronous queue and fakes. */
export const E2E_PORT = 8091;
export const E2E_URL = `http://127.0.0.1:${E2E_PORT}`;

/**
 * Process variables win over .env (immutable dotenv), so the suite never touches the dev database.
 * APP_ENV=local keeps the dev sign-in route and the simulated payment/shipping providers.
 */
export const E2E_ENV: Record<string, string> = {
    APP_ENV: 'local',
    APP_DEBUG: 'true',
    APP_URL: E2E_URL,
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: resolve('database/e2e.sqlite'),
    // The cache lives in the e2e database: isolated from development, and as strict as production
    // about what it unserializes.
    CACHE_STORE: 'database',
    SESSION_DRIVER: 'file',
    QUEUE_CONNECTION: 'sync',
    MAIL_MAILER: 'log',
    INERTIA_SSR_ENABLED: 'false',
    INERTIA_DEVTOOLS_ENABLED: 'false',
    PAYPAL_CLIENT_ID: '',
    MELHOR_ENVIO_TOKEN: '',
    GEOCODER: 'offline',
};
