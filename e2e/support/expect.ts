import { expect as baseExpect } from '@playwright/test';

/**
 * The flows go through PHP's single-threaded built-in server, which also runs the sync jobs
 * (photo processing, reverse geocoding, mail to the log) inside the request. On a busy machine one
 * round trip can take well over the default 5 s, so the flows wait longer for what the server answers.
 */
export const expect = baseExpect.configure({ timeout: 20_000 });

/** Test timeout for a whole flow (several members, several server round trips). */
export const FLOW_TIMEOUT = 240_000;
