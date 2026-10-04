import { Link, router, usePage } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { ProductArt } from '@/Components/Store/ProductArt';
import { QuantityStepper } from '@/Components/Store/QuantityStepper';
import { requestQuote, ShippingQuote } from '@/Components/Store/ShippingQuote';
import { Button } from '@/Components/Ui/Button';
import { ChipGroup } from '@/Components/Ui/Fields';
import { Section } from '@/Components/Ui/Section';
import { TicketCard } from '@/Components/Ui/TicketCard';
import { Badge, Display } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { ProductCard, SharedProps } from '@/types';

const copy = t.storePage;

interface Variant {
    id: number;
    name: string;
    priceCents: number;
    /** How many fit in a cart: 0 = sold out or inactive. */
    max: number;
}

interface Product extends ProductCard {
    description: string | null;
    shortDescription: string | null;
    weightGrams: number;
    dimensions: { length: number; width: number; height: number } | null;
    images: { url: string; alt: string }[];
    variants: Variant[];
}

interface Props {
    product: Product;
    related: ProductCard[];
}

export default function ProductPage({ product, related }: Props) {
    const { errors } = usePage<SharedProps>().props;
    const firstAvailable = product.variants.find((v) => v.max > 0) ?? product.variants[0];
    const [variantId, setVariantId] = useState<number | null>(firstAvailable?.id ?? null);
    const [quantity, setQuantity] = useState(1);
    const [adding, setAdding] = useState(false);
    const variant = product.variants.find((v) => v.id === variantId) ?? null;
    const max = variant?.max ?? 0;
    const cover = product.images[0] ?? null;

    const add = () => {
        if (!variant) return;
        setAdding(true);
        router.post(
            '/carrinho/itens',
            { variantId: variant.id, quantity },
            { preserveScroll: true, preserveState: true, onFinish: () => setAdding(false) },
        );
    };

    return (
        <>
            <SeoHead />
            <Section tone="dark" pattern="stars" innerClassName="pt-32! sm:pt-36!">
                <Link
                    href="/loja"
                    className="min-h-11 text-sm font-semibold text-moonlight/75 underline underline-offset-4"
                >
                    ← {copy.back}
                </Link>
                <div className="mt-6 grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-16">
                    <figure className="group/ticket relative aspect-square overflow-hidden rounded-[26px] bg-night-blue ring-1 ring-moonlight/10">
                        <ProductArt
                            image={cover?.url ?? null}
                            alt={cover?.alt ?? product.name}
                            sizes="(min-width: 1024px) 40vw, 90vw"
                        />
                        {product.label && (
                            <span className="absolute top-6 -right-1 rotate-3 rounded-l-full bg-car px-4 py-1.5 font-display text-xs font-bold tracking-[0.12em] text-night uppercase">
                                {product.label}
                            </span>
                        )}
                    </figure>

                    <div>
                        <Badge tone={product.madeToOrder ? 'horizon' : 'beam'}>
                            {product.madeToOrder ? copy.production(product.productionDays) : copy.readyBadge}
                        </Badge>
                        <Display as="h1" className="mt-4 text-[clamp(2rem,1.2rem+3vw,3.4rem)]!">
                            {product.name}
                        </Display>
                        <p className="mt-4 flex items-baseline gap-3">
                            <span className="font-display text-3xl font-extrabold text-car tabular-nums">
                                {money(variant?.priceCents ?? product.priceCents)}
                            </span>
                            {product.comparePriceCents && (
                                <s className="text-moonlight/50 tabular-nums">{money(product.comparePriceCents)}</s>
                            )}
                        </p>
                        {product.shortDescription && (
                            <p className="mt-5 max-w-[46ch] text-lg text-moonlight/85">{product.shortDescription}</p>
                        )}

                        {product.variants.length > 1 && (
                            <ChipGroup
                                tone="dark"
                                label={copy.variantLabel}
                                className="mt-8"
                                options={product.variants.map((v) => ({
                                    value: String(v.id),
                                    label: v.max > 0 ? v.name : `${v.name} · ${copy.soldOut}`,
                                }))}
                                value={variantId === null ? null : String(variantId)}
                                onChange={(value) => {
                                    const chosen = product.variants.find((v) => String(v.id) === value);
                                    if (chosen && chosen.max > 0) {
                                        setVariantId(chosen.id);
                                        setQuantity(1);
                                    }
                                }}
                            />
                        )}

                        <div className="mt-8 flex flex-wrap items-center gap-4">
                            <QuantityStepper
                                tone="dark"
                                value={quantity}
                                max={Math.max(1, max)}
                                onChange={setQuantity}
                                disabled={max === 0}
                            />
                            <Button variant="car" size="lg" onClick={add} loading={adding} disabled={max === 0}>
                                {max === 0 ? copy.soldOut : copy.add}
                            </Button>
                        </div>
                        {errors.quantity && (
                            <p role="alert" className="mt-3 font-semibold text-car">
                                {errors.quantity}
                            </p>
                        )}

                        <div className="mt-10">
                            {variant && <ShippingQuote request={requestQuote(variant.id, quantity)} />}
                        </div>

                        {(product.description || product.weightGrams > 0) && (
                            <section aria-labelledby="detalhes" className="mt-10">
                                <h2
                                    id="detalhes"
                                    className="font-display text-sm font-bold tracking-[0.06em] uppercase"
                                >
                                    {copy.details}
                                </h2>
                                {product.description && (
                                    <p className="mt-3 max-w-[56ch] whitespace-pre-line text-moonlight/85">
                                        {product.description}
                                    </p>
                                )}
                                <ul className="mt-3 space-y-1 text-sm text-moonlight/65">
                                    {product.weightGrams > 0 && <li>{copy.weight(product.weightGrams)}</li>}
                                    {product.dimensions && <li>{copy.size(product.dimensions)}</li>}
                                </ul>
                            </section>
                        )}
                    </div>
                </div>
            </Section>

            {related.length > 0 && (
                <Section tone="dark" wave labelledBy="combina">
                    <Display as="h2" id="combina" className="text-[clamp(1.4rem,1rem+2vw,2.2rem)]!">
                        {copy.related}
                    </Display>
                    <ul className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {related.map((item) => (
                            <li key={item.id}>
                                <TicketCard
                                    href={`/loja/${item.slug}`}
                                    label={item.label}
                                    title={item.name}
                                    price={money(item.priceCents)}
                                    meta={item.madeToOrder ? t.store.madeToOrder(item.productionDays) : t.store.ready}
                                    art={
                                        <ProductArt image={item.image} alt={item.imageAlt ?? item.name} sizes="14rem" />
                                    }
                                />
                            </li>
                        ))}
                    </ul>
                </Section>
            )}
        </>
    );
}

ProductPage.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
