import { shortDate, t } from '@/i18n/pt-BR';

const copy = t.panel.home;

export interface Week {
    week: string;
    sightings: number;
    orders: number;
}

/**
 * Twelve weeks as paired bars: reports in beam green, paid orders in car
 * yellow. Drawn in SVG (no chart library); a hidden table carries the numbers
 * for screen readers.
 */
export function WeeklyChart({ weeks }: { weeks: Week[] }) {
    const max = Math.max(1, ...weeks.flatMap((w) => [w.sightings, w.orders]));
    const height = 140;
    const slot = 40;

    return (
        <figure>
            <svg viewBox={`0 0 ${weeks.length * slot} ${height + 22}`} className="w-full" aria-hidden>
                {[0.5, 1].map((f) => (
                    <line
                        key={f}
                        x1={0}
                        x2={weeks.length * slot}
                        y1={height - height * f}
                        y2={height - height * f}
                        className="stroke-night/10"
                        strokeDasharray="3 4"
                    />
                ))}
                {weeks.map((w, i) => {
                    const hs = (w.sightings / max) * height;
                    const ho = (w.orders / max) * height;
                    return (
                        <g key={w.week} transform={`translate(${i * slot + 8} 0)`}>
                            <rect
                                x={0}
                                y={height - hs}
                                width={11}
                                height={Math.max(hs, w.sightings ? 2 : 0)}
                                rx={3}
                                className="fill-beam"
                            />
                            <rect
                                x={13}
                                y={height - ho}
                                width={11}
                                height={Math.max(ho, w.orders ? 2 : 0)}
                                rx={3}
                                className="fill-car"
                            />
                            {i % 2 === 1 && (
                                <text x={12} y={height + 16} textAnchor="middle" className="fill-night/50 text-[9px]">
                                    {shortDate(w.week)}
                                </text>
                            )}
                        </g>
                    );
                })}
            </svg>
            <figcaption className="mt-3 flex flex-wrap gap-4 text-sm text-night/70">
                <span className="flex items-center gap-2">
                    <span className="size-3 rounded-sm bg-beam" aria-hidden /> {copy.chartSightings}
                </span>
                <span className="flex items-center gap-2">
                    <span className="size-3 rounded-sm bg-car" aria-hidden /> {copy.chartOrders}
                </span>
            </figcaption>
            <table className="sr-only">
                <caption>{copy.chartTitle}</caption>
                <tbody>
                    {weeks.map((w) => (
                        <tr key={w.week}>
                            <td>{copy.chartLabel(shortDate(w.week), w.sightings, w.orders)}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </figure>
    );
}
