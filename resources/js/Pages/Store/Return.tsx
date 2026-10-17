import { router, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import { SelectField, TextareaField, TextField } from '@/Components/Ui/Fields';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.returnRequest;

interface Props {
    /** Filled when the link came from the order page (?pedido=OVP-...). */
    orderNumber: string;
    reasons: { value: string; label: string }[];
    /** After sending: the protocol (null when the server had nothing to store). */
    sent: { protocol: string | null } | null;
}

/** "/devolucao": the three steps on one side, the form (or the ticket stub with the protocol) on the other. */
export default function ReturnPage({ orderNumber, reasons, sent }: Props) {
    return (
        <>
            <SeoHead />
            <PageCover eyebrow={copy.eyebrow} title={copy.title} lead={copy.lead} />

            <Section tone="light" labelledBy="como-funciona">
                <div className="grid gap-14 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:gap-20">
                    <div>
                        <h2 id="como-funciona" className="sr-only">
                            {copy.formTitle}
                        </h2>
                        <ol className="border-t-2 border-dashed border-night/15">
                            {copy.steps.map((step, index) => (
                                <li
                                    key={step.title}
                                    className="grid grid-cols-[3.5rem_minmax(0,1fr)] gap-4 border-b-2 border-dashed border-night/15 py-6"
                                >
                                    <Display as="span" outlined className="text-4xl leading-none text-horizon">
                                        {String(index + 1).padStart(2, '0')}
                                    </Display>
                                    <div>
                                        <h3 className="font-display text-base leading-tight font-bold tracking-[0.03em] uppercase">
                                            {step.title}
                                        </h3>
                                        <p className="mt-1.5 leading-relaxed text-night/75">{step.text}</p>
                                    </div>
                                </li>
                            ))}
                        </ol>
                        <p className="mt-8 max-w-[52ch] text-sm leading-relaxed text-night/70">{copy.rights}</p>
                    </div>

                    {sent ? (
                        <Stub protocol={sent.protocol} />
                    ) : (
                        <ReturnForm orderNumber={orderNumber} reasons={reasons} />
                    )}
                </div>
            </Section>
        </>
    );
}

function ReturnForm({ orderNumber, reasons }: Pick<Props, 'orderNumber' | 'reasons'>) {
    const form = useForm({
        orderNumber,
        email: '',
        name: '',
        reason: '',
        message: '',
        website: '',
    });
    const orderError = (form.errors as Record<string, string | undefined>).order;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/devolucao', { preserveScroll: true });
    };

    return (
        <form
            onSubmit={submit}
            noValidate
            aria-labelledby="pedido-de-devolucao"
            className="flex flex-col gap-5 rounded-[26px] bg-night p-6 text-moonlight sm:p-8"
        >
            <Display as="h2" id="pedido-de-devolucao" className="text-[clamp(1.3rem,1rem+1.4vw,1.8rem)]!">
                {copy.formTitle}
            </Display>
            {orderError && (
                <p role="alert" className="rounded-2xl bg-car/20 px-4 py-3 text-sm font-semibold ring-1 ring-car">
                    {orderError}
                </p>
            )}
            <div className="grid gap-5 sm:grid-cols-2">
                <TextField
                    tone="dark"
                    label={copy.orderNumber}
                    name="orderNumber"
                    autoComplete="off"
                    autoCapitalize="characters"
                    placeholder="OVP-2026-000123"
                    maxLength={20}
                    value={form.data.orderNumber}
                    onChange={(e) => form.setData('orderNumber', e.target.value.toUpperCase())}
                    error={form.errors.orderNumber}
                    required
                />
                <TextField
                    tone="dark"
                    label={copy.email}
                    type="email"
                    name="email"
                    autoComplete="email"
                    inputMode="email"
                    maxLength={160}
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                    error={form.errors.email}
                    required
                />
            </div>
            <p className="-mt-3 text-sm text-moonlight/60">{copy.orderNumberHint}</p>
            <TextField
                tone="dark"
                label={copy.name}
                name="name"
                autoComplete="name"
                maxLength={120}
                value={form.data.name}
                onChange={(e) => form.setData('name', e.target.value)}
                error={form.errors.name}
                required
            />
            <SelectField
                tone="dark"
                label={copy.reason}
                placeholder={copy.reasonPlaceholder}
                options={reasons}
                value={form.data.reason}
                onChange={(e) => form.setData('reason', e.target.value)}
                error={form.errors.reason}
                required
            />
            <TextareaField
                tone="dark"
                label={copy.message}
                name="message"
                placeholder={copy.messagePlaceholder}
                rows={4}
                maxLength={2000}
                showCount
                value={form.data.message}
                onChange={(e) => form.setData('message', e.target.value)}
                error={form.errors.message}
                required
            />
            <div aria-hidden className="absolute -left-[9999px] h-px w-px overflow-hidden">
                <label>
                    {copy.honeypot}
                    <input
                        type="text"
                        name="website"
                        tabIndex={-1}
                        autoComplete="off"
                        value={form.data.website}
                        onChange={(e) => form.setData('website', e.target.value)}
                    />
                </label>
            </div>
            <Button type="submit" variant="car" size="lg" className="self-start" loading={form.processing}>
                {form.processing ? copy.sending : copy.submit}
            </Button>
        </form>
    );
}

/** The confirmation as a ticket stub: punched edges, a tear line and the protocol to keep. */
function Stub({ protocol }: { protocol: string | null }) {
    return (
        <div
            role="status"
            className="self-start bg-night-blue text-moonlight ticket-punch [--punch-y:42%]"
            style={{ borderRadius: '22px' }}
        >
            <div className="px-7 pt-7 pb-6">
                <Eyebrow tone="dark">{copy.sentEyebrow}</Eyebrow>
                <Display as="h2" className="mt-2 text-[clamp(1.3rem,1rem+1.4vw,1.8rem)]!">
                    {copy.sentTitle}
                </Display>
            </div>
            <div aria-hidden className="mx-6 border-t-2 border-dashed border-moonlight/25" />
            <div className="px-7 pt-6 pb-7">
                {protocol && (
                    <p>
                        <span className="block text-sm text-moonlight/65">{copy.protocol}</span>
                        <span className="font-display text-2xl font-extrabold tracking-[0.04em] text-car tabular-nums">
                            {protocol}
                        </span>
                    </p>
                )}
                <p className="mt-4 max-w-[40ch] leading-relaxed text-moonlight/80">
                    {protocol ? copy.sentLead : copy.sentNoProtocol}
                </p>
                <div className="mt-6 flex flex-wrap gap-3">
                    <Button href="/loja" tone="dark">
                        {copy.backToStore}
                    </Button>
                    <Button
                        variant="ghost"
                        tone="dark"
                        onClick={() => router.visit('/devolucao', { preserveScroll: true })}
                    >
                        {copy.another}
                    </Button>
                </div>
            </div>
        </div>
    );
}

ReturnPage.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
