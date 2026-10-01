import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

/**
 * Admission ticket: art on top, a dashed tear line with punched holes on both
 * edges, then the details. Corner label like a stamped stub.
 */
export function TicketCard({
    href,
    art,
    label,
    title,
    meta,
    price,
    comparePrice,
    className = '',
}: {
    href?: string;
    art: ReactNode;
    label?: string | null;
    title: string;
    meta?: ReactNode;
    price: string;
    comparePrice?: string | null;
    className?: string;
}) {
    const card = (
        <article
            className={`group/ticket ticket-punch relative flex h-full flex-col bg-night-blue text-moonlight transition-transform duration-300 ease-snap [--punch-y:66%] [@media(hover:hover)]:hover:-translate-y-1.5 ${className}`}
            style={{ borderRadius: '18px' }}
        >
            <div className="relative aspect-[4/3] overflow-hidden rounded-t-[18px]">
                {art}
                {label && (
                    <span className="absolute top-4 -right-1 rotate-3 rounded-l-full bg-car px-3 py-1 font-display text-[0.62rem] font-bold tracking-[0.12em] text-night uppercase shadow-sm">
                        {label}
                    </span>
                )}
            </div>
            <div aria-hidden className="mx-5 border-t-2 border-dashed border-moonlight/25" />
            <div className="flex flex-1 flex-col gap-2 px-6 pt-5 pb-6">
                <h3 className="font-display text-lg leading-tight font-bold tracking-[0.03em] uppercase">{title}</h3>
                {meta && <div className="text-sm text-moonlight/70">{meta}</div>}
                <p className="mt-auto flex items-baseline gap-2 pt-2">
                    <span className="font-display text-2xl font-extrabold text-car">{price}</span>
                    {comparePrice && <s className="text-sm text-moonlight/50">{comparePrice}</s>}
                </p>
            </div>
        </article>
    );

    return href ? (
        <Link href={href} className="block h-full rounded-[18px]">
            {card}
        </Link>
    ) : (
        card
    );
}
