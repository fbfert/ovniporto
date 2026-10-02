import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { emptyDraft, type DraftUpdate, type ReportDraft } from './draft';
import { StepWhenWhere } from './StepWhenWhere';

vi.mock('./PointPicker', () => ({ PointPicker: () => <div data-testid="map" /> }));

afterEach(cleanup);

function Harness({ initial }: { initial: ReportDraft }) {
    const [draft, setDraft] = useState(initial);
    const update: DraftUpdate = (patch) =>
        setDraft((current) => ({ ...current, ...(typeof patch === 'function' ? patch(current) : patch) }));
    return (
        <>
            <StepWhenWhere
                draft={draft}
                update={update}
                errors={{}}
                today="2026-10-02"
                lages={{ lat: -27.8, lng: -50.3 }}
            />
            <output data-testid="state">
                {JSON.stringify({ date: draft.observedDate, time: draft.exactTime, point: draft.point })}
            </output>
        </>
    );
}

describe('StepWhenWhere', () => {
    const withSuggestion = {
        ...emptyDraft('2026-10-02', 'coruja'),
        step: 3 as const,
        suggestion: { takenAt: '2026-09-30T22:40', point: { lat: -27.84, lng: -50.21 } },
    };

    it("only offers the photo's date and point; nothing is applied without a tap", () => {
        render(<Harness initial={withSuggestion} />);

        expect(screen.getByText(/A foto diz/)).toBeTruthy();
        expect(screen.getByText('A foto traz o ponto onde foi tirada.')).toBeTruthy();
        expect(screen.queryByText('sugerido pela foto')).toBeNull();
        expect(screen.getByTestId('state').textContent).toContain('"date":"2026-10-02"');
        expect(screen.getByTestId('state').textContent).toContain('"point":null');
    });

    it('marks the fields "sugerido pela foto" once the suggestion is accepted', async () => {
        const user = userEvent.setup();
        render(<Harness initial={withSuggestion} />);

        const [useDate, usePoint] = screen.getAllByRole('button', { name: 'Usar' });
        await user.click(useDate!);
        await user.click(usePoint!);

        expect(screen.getAllByText('sugerido pela foto')).toHaveLength(2);
        expect(screen.getByTestId('state').textContent).toBe(
            JSON.stringify({ date: '2026-09-30', time: '22:40', point: { lat: -27.84, lng: -50.21 } }),
        );
    });

    it('drops a suggestion the person ignores', async () => {
        const user = userEvent.setup();
        render(<Harness initial={withSuggestion} />);

        await user.click(screen.getAllByRole('button', { name: 'Ignorar' })[0]!);

        expect(screen.queryByText(/A foto diz/)).toBeNull();
    });
});
