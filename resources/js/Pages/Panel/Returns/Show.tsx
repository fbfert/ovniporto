import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { PanelSection } from '@/Components/Panel/PanelSection';
import { Button } from '@/Components/Ui/Button';
import { SelectField, TextareaField } from '@/Components/Ui/Fields';
import { Badge, Display } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.returns;

interface ReturnRequest {
    protocol: string;
    status: string;
    /** Statuses this request may move to; empty once it is closed. */
    next: string[];
    reason: string;
    /** Null once the order was anonymized or the account deleted. */
    name: string | null;
    email: string | null;
    message: string | null;
    withinWithdrawal: boolean;
    deliveredAt: string | null;
    createdAt: string;
    order: { number: string; status: string; totalCents: number };
    events: { from: string | null; to: string; actor: string | null; note: string | null; at: string }[];
}

const when = (iso: string) =>
    new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });

/** The request, the order it belongs to, its history and the status change with a mandatory note. */
export default function Show({ request }: { request: ReturnRequest }) {
    return (
        <>
            <Head title={`${request.protocol} · ${t.panel.title}`} />
            <Link
                href="/painel/pedidos/devolucoes"
                className="inline-flex min-h-11 items-center text-sm font-semibold underline underline-offset-4"
            >
                ← {copy.backToList}
            </Link>
            <div className="mt-2 flex flex-wrap items-center gap-3">
                <Display as="h1" className="text-[clamp(1.6rem,1.1rem+2vw,2.4rem)]!">
                    {request.protocol}
                </Display>
                <Badge tone={request.status === 'received' ? 'car' : 'neutral'}>{copy.status[request.status]}</Badge>
                <Badge tone={request.withinWithdrawal ? 'beam' : 'horizon'}>
                    {request.withinWithdrawal ? copy.within : copy.outside}
                </Badge>
            </div>

            <div className="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                <div className="space-y-8">
                    <PanelSection title={copy.reasons[request.reason] ?? request.reason}>
                        <dl className="space-y-3">
                            <div>
                                <dt className="text-sm text-night/60">{copy.buyer}</dt>
                                <dd>{request.name ? `${request.name} · ${request.email}` : copy.anonymized}</dd>
                            </div>
                            {request.message && (
                                <div>
                                    <dt className="text-sm text-night/60">{copy.message}</dt>
                                    <dd className="whitespace-pre-line">{request.message}</dd>
                                </div>
                            )}
                            <div>
                                <dt className="text-sm text-night/60">{copy.requestedAt}</dt>
                                <dd className="tabular-nums">{when(request.createdAt)}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-night/60">{copy.deliveredAt}</dt>
                                <dd>{request.deliveredAt ? when(request.deliveredAt) : copy.notDelivered}</dd>
                            </div>
                        </dl>
                        <p className="mt-5 rounded-2xl bg-beam-glow/30 px-4 py-3 text-sm">{copy.returnShipping}</p>
                    </PanelSection>

                    <PanelSection title={copy.history}>
                        <ol className="space-y-3">
                            {request.events.map((event, i) => (
                                <li key={i} className="text-sm">
                                    <span className="font-semibold">{copy.status[event.to]}</span>
                                    <span className="text-night/60">
                                        {' · '}
                                        {when(event.at)}
                                        {event.actor && ` · @${event.actor}`}
                                    </span>
                                    {event.note && <span className="block text-night/75">{event.note}</span>}
                                </li>
                            ))}
                        </ol>
                    </PanelSection>
                </div>

                <div className="space-y-8">
                    <PanelSection title={copy.order}>
                        <p className="font-display font-bold tracking-[0.03em]">{request.order.number}</p>
                        <p className="mt-1 text-sm text-night/65">
                            {t.order.status[request.order.status]} · {money(request.order.totalCents)}
                        </p>
                        <Link
                            href={`/painel/pedidos/${request.order.number}`}
                            className="mt-3 inline-flex min-h-11 items-center font-semibold underline underline-offset-4"
                        >
                            {copy.openOrder}
                        </Link>
                    </PanelSection>

                    {request.next.length > 0 ? (
                        <StatusForm request={request} />
                    ) : (
                        <p className="rounded-[22px] border-2 border-dashed border-night/20 p-6 text-center font-script text-xl text-horizon">
                            {copy.closed}
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

function StatusForm({ request }: { request: ReturnRequest }) {
    const form = useForm({ status: request.next[0] ?? '', note: '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(`/painel/pedidos/devolucoes/${request.protocol}/status`, {
            preserveScroll: true,
            onSuccess: () => form.reset('note'),
        });
    };

    return (
        <PanelSection title={copy.moveTitle}>
            <form onSubmit={submit} className="flex flex-col gap-4">
                <SelectField
                    label={copy.moveTo}
                    options={request.next.map((status) => ({ value: status, label: copy.status[status] ?? status }))}
                    value={form.data.status}
                    onChange={(e) => form.setData('status', e.target.value)}
                    error={form.errors.status}
                />
                <TextareaField
                    label={copy.note}
                    rows={3}
                    maxLength={500}
                    value={form.data.note}
                    onChange={(e) => form.setData('note', e.target.value)}
                    error={form.errors.note}
                    required
                />
                <Button type="submit" className="self-start" loading={form.processing}>
                    {copy.save}
                </Button>
            </form>
        </PanelSection>
    );
}

Show.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
