import { t } from '@/i18n/pt-BR';

export const primaryLinks = [
    { href: '/regiao', label: t.nav.region },
    { href: '/o-lugar', label: t.nav.place },
    { href: '/mapa', label: t.nav.logbook },
    { href: '/loja', label: t.nav.store },
] as const;

export const allLinks = [
    ...primaryLinks,
    { href: '/relatar', label: 'Relatar avistamento' },
    { href: '/lenda', label: 'A lenda' },
    { href: '/apoie', label: 'Apoie a pista' },
    { href: '/obra', label: 'Diário da obra' },
    { href: '/comunidade', label: 'Comunidade' },
    { href: '/faq', label: 'Perguntas frequentes' },
] as const;

export const JOIN_HREF = '/comunidade';
