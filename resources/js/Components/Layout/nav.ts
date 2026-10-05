import { t } from '@/i18n/pt-BR';

export const primaryLinks = [
    { href: '/regiao', label: t.nav.region },
    { href: '/o-lugar', label: t.nav.place },
    { href: '/mapa', label: t.nav.logbook },
    { href: '/loja', label: t.nav.store },
] as const;

export const allLinks = [
    ...primaryLinks,
    { href: '/relatar', label: t.nav.report },
    { href: '/origem', label: t.nav.origin },
    { href: '/apoie', label: t.nav.support },
    { href: '/obra', label: t.nav.diary },
    { href: '/comunidade', label: t.nav.community },
    { href: '/faq', label: t.nav.faq },
] as const;

/** Full-page sign-in, for places where a modal would stack on another overlay (mobile menu). */
export const JOIN_HREF = '/entrar';
