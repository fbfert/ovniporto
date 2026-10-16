import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { t } from '@/i18n/pt-BR';
import { AgreementChecks } from './AgreementChecks';

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children }: { href: string; children: ReactNode }) => <a href={href}>{children}</a>,
}));

afterEach(cleanup);

const unchecked = { terms: false, age: false };

describe('AgreementChecks', () => {
    it('asks for the terms and the age on the welcome screen', () => {
        render(<AgreementChecks ask={['terms', 'age']} values={unchecked} errors={{}} onChange={vi.fn()} />);

        expect(screen.getAllByRole('checkbox')).toHaveLength(2);
        expect(screen.getByRole('checkbox', { name: new RegExp(t.members.ageConfirm) })).toBeTruthy();
    });

    it('asks only for what is pending', () => {
        render(<AgreementChecks ask={['age']} values={unchecked} errors={{}} onChange={vi.fn()} />);

        expect(screen.getAllByRole('checkbox')).toHaveLength(1);
        expect(screen.queryByRole('link', { name: t.members.terms })).toBeNull();
    });

    it('shows the error on the field and reports each change', () => {
        const onChange = vi.fn();
        render(
            <AgreementChecks
                ask={['terms', 'age']}
                values={unchecked}
                errors={{ age: 'Confirme a idade.' }}
                onChange={onChange}
            />,
        );

        const age = screen.getByRole('checkbox', { name: new RegExp(t.members.ageConfirm) });
        expect(age.getAttribute('aria-invalid')).toBe('true');
        expect(screen.getByText('Confirme a idade.')).toBeTruthy();

        fireEvent.click(age);
        expect(onChange).toHaveBeenCalledWith('age', true);
    });
});
