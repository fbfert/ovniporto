import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { MarkdownEditor } from './MarkdownEditor';

afterEach(cleanup);

function Harness({ renderer }: { renderer: (markdown: string) => Promise<string> }) {
    const [value, setValue] = useState('');
    return <MarkdownEditor label="Texto" value={value} onChange={setValue} render={renderer} />;
}

describe('MarkdownEditor', () => {
    it('shows the rendered preview of what was typed, then goes back to editing', async () => {
        const user = userEvent.setup();
        const renderer = vi.fn(async (markdown: string) => `<p><strong>${markdown}</strong></p>`);
        render(<Harness renderer={renderer} />);

        await user.type(screen.getByRole('textbox', { name: 'Texto' }), 'céu escuro');
        await user.click(screen.getByRole('button', { name: 'Prévia' }));

        expect(renderer).toHaveBeenCalledWith('céu escuro');
        expect((await screen.findByTestId('markdown-preview')).querySelector('strong')?.textContent).toBe('céu escuro');

        await user.click(screen.getByRole('button', { name: 'Editar' }));
        expect((screen.getByRole('textbox', { name: 'Texto' }) as HTMLTextAreaElement).value).toBe('céu escuro');
    });
});
