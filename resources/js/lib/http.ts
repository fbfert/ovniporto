/** Laravel's CSRF cookie, sent back as a header on our own fetch calls. */
export function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1] ?? '') : '';
}

/** A response outside 2xx; `status` 422 means the server refused the input. */
export class HttpError extends Error {
    constructor(
        url: string,
        public readonly status: number,
    ) {
        super(`${url} answered ${status}`);
    }
}

/** JSON in, JSON out, same-origin with the CSRF header. Throws HttpError on a non-2xx answer. */
export async function postJson<T>(url: string, body: Record<string, unknown>): Promise<T> {
    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
        body: JSON.stringify(body),
    });
    if (!response.ok) throw new HttpError(url, response.status);
    return (await response.json()) as T;
}
