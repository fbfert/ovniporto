/**
 * Funnel events for the cookieless metric (self-hosted Umami). The script is only
 * printed in production (resources/views/partials/analytics.blade.php); everywhere
 * else `window.umami` is missing and tracking does nothing.
 * Never send personal data here: no e-mail, nickname, address or order number.
 */
export type AnalyticsEvent =
    | 'entrar_comunidade'
    | 'relatar_iniciado'
    | 'relatar_enviado'
    | 'adicionar_carrinho'
    | 'compra_concluida'
    | 'avise_me';

type EventData = Record<string, string | number>;

declare global {
    interface Window {
        umami?: { track: (event: string, data?: EventData) => void };
    }
}

export function track(event: AnalyticsEvent, data?: EventData): void {
    if (typeof window === 'undefined') return;
    try {
        window.umami?.track(event, data);
    } catch {
        // A metric must never break the page.
    }
}
