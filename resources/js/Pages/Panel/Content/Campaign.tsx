import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { ConfirmButton } from '@/Components/Panel/ConfirmButton';
import { PanelSection } from '@/Components/Panel/PanelSection';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField, SelectField, TextField } from '@/Components/Ui/Fields';
import { Badge, Display } from '@/Components/Ui/Typography';
import { money, shortDate, t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.campaign;
const common = t.panel.content;

interface Settings {
    status: string;
    goalCents: number | null;
    raisedCents: number | null;
    crowdfundingUrl: string | null;
    storeSharePercent: number | null;
}

interface Supporter {
    id: number;
    name: string;
    amountCents: number | null;
    reward: string | null;
    publishName: boolean;
    supportedAt: string | null;
}

interface Sponsor {
    id: number;
    name: string;
    tier: string | null;
    url: string | null;
    logo: string | null;
}

const reais = (cents: number | null) => (cents === null ? '' : String(cents / 100));
const fileInput =
    'block min-h-11 w-full text-sm file:mr-4 file:min-h-11 file:rounded-full file:border-0 file:bg-night file:px-4 file:font-semibold file:text-moonlight';

/** Fixed, on top, impossible to scroll past without seeing: the rule of the project. */
export function CampaignWarning() {
    return (
        <p
            role="note"
            className="sticky top-32 z-10 rounded-2xl bg-car px-5 py-4 font-display text-sm font-extrabold tracking-[0.04em] text-night uppercase shadow-[0_12px_30px_-18px_rgb(6_17_33/0.6)]"
        >
            {copy.warning}
        </p>
    );
}

function SettingsForm({ settings }: { settings: Settings }) {
    const form = useForm({
        status: settings.status,
        goal: reais(settings.goalCents),
        raised: reais(settings.raisedCents),
        crowdfundingUrl: settings.crowdfundingUrl ?? '',
        storeSharePercent: settings.storeSharePercent === null ? '' : String(settings.storeSharePercent),
    });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put('/painel/campanha', { preserveScroll: true });
    };
    const number = (key: 'goal' | 'raised' | 'storeSharePercent', label: string) => (
        <TextField
            type="number"
            min={0}
            step="0.01"
            inputMode="decimal"
            label={label}
            value={form.data[key]}
            onChange={(e) => form.setData(key, e.target.value)}
            error={form.errors[key]}
        />
    );
    return (
        <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
            <SelectField
                label={copy.status}
                value={form.data.status}
                onChange={(e) => form.setData('status', e.target.value)}
                options={copy.statuses}
                error={form.errors.status}
            />
            {number('storeSharePercent', copy.storeShare)}
            {number('goal', copy.goal)}
            {number('raised', copy.raised)}
            <TextField
                type="url"
                label={copy.crowdfundingUrl}
                value={form.data.crowdfundingUrl}
                onChange={(e) => form.setData('crowdfundingUrl', e.target.value)}
                error={form.errors.crowdfundingUrl}
                className="sm:col-span-2"
            />
            <p className="text-sm text-night/60 sm:col-span-2">{copy.storeShareHint}</p>
            <div>
                <Button type="submit" loading={form.processing}>
                    {common.save}
                </Button>
            </div>
        </form>
    );
}

function AddSupporter() {
    const form = useForm({ name: '', amount: '', reward: '', publishName: false, supportedAt: '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/painel/campanha/apoiadores', { preserveScroll: true, onSuccess: () => form.reset() });
    };
    return (
        <form onSubmit={submit} className="grid gap-3 sm:grid-cols-2">
            <TextField
                label={copy.supporterName}
                value={form.data.name}
                onChange={(e) => form.setData('name', e.target.value)}
                error={form.errors.name}
            />
            <TextField
                type="number"
                min={0}
                step="0.01"
                label={copy.supporterAmount}
                value={form.data.amount}
                onChange={(e) => form.setData('amount', e.target.value)}
                error={form.errors.amount}
            />
            <TextField
                label={copy.supporterReward}
                value={form.data.reward}
                onChange={(e) => form.setData('reward', e.target.value)}
            />
            <TextField
                type="date"
                label={copy.supporterDate}
                value={form.data.supportedAt}
                onChange={(e) => form.setData('supportedAt', e.target.value)}
            />
            <CheckboxField
                label={copy.supporterPublish}
                checked={form.data.publishName}
                onChange={(e) => form.setData('publishName', e.target.checked)}
            />
            <div className="sm:col-span-2">
                <Button type="submit" variant="secondary" loading={form.processing}>
                    {copy.addSupporter}
                </Button>
            </div>
        </form>
    );
}

function ImportSupporters() {
    const form = useForm<{ file: File | null }>({ file: null });
    const [inputKey, setInputKey] = useState(0);
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/painel/campanha/apoiadores/importar', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                setInputKey((k) => k + 1);
            },
        });
    };
    return (
        <form onSubmit={submit} className="space-y-3">
            <p className="text-sm text-night/70">{copy.importHint}</p>
            <input
                key={inputKey}
                type="file"
                accept=".csv,text/csv"
                aria-label={copy.importTitle}
                onChange={(e) => form.setData('file', e.target.files?.[0] ?? null)}
                className={fileInput}
            />
            {form.errors.file && (
                <p role="alert" className="text-sm font-semibold">
                    {form.errors.file}
                </p>
            )}
            <Button type="submit" variant="secondary" loading={form.processing} disabled={!form.data.file}>
                {copy.importCta}
            </Button>
        </form>
    );
}

function AddSponsor() {
    const form = useForm<{ name: string; tier: string; url: string; logo: File | null }>({
        name: '',
        tier: '',
        url: '',
        logo: null,
    });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/painel/campanha/patrocinadores', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => form.reset(),
        });
    };
    return (
        <form onSubmit={submit} className="grid gap-3 sm:grid-cols-2">
            <TextField
                label={copy.sponsorName}
                value={form.data.name}
                onChange={(e) => form.setData('name', e.target.value)}
                error={form.errors.name}
            />
            <TextField
                label={copy.sponsorTier}
                value={form.data.tier}
                onChange={(e) => form.setData('tier', e.target.value)}
            />
            <TextField
                type="url"
                label={copy.sponsorUrl}
                value={form.data.url}
                onChange={(e) => form.setData('url', e.target.value)}
                error={form.errors.url}
            />
            <label className="sm:col-span-2">
                <span className="mb-2 block text-sm font-semibold">{copy.sponsorLogo}</span>
                <input
                    type="file"
                    accept="image/*"
                    onChange={(e) => form.setData('logo', e.target.files?.[0] ?? null)}
                    className={fileInput}
                />
                {form.errors.logo && <span className="mt-1 block text-sm font-semibold">{form.errors.logo}</span>}
            </label>
            <div className="sm:col-span-2">
                <Button type="submit" variant="secondary" loading={form.processing}>
                    {copy.addSponsor}
                </Button>
            </div>
        </form>
    );
}

export default function Campaign({
    settings,
    supporters,
    sponsors,
}: {
    settings: Settings;
    supporters: Supporter[];
    sponsors: Sponsor[];
}) {
    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Link href="/painel/conteudo" className="min-h-11 font-semibold underline underline-offset-4">
                ← {common.back}
            </Link>
            <Display as="h1" className="mt-6 text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.title}
            </Display>
            <div className="mt-6">
                <CampaignWarning />
            </div>

            <PanelSection title={copy.title} className="mt-8">
                <SettingsForm settings={settings} />
            </PanelSection>

            <div className="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                <PanelSection title={copy.supportersTitle}>
                    {supporters.length === 0 ? (
                        <p className="text-night/60">{copy.supportersEmpty}</p>
                    ) : (
                        <ul className="divide-y-2 divide-dashed divide-night/12">
                            {supporters.map((s) => (
                                <li key={s.id} className="flex flex-wrap items-center justify-between gap-3 py-3">
                                    <span>
                                        <span className="font-semibold">{s.name}</span>
                                        <span className="ml-2 text-sm text-night/60">
                                            {[
                                                s.amountCents !== null ? money(s.amountCents) : null,
                                                s.reward,
                                                s.supportedAt ? shortDate(s.supportedAt) : null,
                                            ]
                                                .filter(Boolean)
                                                .join(' · ')}
                                        </span>
                                    </span>
                                    <span className="flex items-center gap-2">
                                        <Badge tone={s.publishName ? 'beam' : 'neutral'}>
                                            {s.publishName ? copy.namePublic : copy.namePrivate}
                                        </Badge>
                                        <ConfirmButton
                                            title={common.confirmRemove}
                                            confirmLabel={common.remove}
                                            lead={s.name}
                                            onConfirm={(done) =>
                                                router.delete(`/painel/campanha/apoiadores/${s.id}`, {
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
                    <div className="mt-6 border-t-2 border-dashed border-night/12 pt-6">
                        <AddSupporter />
                    </div>
                </PanelSection>

                <div className="space-y-8">
                    <PanelSection title={copy.importTitle}>
                        <ImportSupporters />
                    </PanelSection>
                    <PanelSection title={copy.sponsorsTitle}>
                        {sponsors.length === 0 ? (
                            <p className="text-night/60">{copy.sponsorsEmpty}</p>
                        ) : (
                            <ul className="divide-y-2 divide-dashed divide-night/12">
                                {sponsors.map((s) => (
                                    <li key={s.id} className="flex items-center justify-between gap-3 py-3">
                                        <span className="flex items-center gap-3">
                                            {s.logo && (
                                                <img
                                                    src={s.logo}
                                                    alt=""
                                                    className="size-10 rounded-lg bg-moonlight object-contain"
                                                />
                                            )}
                                            <span className="font-semibold">{s.name}</span>
                                            {s.tier && <Badge>{s.tier}</Badge>}
                                        </span>
                                        <ConfirmButton
                                            title={common.confirmRemove}
                                            confirmLabel={common.remove}
                                            lead={s.name}
                                            onConfirm={(done) =>
                                                router.delete(`/painel/campanha/patrocinadores/${s.id}`, {
                                                    preserveScroll: true,
                                                    onFinish: done,
                                                })
                                            }
                                        >
                                            {common.remove}
                                        </ConfirmButton>
                                    </li>
                                ))}
                            </ul>
                        )}
                        <div className="mt-6 border-t-2 border-dashed border-night/12 pt-6">
                            <AddSponsor />
                        </div>
                    </PanelSection>
                </div>
            </div>
        </>
    );
}

Campaign.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
