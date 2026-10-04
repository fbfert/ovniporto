import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { PanelSection } from '@/Components/Panel/PanelSection';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField, TextField } from '@/Components/Ui/Fields';
import { Modal } from '@/Components/Ui/Modal';
import { Badge, Display } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { postJson } from '@/lib/http';
import { PanelLayout } from '@/Layouts/PanelLayout';
import type { SharedProps } from '@/types';

const copy = t.panel.orders;

interface Order {
    number: string;
    status: string;
    createdAt: string;
    paidAt: string | null;
    customer: { name: string | null; email: string | null; cpf: string | null; phone: string | null };
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
        sku: string;
        quantity: number;
        unitPriceCents: number;
        lineCents: number;
    }[];
    subtotalCents: number;
    shippingCents: number;
    totalCents: number;
    payment: { method: string | null; orderId: string | null; captureId: string | null };
    events: { from: string | null; to: string; actor: string; note: string | null; at: string }[];
}

type Dialog = 'cancel' | 'refund' | 'ship' | null;

const when = (iso: string) =>
    new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });

function CpfReveal({ number, masked }: { number: string; masked: string | null }) {
    const [cpf, setCpf] = useState<string | null>(null);
    return (
        <span className="inline-flex flex-wrap items-center gap-2">
            <span className="tabular-nums">{cpf ?? masked ?? '—'}</span>
            {masked && !cpf && (
                <button
                    type="button"
                    onClick={async () =>
                        setCpf((await postJson<{ cpf: string | null }>(`/painel/pedidos/${number}/cpf`, {})).cpf)
                    }
                    className="min-h-11 text-sm font-semibold underline underline-offset-4"
                    title={copy.revealHint}
                >
                    {copy.revealCpf}
                </button>
            )}
        </span>
    );
}

function ActionDialog({ dialog, order, onClose }: { dialog: Dialog; order: Order; onClose: () => void }) {
    const form = useForm({ reason: '', tracking: '', confirm: false });
    const path = { cancel: 'cancelar', refund: 'reembolsar', ship: 'enviado' } as const;
    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (dialog)
            form.post(`/painel/pedidos/${order.number}/${path[dialog]}`, { preserveScroll: true, onSuccess: onClose });
    };
    const title = dialog === 'cancel' ? copy.cancelTitle : dialog === 'refund' ? copy.refundTitle : copy.shipTitle;

    return (
        <Modal open={dialog !== null} onClose={onClose} title={title}>
            <form onSubmit={submit} className="space-y-5">
                {dialog === 'refund' && <p className="text-moonlight/80">{copy.refundLead(money(order.totalCents))}</p>}
                {dialog === 'ship' ? (
                    <TextField
                        tone="dark"
                        label={copy.trackingLabel}
                        value={form.data.tracking}
                        onChange={(e) => form.setData('tracking', e.target.value)}
                        error={form.errors.tracking}
                    />
                ) : (
                    <TextField
                        tone="dark"
                        label={copy.reason}
                        value={form.data.reason}
                        onChange={(e) => form.setData('reason', e.target.value)}
                        error={form.errors.reason}
                        autoFocus
                    />
                )}
                {dialog === 'refund' && (
                    <CheckboxField
                        tone="dark"
                        label={copy.refundConfirm}
                        checked={form.data.confirm}
                        onChange={(e) => form.setData('confirm', e.target.checked)}
                        error={form.errors.confirm}
                    />
                )}
                <div className="flex flex-wrap gap-3">
                    <Button type="submit" variant="car" loading={form.processing}>
                        {copy.confirm}
                    </Button>
                    <Button variant="ghost" tone="dark" onClick={onClose}>
                        {t.panel.content.cancel}
                    </Button>
                </div>
            </form>
        </Modal>
    );
}

export default function Show({
    order,
    actions,
    supplierSummary,
}: {
    order: Order;
    actions: string[];
    supplierSummary: string;
}) {
    const { errors } = usePage<SharedProps>().props;
    const [dialog, setDialog] = useState<Dialog>(null);
    const [copied, setCopied] = useState(false);
    const [busy, setBusy] = useState<string | null>(null);

    const direct = (action: string, path: string) => {
        setBusy(action);
        router.post(
            `/painel/pedidos/${order.number}/${path}`,
            {},
            { preserveScroll: true, onFinish: () => setBusy(null) },
        );
    };
    const run = (action: string) => {
        if (action === 'production') direct(action, 'producao');
        if (action === 'label') direct(action, 'etiqueta');
        if (action === 'deliver') direct(action, 'entregue');
        if (action === 'ship') setDialog('ship');
        if (action === 'cancel') setDialog('cancel');
        if (action === 'refund') setDialog('refund');
    };

    return (
        <>
            <Head title={`${order.number} · ${t.panel.title}`} />
            <Link href="/painel/pedidos" className="min-h-11 font-semibold underline underline-offset-4">
                ← {copy.back}
            </Link>
            <div className="mt-6 flex flex-wrap items-center gap-3">
                <Display as="h1" className="text-[clamp(1.6rem,1.1rem+2vw,2.4rem)]!">
                    {order.number}
                </Display>
                <Badge tone={order.status === 'paid' ? 'car' : 'neutral'}>{t.order.status[order.status]}</Badge>
            </div>

            {errors.order && (
                <p role="alert" className="mt-4 rounded-xl bg-car px-4 py-3 font-semibold">
                    {errors.order}
                </p>
            )}

            {actions.length > 0 && (
                <div className="mt-6 flex flex-wrap gap-3">
                    {actions.map((action) => (
                        <Button
                            key={action}
                            variant={action === 'refund' || action === 'cancel' ? 'secondary' : 'primary'}
                            onClick={() => run(action)}
                            loading={busy === action}
                        >
                            {copy.actions[action]}
                        </Button>
                    ))}
                    <a
                        href={`/painel/pedidos/${order.number}/ordem-de-producao.pdf`}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex min-h-11 items-center px-2 font-semibold underline underline-offset-4"
                    >
                        {copy.pdf}
                    </a>
                </div>
            )}

            <div className="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                <div className="space-y-8">
                    <PanelSection title={t.order.items}>
                        <ul className="divide-y-2 divide-dashed divide-night/12">
                            {order.items.map((item, i) => (
                                <li key={i} className="flex justify-between gap-3 py-3">
                                    <span>
                                        <span className="font-semibold">
                                            {item.quantity} × {item.name}
                                        </span>
                                        <span className="block text-sm text-night/60">
                                            {item.variant} · SKU {item.sku} · {money(item.unitPriceCents)}
                                        </span>
                                    </span>
                                    <span className="tabular-nums">{money(item.lineCents)}</span>
                                </li>
                            ))}
                        </ul>
                        <dl className="mt-3 space-y-1">
                            <div className="flex justify-between text-night/70">
                                <dt>{t.order.subtotal}</dt>
                                <dd className="tabular-nums">{money(order.subtotalCents)}</dd>
                            </div>
                            <div className="flex justify-between text-night/70">
                                <dt>{t.order.shipping}</dt>
                                <dd className="tabular-nums">{money(order.shippingCents)}</dd>
                            </div>
                            <div className="flex justify-between font-semibold">
                                <dt>{t.order.total}</dt>
                                <dd className="tabular-nums">{money(order.totalCents)}</dd>
                            </div>
                        </dl>
                    </PanelSection>

                    <PanelSection
                        title={copy.supplier}
                        action={
                            <button
                                type="button"
                                className="min-h-11 text-sm font-semibold underline underline-offset-4"
                                onClick={async () => {
                                    await navigator.clipboard?.writeText(supplierSummary);
                                    setCopied(true);
                                }}
                            >
                                {copied ? copy.copied : copy.copy}
                            </button>
                        }
                    >
                        <pre className="text-sm whitespace-pre-wrap">{supplierSummary}</pre>
                    </PanelSection>

                    <section aria-labelledby="linha-pedido">
                        <h2 id="linha-pedido" className="font-display text-sm font-bold tracking-[0.06em] uppercase">
                            {copy.timeline}
                        </h2>
                        <ol className="mt-3 space-y-3 border-l-2 border-dashed border-horizon/40 pl-4">
                            {order.events.map((event, i) => (
                                <li key={i} className="text-sm">
                                    <strong className="font-semibold">{t.order.status[event.to]}</strong>
                                    <span className="text-night/60">
                                        {' '}
                                        · {copy.actor[event.actor] ?? event.actor} · {when(event.at)}
                                    </span>
                                    {event.note && <p className="mt-1 text-night/75">{event.note}</p>}
                                </li>
                            ))}
                        </ol>
                    </section>
                </div>

                <div className="space-y-8">
                    <PanelSection title={copy.customer}>
                        <dl className="space-y-2">
                            <div>
                                <dt className="text-sm text-night/60">{t.checkout.name}</dt>
                                <dd>{order.customer.name ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-night/60">{t.checkout.email}</dt>
                                <dd className="break-all">{order.customer.email ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-night/60">{t.checkout.phone}</dt>
                                <dd>{order.customer.phone ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-night/60">{t.checkout.cpf}</dt>
                                <dd>
                                    <CpfReveal number={order.number} masked={order.customer.cpf} />
                                </dd>
                            </div>
                        </dl>
                    </PanelSection>

                    <PanelSection title={t.order.delivery}>
                        {order.pickup || !order.address ? (
                            <p>{t.order.pickup}</p>
                        ) : (
                            <p>
                                {order.address.street}, {order.address.number}
                                {order.address.complement ? ` · ${order.address.complement}` : ''} ·{' '}
                                {order.address.district} · {order.address.city}/{order.address.state} ·{' '}
                                {order.address.cep}
                                {order.shipping?.service && (
                                    <span className="block text-sm text-night/60">
                                        {order.shipping.carrier} · {order.shipping.service}
                                    </span>
                                )}
                            </p>
                        )}
                        {order.tracking && (
                            <p className="mt-2 text-sm">
                                {t.order.tracking}: <strong>{order.tracking.code}</strong>
                            </p>
                        )}
                    </PanelSection>

                    <PanelSection title={copy.payment}>
                        <dl className="space-y-2 text-sm">
                            <div>
                                <dt className="text-night/60">{copy.payment}</dt>
                                <dd>{order.payment.method ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-night/60">{copy.paymentId}</dt>
                                <dd className="break-all">{order.payment.orderId ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-night/60">{copy.captureId}</dt>
                                <dd className="break-all">{order.payment.captureId ?? '—'}</dd>
                            </div>
                        </dl>
                    </PanelSection>
                </div>
            </div>

            <ActionDialog key={dialog ?? 'none'} dialog={dialog} order={order} onClose={() => setDialog(null)} />
        </>
    );
}

Show.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
