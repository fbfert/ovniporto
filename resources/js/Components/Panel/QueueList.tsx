import { Link } from '@inertiajs/react';
import { useRef } from 'react';
import { useShortcuts } from '@/hooks/useShortcuts';
import { shortDate, t } from '@/i18n/pt-BR';

const copy = t.panel.queue;

export interface QueueItem {
    id: number;
    type: string;
    nickname: string;
    observedDate: string;
    city: string | null;
    waitingHours: number;
    thumb: string | null;
    photoCount: number;
}

/**
 * The moderation queue as a list of links. J and K move the focus down and up
 * (Enter opens, as on any link); the focused row is outlined in beam green.
 */
export function QueueList({ items, overdueHours = 48 }: { items: QueueItem[]; overdueHours?: number }) {
    const links = useRef<Array<HTMLAnchorElement | null>>([]);

    const move = (step: 1 | -1) => {
        const current = links.current.findIndex((link) => link !== null && link === document.activeElement);
        const next = current === -1 ? (step === 1 ? 0 : items.length - 1) : current + step;
        links.current[Math.max(0, Math.min(items.length - 1, next))]?.focus();
    };

    useShortcuts({ j: () => move(1), k: () => move(-1) }, items.length > 0);

    return (
        <ol className="divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
            {items.map((item, i) => (
                <li key={item.id}>
                    <Link
                        href={`/painel/relatos/${item.id}`}
                        ref={(el: HTMLAnchorElement | null) => {
                            links.current[i] = el;
                        }}
                        className="group grid grid-cols-[4.5rem_minmax(0,1fr)] items-center gap-4 rounded-2xl px-2 py-4 outline-none hover:bg-night/4 focus-visible:bg-beam/10 focus-visible:ring-2 focus-visible:ring-beam sm:grid-cols-[4.5rem_minmax(0,1fr)_auto]"
                    >
                        <span className="relative block size-[4.5rem] overflow-hidden rounded-xl bg-night">
                            {item.thumb ? (
                                <img src={item.thumb} alt="" loading="lazy" className="size-full object-cover" />
                            ) : (
                                <span className="flex size-full items-center justify-center text-[0.65rem] font-semibold tracking-[0.1em] text-moonlight/55 uppercase">
                                    {copy.noPhoto}
                                </span>
                            )}
                        </span>
                        <span className="min-w-0">
                            <span className="flex flex-wrap items-baseline gap-x-2">
                                <span className="font-display text-base font-bold tracking-[0.03em] uppercase">
                                    {t.logbook.types[item.type as keyof typeof t.logbook.types] ?? item.type}
                                </span>
                                <span className="font-semibold text-horizon">@{item.nickname}</span>
                            </span>
                            <span className="mt-1 block truncate text-sm text-night/65">
                                {copy.observed} {shortDate(item.observedDate)} · {item.city ?? copy.cityUnknown}
                                {item.photoCount > 0 && ` · ${copy.photos(item.photoCount)}`}
                            </span>
                        </span>
                        <span
                            className={`col-start-2 justify-self-start text-sm font-semibold sm:col-start-auto ${item.waitingHours > overdueHours ? 'rounded-full bg-car px-3 py-1 text-night' : 'text-night/60'}`}
                        >
                            {t.panel.since(item.waitingHours)}
                        </span>
                    </Link>
                </li>
            ))}
        </ol>
    );
}
