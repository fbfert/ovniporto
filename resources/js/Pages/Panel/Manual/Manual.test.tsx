import { cleanup, render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { forwardRef, type ReactNode } from 'react';
import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest';
import Home from '../Home';
import Chapter from './Chapter';
import Index from './Index';

const visit = vi.fn();

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: forwardRef<HTMLAnchorElement, { href: string; children: ReactNode; className?: string }>(
        ({ href, children, ...rest }, ref) => (
            <a href={href} ref={ref} {...rest}>
                {children}
            </a>
        ),
    ),
    router: { visit: (href: string) => visit(href) },
}));

beforeAll(() => {
    // jsdom has no layout: scrolling is a no-op here.
    Element.prototype.scrollIntoView = vi.fn();
});

afterEach(cleanup);

const CHAPTERS = [
    {
        slug: 'primeiros-passos',
        group: 'start',
        title: 'Primeiros passos',
        summary: 'Entrar e se achar.',
        reviewedAt: '2026-10-07',
    },
    {
        slug: 'produtos',
        group: 'store',
        title: 'Produtos da loja',
        summary: 'Cadastrar e publicar.',
        reviewedAt: '2026-10-01',
    },
];

describe('manual index', () => {
    it('lists the chapters in reading order with their review date', () => {
        render(<Index chapters={CHAPTERS} />);

        const list = screen.getAllByRole('list').find((l) => l.tagName === 'OL')!;
        const links = within(list).getAllByRole('link');
        expect(links.map((l) => l.getAttribute('href'))).toEqual([
            '/painel/manual/primeiros-passos',
            '/painel/manual/produtos',
        ]);
        expect(links[1]!.textContent).toContain('Revisado em 01/10/2026');
    });

    it('draws one branch per group and opens a chapter from its leaf', async () => {
        render(<Index chapters={CHAPTERS} />);
        const tree = screen.getByRole('tree', { name: 'Mapa do manual: grupos e capítulos' });

        expect(within(tree).getByRole('treeitem', { name: 'Comece aqui' }).getAttribute('aria-level')).toBe('2');
        await userEvent.click(within(tree).getByRole('treeitem', { name: 'Produtos da loja' }));
        expect(visit).toHaveBeenCalledWith('/painel/manual/produtos');
    });
});

describe('manual chapter', () => {
    const chapter = {
        slug: 'produtos',
        title: 'Produtos da loja',
        summary: 'Cadastrar e publicar.',
        reviewedAt: '2026-10-07',
        map: { label: 'Produtos', children: [{ label: 'Fotos', kind: 'screen' as const, section: 'fotos' }] },
        sections: [
            { id: 'lista', title: 'A lista', html: '<p>Todos os produtos.</p>' },
            { id: 'fotos', title: 'Fotos', html: '<p>Texto alternativo.</p>' },
        ],
    };

    it('renders every section under its own heading and anchor', () => {
        render(<Chapter chapter={chapter} previous={null} next={{ slug: 'pedidos', title: 'Pedidos' }} />);

        expect(document.getElementById('fotos')?.textContent).toContain('Texto alternativo.');
        expect(screen.getByRole('link', { name: /Próximo capítulo\s*Pedidos/ }).getAttribute('href')).toBe(
            '/painel/manual/pedidos',
        );
    });

    it('takes the reader to the section of a map node and focuses its title', async () => {
        render(<Chapter chapter={chapter} previous={null} next={null} />);

        await userEvent.click(screen.getByRole('treeitem', { name: 'Fotos' }));
        expect(document.activeElement).toBe(screen.getByRole('heading', { name: 'Fotos' }));
        expect(window.location.hash).toBe('#fotos');
    });
});

describe('panel home', () => {
    it('links to the manual', () => {
        render(
            <Home
                counts={{ members: 1, waitlist: 0, sightings: null, orders: null, revenueMonthCents: null }}
                goals={null}
                weekly={[]}
                needsYou={[]}
                sightings={null}
            />,
        );

        expect(screen.getByRole('link', { name: /Manual de operação/ }).getAttribute('href')).toBe('/painel/manual');
    });
});
