import { useId } from 'react';
import { shortDate, t } from '@/i18n/pt-BR';

/** Rubber stamp in beam green, slightly crooked: "APROVADO PELA TORRE" with the publication date. */
export function TowerStamp({ date, className = '' }: { date: string; className?: string }) {
    const path = `stamp-${useId().replace(/:/g, '')}`;
    return (
        <svg
            viewBox="0 0 160 160"
            role="img"
            aria-label={`${t.sightingPage.stamp}, ${shortDate(date)}`}
            className={`size-36 -rotate-12 text-beam ${className}`}
        >
            <defs>
                <path id={path} d="M 80 80 m -58 0 a 58 58 0 1 1 116 0 a 58 58 0 1 1 -116 0" />
            </defs>
            <circle cx="80" cy="80" r="74" fill="none" stroke="currentColor" strokeWidth="4" />
            <circle cx="80" cy="80" r="44" fill="none" stroke="currentColor" strokeWidth="2" strokeDasharray="4 4" />
            <text
                fill="currentColor"
                fontFamily="var(--font-display)"
                fontWeight="800"
                fontSize="13"
                letterSpacing="2.5"
            >
                <textPath href={`#${path}`} startOffset="2%">
                    {t.sightingPage.stamp.toUpperCase()} ★ {t.sightingPage.stamp.toUpperCase()} ★
                </textPath>
            </text>
            <text
                x="80"
                y="76"
                textAnchor="middle"
                fill="currentColor"
                fontFamily="var(--font-display)"
                fontWeight="800"
                fontSize="15"
            >
                OK
            </text>
            <text
                x="80"
                y="96"
                textAnchor="middle"
                fill="currentColor"
                fontFamily="var(--font-sans)"
                fontWeight="600"
                fontSize="11"
            >
                {shortDate(date)}
            </text>
        </svg>
    );
}
