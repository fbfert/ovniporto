import { useMemo, useRef, useState, type CSSProperties, type KeyboardEvent, type ReactNode } from 'react';
import { edgePath, HALF, layoutMindMap, type MapNode, type MapNodeKind } from './mindMapLayout';

export type { MapNode, MapNodeKind } from './mindMapLayout';

/** Branch colours come from the sticker palette; the shape repeats the meaning for who does not see colour. */
const KIND_COLOR: Record<MapNodeKind, string> = {
    screen: 'var(--color-horizon)',
    action: 'var(--color-beam)',
    state: 'var(--color-night-blue)',
    care: 'var(--color-car)',
};
const NEUTRAL = 'var(--color-horizon)';

const colorOf = (node: MapNode) => (node.kind ? KIND_COLOR[node.kind] : NEUTRAL);

export function KindGlyph({ kind, className = 'size-2.5' }: { kind?: MapNodeKind; className?: string }) {
    const fill = kind ? KIND_COLOR[kind] : NEUTRAL;
    const shape = {
        screen: <rect x="1" y="1" width="8" height="8" rx="1.5" />,
        action: <circle cx="5" cy="5" r="4.2" />,
        state: <path d="M5 0.6 9.4 5 5 9.4 0.6 5Z" />,
        care: <path d="M5 0.8 9.4 9H0.6Z" />,
    }[kind ?? 'screen'];
    return (
        <svg viewBox="0 0 10 10" className={`shrink-0 ${className}`} fill={fill} aria-hidden>
            {shape}
        </svg>
    );
}

interface Item {
    node: MapNode;
    level: 1 | 2 | 3;
    parent: number | null;
    firstChild: number | null;
    setSize: number;
    posInSet: number;
    x: number;
    y: number;
    delay: number;
}

const pct = (value: number, total: number) => `${(value / total) * 100}%`;

/** Flatten the laid-out map in reading order: centre, then each branch followed by its leaves. */
function flatten(root: MapNode): {
    items: Item[];
    edges: { d: string; color: string; delay: number; care: boolean }[];
    width: number;
    height: number;
} {
    const layout = layoutMindMap(root);
    const items: Item[] = [
        { node: root, level: 1, parent: null, firstChild: null, setSize: 1, posInSet: 1, ...layout.center, delay: 0 },
    ];
    const edges: { d: string; color: string; delay: number; care: boolean }[] = [];
    const { center } = layout;

    layout.branches.forEach((branch, b) => {
        const sign = branch.side === 'right' ? 1 : -1;
        const delay = b * 40;
        const index = items.length;
        if (items[0] && items[0].firstChild === null) items[0].firstChild = index;
        items.push({
            node: branch.node,
            level: 2,
            parent: 0,
            firstChild: branch.leaves.length ? index + 1 : null,
            setSize: layout.branches.length,
            posInSet: b + 1,
            x: branch.x,
            y: branch.y,
            delay: delay + 180,
        });
        edges.push({
            d: edgePath(
                { x: center.x + sign * HALF.center, y: center.y },
                { x: branch.x - sign * HALF.branch, y: branch.y },
            ),
            color: colorOf(branch.node),
            delay,
            care: branch.node.kind === 'care',
        });
        branch.leaves.forEach((leaf, l) => {
            const leafDelay = delay + 120 + l * 30;
            items.push({
                node: leaf.node,
                level: 3,
                parent: index,
                firstChild: null,
                setSize: branch.leaves.length,
                posInSet: l + 1,
                x: leaf.x,
                y: leaf.y,
                delay: leafDelay + 180,
            });
            edges.push({
                d: edgePath(
                    { x: branch.x + sign * HALF.branch, y: branch.y },
                    { x: leaf.x - sign * HALF.leaf, y: leaf.y },
                ),
                color: colorOf(leaf.node),
                delay: leafDelay,
                care: leaf.node.kind === 'care',
            });
        });
    });

    return { items, edges, width: layout.width, height: layout.height };
}

const LEVEL_CLASS: Record<Item['level'], string> = {
    1: 'bg-night px-5 font-display text-sm font-bold tracking-[0.04em] text-moonlight uppercase lg:w-(--w) lg:justify-center lg:text-center',
    2: 'bg-moonlight px-4 text-[0.95rem] font-semibold text-night ring-2 ring-(--kind) lg:w-(--w)',
    3: 'bg-night/[0.04] px-3 text-sm text-night ring-1 ring-night/15 lg:w-(--w)',
};
const HALF_OF: Record<Item['level'], number> = { 1: HALF.center, 2: HALF.branch, 3: HALF.leaf };
const INDENT: Record<Item['level'], string> = { 1: '', 2: 'ml-4 border-l-2 pl-4', 3: 'ml-12 border-l-2 pl-4' };

/**
 * The manual's mind map. On wide screens: the theme in the middle of a faint radar, branches drawn
 * to both sides. On a phone: the same tree as an indented list with coloured rails. One ARIA tree
 * either way (arrows move, Enter opens); the drawing is decoration and hidden from assistive tech.
 */
export function MindMap({
    root,
    label,
    onActivate,
    center,
}: {
    root: MapNode;
    label: string;
    onActivate: (node: MapNode) => void;
    /** Optional mark drawn inside the centre node (the seal on the overview map). */
    center?: ReactNode;
}) {
    const { items, edges, width, height } = useMemo(() => flatten(root), [root]);
    const [active, setActive] = useState(0);
    const refs = useRef<(HTMLDivElement | null)[]>([]);

    const focus = (index: number) => {
        if (index < 0 || index >= items.length) return;
        setActive(index);
        refs.current[index]?.focus();
    };
    const activate = (item: Item) => {
        if (item.node.section || item.node.href) onActivate(item.node);
    };
    const onKeyDown = (event: KeyboardEvent<HTMLDivElement>, index: number) => {
        const item = items[index];
        if (!item) return;
        const moves: Record<string, () => void> = {
            ArrowDown: () => focus(index + 1),
            ArrowUp: () => focus(index - 1),
            ArrowRight: () => item.firstChild !== null && focus(item.firstChild),
            ArrowLeft: () => item.parent !== null && focus(item.parent),
            Home: () => focus(0),
            End: () => focus(items.length - 1),
            Enter: () => activate(item),
            ' ': () => activate(item),
        };
        const move = moves[event.key];
        if (move) {
            event.preventDefault();
            move();
        }
    };

    const radar = [0.16, 0.3, 0.44].map((r) => r * width);

    return (
        <div className="relative lg:aspect-(--ratio)" style={{ '--ratio': `${width} / ${height}` } as CSSProperties}>
            <svg
                viewBox={`0 0 ${width} ${height}`}
                className="pointer-events-none absolute inset-0 hidden h-full w-full lg:block"
                aria-hidden
            >
                {radar.map((r) => (
                    <ellipse
                        key={r}
                        cx={width / 2}
                        cy={height / 2}
                        rx={r}
                        ry={Math.min(r * 0.62, height / 2 - 4)}
                        fill="none"
                        stroke="var(--color-night)"
                        strokeOpacity={0.07}
                        strokeDasharray="2 7"
                    />
                ))}
                {edges.map((edge, i) => (
                    <path
                        key={i}
                        d={edge.d}
                        pathLength={1}
                        className="map-edge"
                        style={{ '--delay': `${edge.delay}ms` } as CSSProperties}
                        fill="none"
                        stroke={edge.color}
                        strokeWidth={edge.care ? 2 : 2.5}
                        strokeLinecap="round"
                    />
                ))}
            </svg>
            <div role="tree" aria-label={label} className="relative flex flex-col lg:absolute lg:inset-0 lg:block">
                {items.map((item, index) => {
                    const target = Boolean(item.node.section || item.node.href);
                    return (
                        <div
                            key={index}
                            role="none"
                            className={`py-1 lg:contents ${INDENT[item.level]}`}
                            style={{ borderColor: colorOf(item.node) }}
                        >
                            <div
                                ref={(el) => {
                                    refs.current[index] = el;
                                }}
                                role="treeitem"
                                aria-level={item.level}
                                aria-setsize={item.setSize}
                                aria-posinset={item.posInSet}
                                aria-expanded={item.firstChild !== null ? true : undefined}
                                aria-selected={index === active}
                                tabIndex={index === active ? 0 : -1}
                                onKeyDown={(e) => onKeyDown(e, index)}
                                onFocus={() => setActive(index)}
                                onClick={() => {
                                    setActive(index);
                                    activate(item);
                                }}
                                className={`map-node flex min-h-11 items-center gap-2 rounded-2xl py-2 leading-snug outline-offset-2 lg:absolute lg:top-(--y) lg:left-(--x) lg:-translate-x-1/2 lg:-translate-y-1/2 ${LEVEL_CLASS[item.level]} ${target ? 'cursor-pointer hover:ring-night/40' : ''}`}
                                style={
                                    {
                                        '--x': pct(item.x, width),
                                        '--y': pct(item.y, height),
                                        '--w': pct(HALF_OF[item.level] * 2, width),
                                        '--kind': colorOf(item.node),
                                        '--delay': `${item.delay}ms`,
                                    } as CSSProperties
                                }
                            >
                                {item.level === 1 ? center : <KindGlyph kind={item.node.kind} />}
                                <span>{item.node.label}</span>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

/** The four kinds, with colour and shape, for the key under the map. */
export function MindMapLegend({ labels }: { labels: Record<MapNodeKind, string> }) {
    return (
        <ul className="flex flex-wrap gap-x-5 gap-y-2 text-sm text-night/75">
            {(Object.keys(labels) as MapNodeKind[]).map((kind) => (
                <li key={kind} className="inline-flex items-center gap-2">
                    <KindGlyph kind={kind} className="size-3" />
                    {labels[kind]}
                </li>
            ))}
        </ul>
    );
}
