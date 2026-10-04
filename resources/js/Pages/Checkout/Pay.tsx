import { router } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { PayPalButtons } from '@/Components/Store/PayPalButtons';
import { Button } from '@/Components/Ui/Button';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { postJson } from '@/lib/http';
import { PublicLayout } from '@/Layouts/PublicLayout';
import { track } from '@/lib/analytics';

const copy = t.pay;

interface Order {
    number: string;
    totalCents: number;
    subtotalCents: number;
    shippingCents: number;
    pickup: boolean;
    items: { name: string; variant: string; quantity: number; lineCents: number }[];
}

interface Props {
    order: Order;
    payment: { simulated: boolean; clientId: string | null };
}

/** Neither call sends an amount: the server creates the provider order with its own total. */
const startPayment = async (number: string) => (await postJson<{ id: string }>(`/pedido/${number}/pagamento`, {})).id;

export default function Pay({ order, payment }: Props) {
    const [error, setError] = useState<string | null>(null);
    const [processing, setProcessing] = useState(false);
    const [pending, setPending] = useState(false);

    const approve = async () => {
        setProcessing(true);
        setError(null);
        try {
            const result = await postJson<{ paid: boolean; status: string; redirect: string }>(
                `/pedido/${order.number}/aprovar`,
                {},
            );
            if (result.paid) {
                track('compra_concluida');
                router.visit(result.redirect);
            } else {
                setPending(true);
            }
        } catch {
            setError(copy.failed);
        } finally {
            setProcessing(false);
        }
    };

    const simulate = async () => {
        try {
            await startPayment(order.number);
            await approve();
        } catch {
            setError(copy.failed);
        }
    };

    return (
        <>
            <SeoHead />
            <Section tone="light" innerClassName="pt-32! sm:pt-36!">
                <div className="mx-auto grid max-w-4xl gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                    <div>
                        <Eyebrow>{copy.orderLabel(order.number)}</Eyebrow>
                        <Display as="h1" className="mt-2 text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                            {copy.title}
                        </Display>
                        <p className="mt-4 max-w-[46ch] text-night/75">{copy.lead}</p>
                        <ul className="mt-8 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                            {order.items.map((item, i) => (
                                <li key={i} className="flex justify-between gap-3 py-3">
                                    <span>
                                        {item.quantity} × {item.name}{' '}
                                        <span className="text-night/60">· {item.variant}</span>
                                    </span>
                                    <span className="tabular-nums">{money(item.lineCents)}</span>
                                </li>
                            ))}
                        </ul>
                        <dl className="mt-4 space-y-1">
                            <div className="flex justify-between text-night/70">
                                <dt>{t.checkout.subtotal}</dt>
                                <dd className="tabular-nums">{money(order.subtotalCents)}</dd>
                            </div>
                            <div className="flex justify-between text-night/70">
                                <dt>{t.checkout.shipping}</dt>
                                <dd className="tabular-nums">
                                    {order.pickup ? t.checkout.free : money(order.shippingCents)}
                                </dd>
                            </div>
                            <div className="flex items-baseline justify-between pt-2">
                                <dt className="font-semibold">{t.checkout.total}</dt>
                                <dd className="font-display text-3xl font-extrabold text-horizon tabular-nums">
                                    {money(order.totalCents)}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div className="self-start rounded-[26px] bg-night/5 p-6 ring-1 ring-night/10">
                        {pending ? (
                            <p role="status" className="font-semibold">
                                {copy.pending}
                            </p>
                        ) : payment.simulated ? (
                            <div className="space-y-4">
                                <p className="rounded-xl bg-car/30 px-4 py-3 text-sm font-semibold">{copy.simulated}</p>
                                <Button
                                    variant="car"
                                    size="lg"
                                    className="w-full"
                                    onClick={simulate}
                                    loading={processing}
                                >
                                    {copy.simulate}
                                </Button>
                            </div>
                        ) : payment.clientId ? (
                            <PayPalButtons
                                clientId={payment.clientId}
                                createOrder={() => startPayment(order.number)}
                                onApprove={approve}
                                onError={setError}
                            />
                        ) : (
                            <p role="alert" className="font-semibold">
                                {copy.failed}
                            </p>
                        )}
                        {processing && <p className="mt-3 text-sm text-night/60">{copy.processing}</p>}
                        {error && (
                            <p role="alert" className="mt-3 font-semibold">
                                {error}
                            </p>
                        )}
                        <p className="mt-4 text-sm text-night/60">{copy.expires}</p>
                    </div>
                </div>
            </Section>
        </>
    );
}

Pay.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
