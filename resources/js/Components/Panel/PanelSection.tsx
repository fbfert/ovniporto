import type { ReactNode } from 'react';

/** A titled block of the panel's work surface. */
export function PanelSection({
    title,
    action,
    children,
    className = '',
}: {
    title: string;
    action?: ReactNode;
    children: ReactNode;
    className?: string;
}) {
    return (
        <section className={`rounded-[22px] bg-night/5 p-5 ring-1 ring-night/10 sm:p-6 ${className}`}>
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h2 className="font-display text-sm font-bold tracking-[0.06em] uppercase">{title}</h2>
                {action}
            </div>
            <div className="mt-4">{children}</div>
        </section>
    );
}

/** Up / down buttons for ordered lists (the server swaps with the neighbour). */
export function MoveButtons({
    onMove,
    first,
    last,
    labels,
}: {
    onMove: (direction: -1 | 1) => void;
    first: boolean;
    last: boolean;
    labels: { up: string; down: string };
}) {
    const button =
        'inline-flex size-11 items-center justify-center rounded-full ring-1 ring-night/20 hover:ring-night/50 disabled:opacity-30';
    return (
        <span className="inline-flex gap-1.5">
            <button type="button" className={button} disabled={first} onClick={() => onMove(-1)} aria-label={labels.up}>
                <svg viewBox="0 0 24 24" className="size-4" fill="none" aria-hidden>
                    <path d="M6 15l6-6 6 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                </svg>
            </button>
            <button type="button" className={button} disabled={last} onClick={() => onMove(1)} aria-label={labels.down}>
                <svg viewBox="0 0 24 24" className="size-4" fill="none" aria-hidden>
                    <path d="M6 9l6 6 6-6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                </svg>
            </button>
        </span>
    );
}
