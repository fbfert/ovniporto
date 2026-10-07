import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { ConfirmButton } from '@/Components/Panel/ConfirmButton';
import { MoveButtons, PanelSection } from '@/Components/Panel/PanelSection';
import { ProductArt } from '@/Components/Store/ProductArt';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField, SelectField, TextareaField, TextField } from '@/Components/Ui/Fields';
import { TicketCard } from '@/Components/Ui/TicketCard';
import { Display } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.products;
const common = t.panel.content;

interface Variant {
    id: number;
    name: string;
    sku: string;
    priceDeltaCents: number;
    stock: number | null;
    active: boolean;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    shortDescription: string | null;
    description: string | null;
    priceCents: number;
    comparePriceCents: number | null;
    madeToOrder: boolean;
    productionDays: number;
    weightGrams: number;
    dimensions: { length: number; width: number; height: number } | null;
    label: string | null;
    active: boolean;
    featured: boolean;
    variants: Variant[];
    images: { id: number; url: string; alt: string }[];
    stockHistory: {
        variantId: number;
        delta: number;
        after: number;
        reason: string;
        actor: string | null;
        at: string;
    }[];
}

const reais = (cents: number | null) => (cents === null ? '' : (cents / 100).toFixed(2));
const toCents = (value: string) => Math.round(Number(value.replace(',', '.')) * 100) || 0;
const fileInput =
    'block min-h-11 w-full text-sm file:mr-4 file:min-h-11 file:rounded-full file:border-0 file:bg-night file:px-4 file:font-semibold file:text-moonlight';

function ProductForm({ product }: { product: Product | null }) {
    const form = useForm({
        name: product?.name ?? '',
        shortDescription: product?.shortDescription ?? '',
        description: product?.description ?? '',
        price: reais(product?.priceCents ?? null),
        comparePrice: reais(product?.comparePriceCents ?? null),
        madeToOrder: product?.madeToOrder ?? false,
        productionDays: String(product?.productionDays ?? 0),
        weightGrams: String(product?.weightGrams ?? ''),
        length: String(product?.dimensions?.length ?? ''),
        width: String(product?.dimensions?.width ?? ''),
        height: String(product?.dimensions?.height ?? ''),
        label: product?.label ?? '',
        active: product?.active ?? false,
        featured: product?.featured ?? false,
    });
    const { data } = form;
    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (product) {
            form.put(`/painel/produtos/${product.id}`, { preserveScroll: true });
            return;
        }
        // The submitter tells "Salvar" (stay and add photos) from "Salvar e voltar" (back to the list).
        const submitter = (event.nativeEvent as SubmitEvent).submitter;
        const returnToList = submitter?.getAttribute('name') === 'returnToList';
        form.transform((values) => ({ ...values, returnToList }));
        form.post('/painel/produtos');
    };
    const text = (key: 'name' | 'shortDescription' | 'label', label: string, max: number) => (
        <TextField
            label={label}
            value={data[key]}
            onChange={(e) => form.setData(key, e.target.value)}
            error={form.errors[key]}
            maxLength={max}
        />
    );
    const number = (
        key: 'price' | 'comparePrice' | 'productionDays' | 'weightGrams' | 'length' | 'width' | 'height',
        label: string,
        step = '1',
    ) => (
        <TextField
            type="number"
            min={0}
            step={step}
            inputMode="decimal"
            label={label}
            value={data[key]}
            onChange={(e) => form.setData(key, e.target.value)}
            error={form.errors[key]}
        />
    );

    return (
        <form onSubmit={submit} className="grid gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="sm:col-span-2">{text('name', copy.name, 120)}</div>
                <div className="sm:col-span-2">{text('shortDescription', copy.shortDescription, 255)}</div>
                <TextareaField
                    label={copy.description}
                    value={data.description}
                    onChange={(e) => form.setData('description', e.target.value)}
                    rows={4}
                    className="sm:col-span-2"
                />
                {number('price', copy.price, '0.01')}
                {number('comparePrice', copy.comparePrice, '0.01')}
                <SelectField
                    label={copy.kind}
                    value={data.madeToOrder ? 'made_to_order' : 'stock'}
                    onChange={(e) => form.setData('madeToOrder', e.target.value === 'made_to_order')}
                    options={copy.kinds}
                />
                {data.madeToOrder ? number('productionDays', copy.productionDays) : <span />}
                {number('weightGrams', copy.weight)}
                {text('label', copy.label, 30)}
                {number('length', copy.length, '0.01')}
                {number('width', copy.width, '0.01')}
                {number('height', copy.height, '0.01')}
                <div className="sm:col-span-2">
                    <CheckboxField
                        label={copy.activeLabel}
                        checked={data.active}
                        onChange={(e) => form.setData('active', e.target.checked)}
                    />
                    <CheckboxField
                        label={copy.featuredLabel}
                        checked={data.featured}
                        onChange={(e) => form.setData('featured', e.target.checked)}
                    />
                </div>
                <div className="flex flex-wrap gap-3 sm:col-span-2">
                    <Button type="submit" loading={form.processing}>
                        {common.save}
                    </Button>
                    {!product && (
                        <Button type="submit" name="returnToList" variant="secondary" disabled={form.processing}>
                            {copy.saveAndBack}
                        </Button>
                    )}
                </div>
            </div>

            <div>
                <p className="mb-3 font-display text-sm font-bold tracking-[0.06em] uppercase">{copy.preview}</p>
                <div className="max-w-80">
                    <TicketCard
                        label={data.label || null}
                        title={data.name || '—'}
                        price={money(toCents(data.price))}
                        comparePrice={data.comparePrice ? money(toCents(data.comparePrice)) : null}
                        meta={data.madeToOrder ? t.store.madeToOrder(Number(data.productionDays) || 0) : t.store.ready}
                        art={
                            <ProductArt
                                image={product?.images[0]?.url ?? null}
                                alt={product?.images[0]?.alt ?? data.name}
                                sizes="20rem"
                            />
                        }
                    />
                </div>
            </div>
        </form>
    );
}

function VariantRow({ product, variant }: { product: Product; variant: Variant }) {
    const form = useForm({
        name: variant.name,
        sku: variant.sku,
        priceDelta: reais(variant.priceDeltaCents),
        active: variant.active,
    });
    const stock = useForm({ quantity: String(variant.stock ?? 0), reason: '' });
    return (
        <li className="space-y-4 py-5">
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.put(`/painel/produtos/${product.id}/variantes/${variant.id}`, { preserveScroll: true });
                }}
                className="grid items-end gap-3 sm:grid-cols-[minmax(0,1fr)_10rem_8rem_auto]"
            >
                <TextField
                    label={copy.variantName}
                    value={form.data.name}
                    onChange={(e) => form.setData('name', e.target.value)}
                    error={form.errors.name}
                />
                <TextField
                    label={copy.sku}
                    value={form.data.sku}
                    onChange={(e) => form.setData('sku', e.target.value.toUpperCase())}
                    error={form.errors.sku}
                />
                <TextField
                    type="number"
                    step="0.01"
                    label={copy.priceDelta}
                    value={form.data.priceDelta}
                    onChange={(e) => form.setData('priceDelta', e.target.value)}
                />
                <Button type="submit" variant="secondary" loading={form.processing}>
                    {common.save}
                </Button>
                <div className="sm:col-span-4">
                    <CheckboxField
                        label={copy.variantActive}
                        checked={form.data.active}
                        onChange={(e) => form.setData('active', e.target.checked)}
                    />
                </div>
            </form>
            {!product.madeToOrder && (
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        stock.post(`/painel/produtos/${product.id}/variantes/${variant.id}/estoque`, {
                            preserveScroll: true,
                            onSuccess: () => stock.setData('reason', ''),
                        });
                    }}
                    className="grid items-end gap-3 rounded-2xl bg-moonlight p-4 ring-1 ring-night/10 sm:grid-cols-[8rem_minmax(0,1fr)_auto]"
                >
                    <TextField
                        type="number"
                        min={0}
                        label={copy.stockQuantity}
                        value={stock.data.quantity}
                        onChange={(e) => stock.setData('quantity', e.target.value)}
                        error={stock.errors.quantity}
                    />
                    <TextField
                        label={copy.stockReason}
                        value={stock.data.reason}
                        onChange={(e) => stock.setData('reason', e.target.value)}
                        error={stock.errors.reason}
                    />
                    <Button type="submit" variant="secondary" loading={stock.processing}>
                        {copy.stockSave}
                    </Button>
                </form>
            )}
        </li>
    );
}

function NewVariant({ product }: { product: Product }) {
    const form = useForm({ name: '', sku: '', priceDelta: '', active: true });
    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                form.post(`/painel/produtos/${product.id}/variantes`, {
                    preserveScroll: true,
                    onSuccess: () => form.reset(),
                });
            }}
            className="mt-4 grid items-end gap-3 border-t-2 border-dashed border-night/12 pt-5 sm:grid-cols-[minmax(0,1fr)_10rem_8rem_auto]"
        >
            <TextField
                label={copy.variantName}
                value={form.data.name}
                onChange={(e) => form.setData('name', e.target.value)}
                error={form.errors.name}
            />
            <TextField
                label={copy.sku}
                value={form.data.sku}
                onChange={(e) => form.setData('sku', e.target.value.toUpperCase())}
                error={form.errors.sku}
            />
            <TextField
                type="number"
                step="0.01"
                label={copy.priceDelta}
                value={form.data.priceDelta}
                onChange={(e) => form.setData('priceDelta', e.target.value)}
            />
            <Button type="submit" variant="secondary" loading={form.processing}>
                {copy.addVariant}
            </Button>
        </form>
    );
}

function Images({ product }: { product: Product }) {
    const form = useForm<{ image: File | null; alt: string }>({ image: null, alt: '' });
    const [inputKey, setInputKey] = useState(0);
    return (
        <>
            {product.images.length === 0 ? (
                <p className="text-night/60">{copy.imagesEmpty}</p>
            ) : (
                <ul className="grid gap-4 sm:grid-cols-2">
                    {product.images.map((image, i) => (
                        <li key={image.id} className="space-y-2">
                            <img
                                src={image.url}
                                alt={image.alt}
                                className="aspect-square w-full rounded-xl object-cover"
                            />
                            <ImageAlt product={product} image={image} />
                            <span className="flex flex-wrap gap-2">
                                <MoveButtons
                                    onMove={(direction) =>
                                        router.post(
                                            `/painel/produtos/${product.id}/imagens/${image.id}/mover`,
                                            { direction },
                                            { preserveScroll: true },
                                        )
                                    }
                                    first={i === 0}
                                    last={i === product.images.length - 1}
                                    labels={{ up: common.moveUp, down: common.moveDown }}
                                />
                                <ConfirmButton
                                    title={common.confirmRemove}
                                    confirmLabel={common.remove}
                                    lead={image.alt}
                                    onConfirm={(done) =>
                                        router.delete(`/painel/produtos/${product.id}/imagens/${image.id}`, {
                                            preserveScroll: true,
                                            onFinish: done,
                                        })
                                    }
                                >
                                    {common.remove}
                                </ConfirmButton>
                            </span>
                        </li>
                    ))}
                </ul>
            )}
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(`/painel/produtos/${product.id}/imagens`, {
                        preserveScroll: true,
                        forceFormData: true,
                        onSuccess: () => {
                            form.reset();
                            setInputKey((k) => k + 1);
                        },
                    });
                }}
                className="mt-6 space-y-3 border-t-2 border-dashed border-night/12 pt-5"
            >
                <input
                    key={inputKey}
                    type="file"
                    accept="image/*"
                    aria-label={copy.addImage}
                    onChange={(e) => form.setData('image', e.target.files?.[0] ?? null)}
                    className={fileInput}
                />
                {form.errors.image && <p className="text-sm font-semibold">{form.errors.image}</p>}
                <TextField
                    label={copy.imageAlt}
                    value={form.data.alt}
                    onChange={(e) => form.setData('alt', e.target.value)}
                    error={form.errors.alt}
                />
                <p className="text-sm text-night/60">{copy.imageHint}</p>
                <Button type="submit" variant="secondary" loading={form.processing} disabled={!form.data.image}>
                    {copy.addImage}
                </Button>
            </form>
        </>
    );
}

function ImageAlt({ product, image }: { product: Product; image: { id: number; alt: string } }) {
    const form = useForm({ alt: image.alt });
    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                form.put(`/painel/produtos/${product.id}/imagens/${image.id}`, { preserveScroll: true });
            }}
            className="flex items-end gap-2"
        >
            <TextField
                label={copy.imageAlt}
                value={form.data.alt}
                onChange={(e) => form.setData('alt', e.target.value)}
                error={form.errors.alt}
                className="flex-1"
            />
            <Button type="submit" variant="secondary" size="sm" loading={form.processing}>
                {common.save}
            </Button>
        </form>
    );
}

export default function Edit({ product }: { product: Product | null }) {
    const variantName = (id: number) => product?.variants.find((v) => v.id === id)?.name ?? '';
    return (
        <>
            <Head title={`${product?.name ?? copy.newProduct} · ${t.panel.title}`} />
            <Link href="/painel/produtos" className="min-h-11 font-semibold underline underline-offset-4">
                ← {copy.back}
            </Link>
            <Display as="h1" className="mt-6 text-[clamp(1.6rem,1.1rem+2vw,2.4rem)]!">
                {product?.name ?? copy.newProduct}
            </Display>

            <div className="mt-8">
                <ProductForm key={product?.id ?? 'new'} product={product} />
            </div>

            {product && (
                <div className="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
                    <div className="space-y-8">
                        <PanelSection title={copy.variants}>
                            <ul className="divide-y-2 divide-dashed divide-night/12">
                                {product.variants.map((variant) => (
                                    <VariantRow key={variant.id} product={product} variant={variant} />
                                ))}
                            </ul>
                            <NewVariant product={product} />
                        </PanelSection>
                        {!product.madeToOrder && (
                            <PanelSection title={copy.history}>
                                {product.stockHistory.length === 0 ? (
                                    <p className="text-night/60">{copy.historyEmpty}</p>
                                ) : (
                                    <ol className="divide-y divide-night/10 text-sm">
                                        {product.stockHistory.map((m, i) => (
                                            <li key={i} className="flex flex-wrap justify-between gap-2 py-2">
                                                <span>
                                                    <strong className={m.delta < 0 ? 'text-night' : 'text-horizon'}>
                                                        {m.delta > 0 ? `+${m.delta}` : m.delta}
                                                    </strong>{' '}
                                                    {variantName(m.variantId)} · {m.reason}
                                                </span>
                                                <span className="text-night/60">
                                                    {m.after} · {m.actor ?? t.panel.audit.system} ·{' '}
                                                    {new Date(m.at.replace(' ', 'T') + 'Z').toLocaleString('pt-BR', {
                                                        dateStyle: 'short',
                                                        timeStyle: 'short',
                                                        timeZone: 'America/Sao_Paulo',
                                                    })}
                                                </span>
                                            </li>
                                        ))}
                                    </ol>
                                )}
                            </PanelSection>
                        )}
                    </div>
                    <PanelSection title={copy.images}>
                        <Images product={product} />
                    </PanelSection>
                </div>
            )}
        </>
    );
}

Edit.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
