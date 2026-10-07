import { describe, expect, it } from 'vitest';
import { HALF, layoutMindMap, WIDTH, type MapNode } from './mindMapLayout';

const leaf = (label: string): MapNode => ({ label, kind: 'action' });
const branch = (label: string, leaves: number): MapNode => ({
    label,
    kind: 'screen',
    children: Array.from({ length: leaves }, (_, i) => leaf(`${label}-${i}`)),
});

/** Every box of the map, as [left, top, right, bottom], using the column half-widths and a row height. */
function boxes(root: MapNode) {
    const layout = layoutMindMap(root);
    const box = (x: number, y: number, half: number) => [x - half, y - 22, x + half, y + 22] as const;
    return [
        box(layout.center.x, layout.center.y, HALF.center),
        ...layout.branches.flatMap((b) => [
            box(b.x, b.y, HALF.branch),
            ...b.leaves.map((l) => box(l.x, l.y, HALF.leaf)),
        ]),
    ];
}

describe('layoutMindMap', () => {
    it('puts the theme in the middle and splits branches to both sides, keeping reading order', () => {
        const layout = layoutMindMap({
            label: 'Pedidos',
            children: [branch('a', 2), branch('b', 2), branch('c', 2), branch('d', 2)],
        });

        expect(layout.center).toEqual({ x: WIDTH / 2, y: layout.height / 2 });
        expect(layout.branches.map((b) => [b.node.label, b.side])).toEqual([
            ['a', 'right'],
            ['b', 'right'],
            ['c', 'left'],
            ['d', 'left'],
        ]);
        expect(layout.branches[0]!.y).toBeLessThan(layout.branches[1]!.y);
    });

    it('stacks the leaves beside their branch, centred on it', () => {
        const [only] = layoutMindMap({ label: 'X', children: [branch('a', 3)] }).branches;

        expect(only!.leaves.map((l) => l.side)).toEqual(['right', 'right', 'right']);
        expect(only!.leaves[1]!.y).toBeCloseTo(only!.y);
        expect(only!.leaves[0]!.x).toBeGreaterThan(only!.x);
    });

    it('keeps every box inside the frame and never lets two overlap', () => {
        const root = { label: 'Cheio', children: Array.from({ length: 7 }, (_, i) => branch(`r${i}`, 8)) };
        const all = boxes(root);
        const { height } = layoutMindMap(root);

        for (const [left, top, right, bottom] of all) {
            expect(left).toBeGreaterThanOrEqual(0);
            expect(right).toBeLessThanOrEqual(WIDTH);
            expect(top).toBeGreaterThanOrEqual(0);
            expect(bottom).toBeLessThanOrEqual(height);
        }
        all.forEach((a, i) =>
            all.slice(i + 1).forEach((b) => {
                const overlap = a[0] < b[2] && b[0] < a[2] && a[1] < b[3] && b[1] < a[3];
                expect(overlap).toBe(false);
            }),
        );
    });

    it('balances the two sides by height, not by count', () => {
        const layout = layoutMindMap({
            label: 'X',
            children: [branch('alto', 8), branch('b', 1), branch('c', 1), branch('d', 1)],
        });

        expect(layout.branches.filter((b) => b.side === 'right').map((b) => b.node.label)).toEqual(['alto']);
    });
});
