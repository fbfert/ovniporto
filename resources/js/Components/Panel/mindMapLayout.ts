/**
 * Geometry of the manual's mind map. The theme sits in the middle; branches go to both sides
 * (the classic two-sided mind map), each with its leaves stacked beside it. Coordinates live in a
 * fixed 1120-wide box that the component scales with the page, so the layout is pure and testable.
 */

export type MapNodeKind = 'screen' | 'action' | 'state' | 'care';

export interface MapNode {
    label: string;
    kind?: MapNodeKind;
    section?: string;
    href?: string;
    children?: MapNode[];
}

export interface PlacedNode {
    node: MapNode;
    x: number;
    y: number;
    side: 'left' | 'right';
}

export interface PlacedBranch extends PlacedNode {
    leaves: PlacedNode[];
}

export interface MindMapLayout {
    width: number;
    height: number;
    center: { x: number; y: number };
    branches: PlacedBranch[];
}

export const WIDTH = 1120;
/** Half-widths of each column, so edges start and end at the boxes, not at their centres. */
export const HALF = { center: 85, branch: 80, leaf: 115 } as const;
const ROW = 64;
const GAP = 22;
const PADDING = 28;
const X = { branch: 220, leaf: 440 } as const;

const blockHeight = (branch: MapNode) => Math.max(1, branch.children?.length ?? 0) * ROW + GAP;

/** Where to cut the branch list so both sides are as tall as possible alike (reading order kept). */
function splitPoint(branches: MapNode[]): number {
    const heights = branches.map(blockHeight);
    const total = heights.reduce((sum, h) => sum + h, 0);
    let best = branches.length;
    let bestDiff = Infinity;
    let running = 0;
    for (let k = 1; k <= branches.length; k++) {
        running += heights[k - 1] ?? 0;
        const diff = Math.abs(running - (total - running));
        if (diff < bestDiff) {
            bestDiff = diff;
            best = k;
        }
    }
    return best;
}

function placeSide(branches: MapNode[], side: 'left' | 'right', top: number, centerX: number): PlacedBranch[] {
    const sign = side === 'right' ? 1 : -1;
    let cursor = top;
    return branches.map((branch) => {
        const height = blockHeight(branch);
        const leaves = (branch.children ?? []).map((leaf, i) => ({
            node: leaf,
            x: centerX + sign * X.leaf,
            y: cursor + GAP / 2 + (i + 0.5) * ROW,
            side,
        }));
        const placed = { node: branch, x: centerX + sign * X.branch, y: cursor + height / 2, side, leaves };
        cursor += height;
        return placed;
    });
}

const sideHeight = (branches: MapNode[]) => branches.reduce((sum, b) => sum + blockHeight(b), 0);

export function layoutMindMap(root: MapNode): MindMapLayout {
    const branches = root.children ?? [];
    const cut = splitPoint(branches);
    const right = branches.slice(0, cut);
    const left = branches.slice(cut);
    const inner = Math.max(sideHeight(right), sideHeight(left), ROW);
    const height = inner + PADDING * 2;
    const center = { x: WIDTH / 2, y: height / 2 };
    const top = (side: MapNode[]) => PADDING + (inner - sideHeight(side)) / 2;

    return {
        width: WIDTH,
        height,
        center,
        branches: [...placeSide(right, 'right', top(right), center.x), ...placeSide(left, 'left', top(left), center.x)],
    };
}

/** A soft S-curve between two points, leaving and arriving horizontally. */
export function edgePath(from: { x: number; y: number }, to: { x: number; y: number }): string {
    const mid = (from.x + to.x) / 2;
    return `M ${from.x} ${from.y} C ${mid} ${from.y}, ${mid} ${to.y}, ${to.x} ${to.y}`;
}
