import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { ProductArt } from '@/Components/Store/ProductArt';
import { Button } from '@/Components/Ui/Button';
import { Badge, Display } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.products;

interface Row {
    id: number;
    name: string;
    slug: string;
    active: boolean;
    priceCents: number;
    madeToOrder: boolean;
    stock: number | null;
    image: string | null;
}

export default function Index({ products }: { products: Row[] }) {
    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <div className="flex flex-wrap items-end justify-between gap-4">
                <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                    {copy.title}
                </Display>
                <Button href="/painel/produtos/novo">{copy.newProduct}</Button>
            </div>
            <ul className="mt-10 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                {products.map((product) => (
                    <li key={product.id}>
                        <Link
                            href={`/painel/produtos/${product.id}`}
                            className="grid grid-cols-[4rem_minmax(0,1fr)_auto] items-center gap-4 rounded-2xl px-2 py-4 outline-none hover:bg-night/4 focus-visible:ring-2 focus-visible:ring-beam"
                        >
                            <span className="block aspect-square overflow-hidden rounded-xl bg-night">
                                <ProductArt image={product.image} alt="" sizes="4rem" />
                            </span>
                            <span className="min-w-0">
                                <span className="flex flex-wrap items-center gap-2">
                                    <span className="font-semibold">{product.name}</span>
                                    <Badge tone={product.active ? 'beam' : 'neutral'}>
                                        {product.active ? copy.active : copy.inactive}
                                    </Badge>
                                </span>
                                <span className="mt-0.5 block text-sm text-night/60">
                                    {product.madeToOrder ? copy.madeToOrder : copy.stock(product.stock ?? 0)}
                                </span>
                            </span>
                            <span className="font-semibold tabular-nums">{money(product.priceCents)}</span>
                        </Link>
                    </li>
                ))}
            </ul>
        </>
    );
}

Index.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
