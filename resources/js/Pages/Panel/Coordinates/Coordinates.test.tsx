import { cleanup, render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import Coordinates, { type Field, type Integration, type TestResult } from './Index';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children }: { href: string; children: ReactNode }) => <a href={href}>{children}</a>,
    router: { delete: vi.fn() },
    useForm: <T extends object>(initial: T) => ({
        data: initial,
        errors: {},
        processing: false,
        setData: vi.fn(),
        put: vi.fn(),
        post: vi.fn(),
    }),
}));

afterEach(cleanup);

const none: Field = { value: null, env: null, fromPanel: false };
const group = (keys: string[], overrides: Record<string, Field> = {}) =>
    Object.fromEntries(keys.map((k) => [k, overrides[k] ?? none]));

const MAIL = ['host', 'port', 'scheme', 'username', 'password', 'fromAddress', 'fromName'];
const SHIPPING = [
    'name',
    'phone',
    'email',
    'document',
    'postalCode',
    'street',
    'number',
    'district',
    'city',
    'state',
    'packageLength',
    'packageWidth',
    'packageHeight',
];

function page({
    mail = {},
    test = null,
    integrations = [],
    alertsMissing = false,
}: {
    mail?: Record<string, Field>;
    test?: TestResult | null;
    integrations?: Integration[];
    alertsMissing?: boolean;
}) {
    render(
        <Coordinates
            groups={{ correio: group(MAIL, mail), alertas: group(['email']), frete: group(SHIPPING) }}
            integrations={integrations}
            alertsMissing={alertsMissing}
            test={test}
        />,
    );
}

describe('Coordenadas', () => {
    it('never fills the password, only says it is set, and offers to remove it', () => {
        page({ mail: { password: { value: null, env: null, fromPanel: true, set: true } } });

        expect((screen.getByLabelText('Senha') as HTMLInputElement).value).toBe('');
        expect(screen.getByText('Senha definida. Deixe em branco para manter.')).toBeTruthy();
        expect(screen.getByRole('button', { name: 'Remover senha' })).toBeTruthy();
    });

    it('shows the .env value as a hint and says where each value comes from', () => {
        page({
            mail: {
                host: { value: 'smtp.painel.test', env: 'smtp.env.test', fromPanel: true },
                port: { value: null, env: '587', fromPanel: false },
            },
        });

        expect((screen.getByLabelText('Servidor SMTP') as HTMLInputElement).value).toBe('smtp.painel.test');
        expect((screen.getByLabelText('Porta') as HTMLInputElement).placeholder).toBe('587');
        expect(screen.getAllByText('definido aqui').length).toBe(1);
        expect(screen.getAllByText('do servidor').length).toBe(1);
    });

    it('explains a refused login with the server answer', () => {
        page({
            test: { kind: 'mail', to: 'torre@ovniporto.test', sent: false, problem: 'auth', detail: '535 5.7.8 Error' },
        });

        expect(screen.getByText(/O servidor recusou o usuário ou a senha/)).toBeTruthy();
        expect(screen.getByText('535 5.7.8 Error')).toBeTruthy();
    });

    it('warns when the alerts reach nobody', () => {
        page({ alertsMissing: true });

        expect(screen.getByRole('note').textContent).toContain('a fila esperando demais não avisa ninguém');
    });

    it('lists the integrations by state and mode, without values', () => {
        page({
            integrations: [
                { name: 'paypal', configured: true, mode: 'sandbox', attention: true },
                { name: 'melhorEnvio', configured: false, mode: 'sandbox', attention: true },
            ],
        });

        expect(screen.getByText('PayPal')).toBeTruthy();
        expect(screen.getByText('no ar')).toBeTruthy();
        expect(screen.getByText('não configurado')).toBeTruthy();
        expect(screen.getAllByText('precisa de atenção').length).toBe(2);
    });
});
