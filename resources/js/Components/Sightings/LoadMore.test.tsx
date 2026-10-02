import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { LoadMore } from './LoadMore';

afterEach(cleanup);

describe('LoadMore', () => {
    it('asks for the next page and stays busy until it arrives', async () => {
        const user = userEvent.setup();
        let finish = () => {};
        const onLoad = vi.fn((_next: number, done: () => void) => {
            finish = done;
        });
        render(<LoadMore page={2} hasMore onLoad={onLoad} />);

        await user.click(screen.getByRole('button', { name: 'Carregar mais' }));

        expect(onLoad).toHaveBeenCalledWith(3, expect.any(Function));
        expect((screen.getByRole('button') as HTMLButtonElement).disabled).toBe(true);
        finish();
    });

    it('disappears when there is nothing left to load', () => {
        render(<LoadMore page={4} hasMore={false} onLoad={vi.fn()} />);

        expect(screen.queryByRole('button')).toBeNull();
    });
});
