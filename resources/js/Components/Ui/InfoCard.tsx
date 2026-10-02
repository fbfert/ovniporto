import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

/** Short fact, like a field on a boarding pass: tiny tracked label above a value. */
export function InfoCard({
    label,
    value,
    href,
    extra,
    icon,
}: {
    label: string;
    value: ReactNode;
    href?: string;
    extra?: ReactNode;
    /** Small icon beside the label (decorative). */
    icon?: ReactNode;
}) {
    const inner = (
        <>
            <dt className="flex items-center gap-1.5 text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase">
                {icon && <span className="text-horizon">{icon}</span>}
                {label}
            </dt>
            <dd className="mt-1.5 flex flex-wrap items-center gap-2 text-base leading-snug font-semibold">
                {value}
                {extra}
            </dd>
        </>
    );
    return href ? (
        <Link href={href} className="block rounded-lg underline-offset-4 hover:underline">
            {inner}
        </Link>
    ) : (
        <div>{inner}</div>
    );
}
