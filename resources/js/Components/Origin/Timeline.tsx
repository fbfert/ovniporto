import type { ReactNode } from 'react';

export interface TimelineItem {
    when: string;
    title: string;
    body?: ReactNode;
}

/**
 * A vertical timeline: the year in outlined display type (like the 01/02/03 blocks), a beam-green
 * dot on the line, then the event. The line and dots are decoration; the order is the content.
 */
export function Timeline({ items, tone = 'light' }: { items: TimelineItem[]; tone?: 'light' | 'dark' }) {
    const line = tone === 'dark' ? 'border-moonlight/20' : 'border-night/15';
    const body = tone === 'dark' ? 'text-moonlight/75' : 'text-night/75';

    return (
        <ol className={`relative ml-2 border-l-2 border-dashed ${line}`}>
            {items.map((item) => (
                <li key={`${item.when}-${item.title}`} className="relative pb-9 pl-8 last:pb-0">
                    <span
                        aria-hidden
                        className="absolute top-2 -left-[7px] size-3 rounded-full bg-beam shadow-[0_0_10px_2px_rgb(84_201_51/0.55)]"
                    />
                    {/* Years in outlined display type; phrases ("data não localizada") stay plain and readable. */}
                    {/^[\d\s–-]{2,12}$/.test(item.when) ? (
                        <p className="font-display text-[clamp(1.5rem,1rem+1.6vw,2.2rem)] leading-none font-extrabold text-transparent [-webkit-text-stroke:1.5px_var(--color-beam)]">
                            {item.when}
                        </p>
                    ) : (
                        <p className="font-display text-[0.95rem] leading-tight font-bold tracking-[0.04em] text-beam uppercase">
                            {item.when}
                        </p>
                    )}
                    <h3 className="mt-2 font-display text-base leading-tight font-bold tracking-[0.03em] uppercase">
                        {item.title}
                    </h3>
                    {item.body && <div className={`mt-1.5 max-w-[60ch] leading-relaxed ${body}`}>{item.body}</div>}
                </li>
            ))}
        </ol>
    );
}
