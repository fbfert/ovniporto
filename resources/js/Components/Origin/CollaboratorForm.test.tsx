import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { t } from '@/i18n/pt-BR';
import { CollaboratorForm } from './CollaboratorForm';

vi.mock('@inertiajs/react', () => ({
    useForm: <T extends object>(initial: T) => ({
        data: initial,
        errors: {},
        processing: false,
        setData: vi.fn(),
        post: vi.fn(),
        reset: vi.fn(),
    }),
}));

afterEach(cleanup);

describe('CollaboratorForm', () => {
    it('keeps the form closed until the link is used', () => {
        render(<CollaboratorForm />);

        expect(screen.queryByRole('dialog')).toBeNull();
        expect(screen.getByRole('button', { name: t.collaborator.open })).toBeTruthy();
    });

    it('opens a dialog with every field, the areas and the consent', () => {
        render(<CollaboratorForm />);
        fireEvent.click(screen.getByRole('button', { name: t.collaborator.open }));

        const dialog = screen.getByRole('dialog', { name: t.collaborator.title });
        expect(dialog).toBeTruthy();
        expect(screen.getByLabelText(t.collaborator.name, { exact: false })).toBeTruthy();
        expect(screen.getByLabelText(t.collaborator.email, { exact: false })).toBeTruthy();
        expect(screen.getByLabelText(t.collaborator.location, { exact: false })).toBeTruthy();
        for (const option of t.collaborator.areaOptions) {
            expect(screen.getByRole('button', { name: option.label })).toBeTruthy();
        }
        expect(screen.getByRole('checkbox', { name: t.collaborator.consent })).toBeTruthy();
    });
});
