import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

/**
 * Short fact, like a field on a boarding pass: tiny tracked label above a value.
 * One name/value group of a <dl> (a <div> holding <dt> and <dd>, as HTML allows);
 * with `href`, the value is the link, so the list structure stays valid.
 */
export function InfoCard({
    label,
    value,
    href,
    extra,
    icon,
    className = '',
}: {
    label: string;
    value: ReactNode;
    href?: string;
    extra?: ReactNode;
    /** Small icon beside the label (decorative). */
    icon?: ReactNode;
    className?: string;
}) {
    return (
        <div className={className}>
            <dt className="flex items-center gap-1.5 text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase">
                {icon && <span className="text-horizon">{icon}</span>}
                {label}
            </dt>
            <dd className="mt-1.5 flex flex-wrap items-center gap-2 text-base leading-snug font-semibold">
                {href ? (
                    <Link href={href} className="rounded-lg underline-offset-4 hover:underline">
                        {value}
                    </Link>
                ) : (
                    value
                )}
                {extra}
            </dd>
        </div>
    );
}
