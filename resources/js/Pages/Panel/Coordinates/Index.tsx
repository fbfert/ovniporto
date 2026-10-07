import { Head, router, useForm } from '@inertiajs/react';
import type { FormEvent, InputHTMLAttributes, ReactNode } from 'react';
import { ConfirmButton } from '@/Components/Panel/ConfirmButton';
import { PanelSection } from '@/Components/Panel/PanelSection';
import { Button } from '@/Components/Ui/Button';
import { SelectField, TextField } from '@/Components/Ui/Fields';
import { Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.coordinates;

export interface Field {
    value: string | null;
    env: string | null;
    fromPanel: boolean;
    set?: boolean;
}

type Group = Record<string, Field>;

const EMPTY: Field = { value: null, env: null, fromPanel: false };
const pick = (group: Group, key: string): Field => group[key] ?? EMPTY;

export interface Integration {
    name: string;
    configured: boolean;
    mode: string | null;
    attention: boolean;
}

export interface TestResult {
    kind: 'mail' | 'alert';
    to: string | null;
    sent: boolean;
    problem: string | null;
    detail: string | null;
}

interface Props {
    groups: { correio: Group; alertas: Group; frete: Group };
    integrations: Integration[];
    alertsMissing: boolean;
    test: TestResult | null;
}

/** The form starts from what the panel saved; the .env value shows as the placeholder. */
function initial(group: Group, secret: string[] = []) {
    return Object.fromEntries(
        Object.entries(group).map(([key, field]) => [key, secret.includes(key) ? '' : (field.value ?? '')]),
    );
}

/** Where the value in force comes from, under every field. */
function Origin({ field }: { field: Field }) {
    const fromEnv = !field.fromPanel && (field.env !== null || field.set);
    return (
        <p className="mt-1.5 flex items-center gap-2 px-5 text-sm text-night/70">
            <span
                aria-hidden
                className={`size-2 rotate-45 ${field.fromPanel ? 'bg-horizon' : fromEnv ? 'ring-[1.5px] ring-night/50' : 'bg-night/15'}`}
            />
            {field.fromPanel ? copy.fromPanel : fromEnv ? copy.fromEnv : copy.unset}
        </p>
    );
}

function SettingField({
    name,
    label,
    field,
    form,
    className = '',
    ...input
}: {
    name: string;
    label: string;
    field: Field;
    form: ReturnType<typeof useForm<Record<string, string>>>;
    className?: string;
} & Omit<InputHTMLAttributes<HTMLInputElement>, 'form' | 'name'>) {
    return (
        <div className={className}>
            <TextField
                label={label}
                name={name}
                value={form.data[name] ?? ''}
                placeholder={field.env ?? undefined}
                onChange={(e) => form.setData(name, e.target.value)}
                error={form.errors[name]}
                {...input}
            />
            <Origin field={field} />
        </div>
    );
}

function TestOutcome({ result }: { result: TestResult }) {
    const ok = result.sent;
    return (
        <div
            className={`mt-4 rounded-2xl px-5 py-4 ${ok ? 'bg-beam-glow/45 ring-1 ring-beam/50' : 'bg-car/25 ring-1 ring-car'}`}
        >
            <p className="font-semibold">
                {ok
                    ? result.kind === 'mail'
                        ? copy.result.sent(result.to ?? '')
                        : copy.result.alertSent
                    : `${copy.result.failed} ${copy.result.problems[result.problem ?? 'other'] ?? copy.result.problems.other}`}
            </p>
            {!ok && result.detail && (
                <p className="mt-2 text-sm text-night/80">
                    <span className="font-semibold">{copy.result.answer}:</span>{' '}
                    <span className="break-words">{result.detail}</span>
                </p>
            )}
        </div>
    );
}

/** The result of the last test button, read out once where the button is. */
function TestSlot({ kind, test }: { kind: TestResult['kind']; test: TestResult | null }) {
    return <div aria-live="polite">{test?.kind === kind && <TestOutcome result={test} />}</div>;
}

function TestButton({ url, label }: { url: string; label: string }) {
    const form = useForm({});
    return (
        <Button variant="secondary" loading={form.processing} onClick={() => form.post(url, { preserveScroll: true })}>
            {label}
        </Button>
    );
}

function useGroupForm(group: Group, url: string, secret: string[] = []) {
    const form = useForm<Record<string, string>>(initial(group, secret));
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(url, {
            preserveScroll: true,
            onSuccess: () => secret.forEach((key) => form.setData(key, '')),
        });
    };
    return { form, submit };
}

function MailForm({ group, test }: { group: Group; test: TestResult | null }) {
    const { form, submit } = useGroupForm(group, '/painel/coordenadas/correio', ['password']);
    const password = pick(group, 'password');
    const passwordNote = password.fromPanel
        ? copy.mail.passwordSet
        : password.set
          ? copy.mail.passwordFromEnv
          : copy.mail.passwordUnset;
    const field = (
        name: string,
        extra: Omit<Parameters<typeof SettingField>[0], 'name' | 'label' | 'field' | 'form'> = {},
    ) => (
        <SettingField
            name={name}
            label={copy.mail[name as keyof typeof copy.mail] as string}
            field={pick(group, name)}
            form={form}
            {...extra}
        />
    );
    return (
        <>
            <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                {field('host', { className: 'sm:col-span-2', autoComplete: 'off', spellCheck: false })}
                {field('port', { type: 'number', inputMode: 'numeric', min: 1, max: 65535 })}
                <div>
                    <SelectField
                        label={copy.mail.scheme}
                        value={form.data.scheme ?? ''}
                        onChange={(e) => form.setData('scheme', e.target.value)}
                        options={copy.mail.schemes}
                        error={form.errors.scheme}
                    />
                    <Origin field={pick(group, 'scheme')} />
                </div>
                {field('username', { autoComplete: 'off', spellCheck: false })}
                <div>
                    <TextField
                        type="password"
                        label={copy.mail.password}
                        value={form.data.password ?? ''}
                        onChange={(e) => form.setData('password', e.target.value)}
                        error={form.errors.password}
                        autoComplete="new-password"
                    />
                    <p className="mt-1.5 px-5 text-sm text-night/70">{passwordNote}</p>
                </div>
                {field('fromAddress', { type: 'email', autoComplete: 'off' })}
                {field('fromName')}
                <div className="flex flex-wrap items-center gap-3 sm:col-span-2">
                    <Button type="submit" loading={form.processing}>
                        {copy.save}
                    </Button>
                    {password.fromPanel && (
                        <ConfirmButton
                            title={copy.mail.removeTitle}
                            lead={copy.mail.removeLead}
                            confirmLabel={copy.mail.removeConfirm}
                            onConfirm={(done) =>
                                router.delete('/painel/coordenadas/senha-smtp', {
                                    preserveScroll: true,
                                    onFinish: done,
                                })
                            }
                        >
                            {copy.mail.removePassword}
                        </ConfirmButton>
                    )}
                </div>
            </form>
            <div className="mt-6 border-t-2 border-dashed border-night/12 pt-5">
                <div className="flex flex-wrap items-center gap-x-4 gap-y-2">
                    <TestButton url="/painel/coordenadas/teste-email" label={copy.mail.test} />
                    <p className="text-sm text-night/70">{copy.mail.testHint}</p>
                </div>
                <TestSlot kind="mail" test={test} />
            </div>
        </>
    );
}

function AlertsForm({ group, missing, test }: { group: Group; missing: boolean; test: TestResult | null }) {
    const { form, submit } = useGroupForm(group, '/painel/coordenadas/alertas');
    return (
        <>
            <form onSubmit={submit} className="flex flex-wrap items-start gap-4">
                <SettingField
                    name="email"
                    label={copy.alerts.email}
                    field={pick(group, 'email')}
                    form={form}
                    type="email"
                    className="min-w-0 flex-1 basis-72"
                />
                <Button type="submit" loading={form.processing} className="sm:mt-7">
                    {copy.save}
                </Button>
            </form>
            {missing && (
                <p role="note" className="mt-4 rounded-2xl bg-car/25 px-5 py-3 text-sm font-medium ring-1 ring-car">
                    {copy.alerts.missing}
                </p>
            )}
            <div className="mt-5">
                <TestButton url="/painel/coordenadas/teste-alerta" label={copy.alerts.test} />
                <TestSlot kind="alert" test={test} />
            </div>
        </>
    );
}

function ShippingForm({ group }: { group: Group }) {
    const { form, submit } = useGroupForm(group, '/painel/coordenadas/frete');
    const field = (
        name: keyof typeof copy.shipping,
        extra: Omit<Parameters<typeof SettingField>[0], 'name' | 'label' | 'field' | 'form'> = {},
    ) => (
        <SettingField
            name={name}
            label={copy.shipping[name] as string}
            field={pick(group, name)}
            form={form}
            {...extra}
        />
    );
    const size = { type: 'number', inputMode: 'numeric', min: 1, max: 100, step: 1 } as const;
    return (
        <form onSubmit={submit} className="space-y-6">
            <fieldset className="grid gap-4 sm:grid-cols-6">
                <legend className="mb-3 font-semibold">{copy.shipping.sender}</legend>
                {field('name', { className: 'sm:col-span-4' })}
                {field('document', { className: 'sm:col-span-2', inputMode: 'numeric' })}
                {field('phone', { className: 'sm:col-span-3', type: 'tel', inputMode: 'tel' })}
                {field('email', { className: 'sm:col-span-3', type: 'email' })}
                {field('postalCode', { className: 'sm:col-span-2', inputMode: 'numeric' })}
                {field('street', { className: 'sm:col-span-4' })}
                {field('number', { className: 'sm:col-span-2' })}
                {field('district', { className: 'sm:col-span-4' })}
                {field('city', { className: 'sm:col-span-4' })}
                {field('state', { className: 'sm:col-span-2', maxLength: 2 })}
            </fieldset>
            <fieldset className="grid grid-cols-3 gap-3 sm:gap-4">
                <legend className="mb-3 font-semibold">{copy.shipping.package}</legend>
                {field('packageLength', size)}
                {field('packageWidth', size)}
                {field('packageHeight', size)}
            </fieldset>
            <Button type="submit" loading={form.processing}>
                {copy.save}
            </Button>
        </form>
    );
}

/** Beam green on air, car yellow when it needs a look, unlit when an optional one is simply off. */
function light(item: Integration) {
    if (item.attention) return 'bg-car shadow-[0_0_10px_2px_rgb(252_184_2/0.45)]';
    if (item.configured) return 'bg-beam shadow-[0_0_10px_2px_rgb(84_201_51/0.45)]';
    return 'ring-[1.5px] ring-moonlight/45';
}

/**
 * The integrations that stay on the server, as a strip on the tower's board:
 * a beam-green light when it is on air, a car-yellow one when it needs a look.
 */
export function IntegrationBoard({ integrations }: { integrations: Integration[] }) {
    return (
        <section aria-labelledby="integracoes" className="rounded-[22px] bg-night p-5 text-moonlight sm:p-6">
            <h2 id="integracoes" className="font-display text-sm font-bold tracking-[0.06em] uppercase">
                {copy.integrations.title}
            </h2>
            <p className="mt-2 max-w-prose text-sm text-moonlight/75">{copy.integrations.lead}</p>
            <ul className="mt-5 divide-y-2 divide-dashed divide-moonlight/12 border-y-2 border-dashed border-moonlight/12">
                {integrations.map((item) => {
                    const state = item.configured ? copy.integrations.configured : copy.integrations.missing;
                    const mode = item.mode ? (copy.integrations.modes[item.mode] ?? item.mode) : null;
                    return (
                        <li key={item.name} className="grid grid-cols-[0.625rem_1fr] items-baseline gap-x-3 py-3">
                            <span aria-hidden className={`size-2.5 translate-y-px rounded-full ${light(item)}`} />
                            <span className="font-semibold">{copy.integrations.names[item.name] ?? item.name}</span>
                            <span className="col-start-2 flex flex-wrap gap-x-3 text-sm">
                                <span
                                    className={
                                        item.attention
                                            ? 'text-car'
                                            : item.configured
                                              ? 'text-beam-glow'
                                              : 'text-moonlight/75'
                                    }
                                >
                                    {state}
                                </span>
                                {mode && <span className="text-moonlight/75">{mode}</span>}
                            </span>
                            {item.attention && <span className="sr-only">{copy.integrations.attention}</span>}
                        </li>
                    );
                })}
            </ul>
        </section>
    );
}

export default function Coordinates({ groups, integrations, alertsMissing, test }: Props) {
    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.title}
            </Display>
            <p className="mt-4 max-w-prose text-night/80">{copy.lead}</p>
            <p className="mt-2 max-w-prose text-sm text-night/70">{copy.rule}</p>

            <div className="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1fr)_minmax(0,22rem)] xl:items-start">
                <div className="space-y-8">
                    <PanelSection title={copy.mail.title}>
                        <p className="-mt-1 mb-5 max-w-prose text-sm text-night/70">{copy.mail.lead}</p>
                        <MailForm group={groups.correio} test={test} />
                    </PanelSection>
                    <PanelSection title={copy.alerts.title}>
                        <p className="-mt-1 mb-5 max-w-prose text-sm text-night/70">{copy.alerts.lead}</p>
                        <AlertsForm group={groups.alertas} missing={alertsMissing} test={test} />
                    </PanelSection>
                    <PanelSection title={copy.shipping.title}>
                        <p className="-mt-1 mb-5 max-w-prose text-sm text-night/70">{copy.shipping.lead}</p>
                        <ShippingForm group={groups.frete} />
                    </PanelSection>
                </div>
                <div className="xl:sticky xl:top-28">
                    <IntegrationBoard integrations={integrations} />
                </div>
            </div>
        </>
    );
}

Coordinates.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
