import type { ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.order;

interface Order {
    number: string;
    status: string;
    createdAt: string;
    customer: { name: string | null; email: string | null; cpf: string | null };
    pickup: boolean;
    address: {
        cep?: string;
        street?: string;
        number?: string;
        complement?: string | null;
        district?: string;
        city: string;
        state: string;
    } | null;
    shipping: { carrier: string | null; service: string | null; days: number | null } | null;
    tracking: { code: string; url: string | null } | null;
    items: {
        name: string;
        variant: string;
        quantity: number;
        lineCents: number;
        madeToOrder: boolean;
        productionDays: number;
    }[];
    subtotalCents: number;
    shippingCents: number;
    totalCents: number;
    events: { from: string | null; to: string; actor: string; note: string | null; at: string }[];
}

const when = (iso: string) =>
    new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });

const TONE: Record<string, 'beam' | 'car' | 'horizon' | 'neutral'> = {
    pending_payment: 'car',
    paid: 'beam',
    in_production: 'horizon',
    shipped: 'horizon',
    delivered: 'beam',
};

/** The order's own flight log: status timeline, what was bought and where it goes. */
export default function Show({ order, payUrl }: { order: Order; payUrl: string | null }) {
    const timeline = order.events.filter((e) => e.from !== e.to);
    return (
        <>
            <SeoHead title={copy.title(order.number)} />
            <Section tone="dark" pattern="stars" innerClassName="pt-32! sm:pt-36!">
                <div className="mx-auto max-w-4xl">
                    <Eyebrow tone="dark">{copy.eyebrow}</Eyebrow>
                    <div className="mt-2 flex flex-wrap items-center gap-4">
                        <Display as="h1" className="text-[clamp(1.6rem,1.1rem+2vw,2.6rem)]!">
                            {order.number}
                        </Display>
                        <Badge tone={TONE[order.status] ?? 'neutral'}>
                            {copy.status[order.status] ?? order.status}
                        </Badge>
                    </div>
                    {payUrl && (
                        <div className="mt-6">
                            <Button href={payUrl} variant="car" size="lg">
                                {copy.payNow}
                            </Button>
                        </div>
                    )}

                    <div className="mt-12 grid gap-10 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                        <section aria-labelledby="linha">
                            <h2 id="linha" className="font-display text-sm font-bold tracking-[0.06em] uppercase">
                                {copy.timeline}
                            </h2>
                            <ol className="mt-4 space-y-5 border-l-2 border-dashed border-beam/40 pl-5">
                                {timeline.map((event, i) => (
                                    <li key={i} className="relative">
                                        <span
                                            aria-hidden
                                            className="absolute top-1.5 -left-[1.72rem] size-3 rounded-full bg-beam shadow-[0_0_10px_var(--color-beam)]"
                                        />
                                        <p className="font-semibold">{copy.status[event.to] ?? event.to}</p>
                                        <p className="text-sm text-moonlight/60">{when(event.at)}</p>
                                        {event.note && <p className="mt-1 text-sm text-moonlight/75">{event.note}</p>}
                                    </li>
                                ))}
                            </ol>
                        </section>

                        <div className="space-y-6">
                            <section aria-labelledby="itens-pedido" className="rounded-[22px] bg-night-blue p-5">
                                <h2
                                    id="itens-pedido"
                                    className="font-display text-sm font-bold tracking-[0.06em] uppercase"
                                >
                                    {copy.items}
                                </h2>
                                <ul className="mt-3 divide-y divide-moonlight/10">
                                    {order.items.map((item, i) => (
                                        <li key={i} className="flex justify-between gap-3 py-2.5">
                                            <span>
                                                {item.quantity} × {item.name}
                                                <span className="block text-sm text-moonlight/60">
                                                    {item.variant}
                                                    {item.madeToOrder &&
                                                        ` · ${t.checkout.madeToOrder(item.productionDays)}`}
                                                </span>
                                            </span>
                                            <span className="tabular-nums">{money(item.lineCents)}</span>
                                        </li>
                                    ))}
                                </ul>
                                <dl className="mt-3 space-y-1 border-t-2 border-dashed border-moonlight/15 pt-3">
                                    <div className="flex justify-between text-moonlight/70">
                                        <dt>{copy.subtotal}</dt>
                                        <dd className="tabular-nums">{money(order.subtotalCents)}</dd>
                                    </div>
                                    <div className="flex justify-between text-moonlight/70">
                                        <dt>{copy.shipping}</dt>
                                        <dd className="tabular-nums">
                                            {order.pickup ? t.checkout.free : money(order.shippingCents)}
                                        </dd>
                                    </div>
                                    <div className="flex items-baseline justify-between pt-1">
                                        <dt className="font-semibold">{copy.total}</dt>
                                        <dd className="font-display text-2xl font-extrabold text-car tabular-nums">
                                            {money(order.totalCents)}
                                        </dd>
                                    </div>
                                </dl>
                            </section>

                            <section aria-labelledby="entrega-pedido" className="rounded-[22px] bg-night-blue p-5">
                                <h2
                                    id="entrega-pedido"
                                    className="font-display text-sm font-bold tracking-[0.06em] uppercase"
                                >
                                    {copy.delivery}
                                </h2>
                                {order.pickup || !order.address ? (
                                    <p className="mt-2 text-moonlight/85">{copy.pickup}</p>
                                ) : (
                                    <p className="mt-2 text-moonlight/85">
                                        {order.address.street}, {order.address.number}
                                        {order.address.complement ? ` · ${order.address.complement}` : ''} ·{' '}
                                        {order.address.district} · {order.address.city}/{order.address.state} ·{' '}
                                        {order.address.cep}
                                        {order.shipping?.service && (
                                            <span className="block text-sm text-moonlight/60">
                                                {order.shipping.carrier} · {order.shipping.service}
                                            </span>
                                        )}
                                    </p>
                                )}
                                {order.tracking && (
                                    <p className="mt-3">
                                        <span className="text-sm text-moonlight/60">{copy.tracking}: </span>
                                        <span className="font-semibold tracking-wide">{order.tracking.code}</span>
                                        {order.tracking.url && (
                                            <a
                                                href={order.tracking.url}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="ml-3 text-sm text-beam-glow underline underline-offset-4"
                                            >
                                                {copy.track}
                                            </a>
                                        )}
                                    </p>
                                )}
                                {order.customer.name && (
                                    <p className="mt-3 text-sm text-moonlight/60">
                                        {copy.buyer}: {order.customer.name} · {order.customer.cpf}
                                    </p>
                                )}
                            </section>
                        </div>
                    </div>
                </div>
            </Section>
        </>
    );
}

Show.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
