import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { ConfirmButton } from '@/Components/Panel/ConfirmButton';
import { PanelSection } from '@/Components/Panel/PanelSection';
import { PointPicker } from '@/Components/Report/PointPicker';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField, SelectField, TextField } from '@/Components/Ui/Fields';
import { Badge, Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { postJson } from '@/lib/http';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.region;
const common = t.panel.content;

interface Partner {
    id: number;
    name: string;
    slug: string;
    type: string;
    shortDescription: string | null;
    city: string;
    address: string | null;
    lat: number | null;
    lng: number | null;
    phone: string | null;
    whatsapp: string | null;
    instagram: string | null;
    website: string | null;
    isFeatured: boolean;
    isDemo: boolean;
    cover: string | null;
    consentGivenAt: string | null;
    hasConsentProof: boolean;
    publishedAt: string | null;
}

const TYPE_OPTIONS = Object.entries(t.region.types).map(([value, label]) => ({ value, label }));

interface FormData {
    name: string;
    type: string;
    shortDescription: string;
    city: string;
    address: string;
    lat: number | null;
    lng: number | null;
    phone: string;
    whatsapp: string;
    instagram: string;
    website: string;
    isFeatured: boolean;
    consentGivenAt: string;
    cover: File | null;
    consentProof: File | null;
}

const fileInput =
    'block min-h-11 w-full text-sm file:mr-4 file:min-h-11 file:rounded-full file:border-0 file:bg-night file:px-4 file:font-semibold file:text-moonlight';

function Publication({ partner }: { partner: Partner }) {
    const [busy, setBusy] = useState(false);
    const go = (action: 'publicar' | 'despublicar') => {
        setBusy(true);
        router.post(
            `/painel/regiao/${partner.id}/${action}`,
            {},
            { preserveScroll: true, onFinish: () => setBusy(false) },
        );
    };
    const canPublish = partner.consentGivenAt !== null;

    if (partner.publishedAt) {
        return (
            <div className="space-y-3">
                <Badge tone="beam">{copy.published}</Badge>
                <div>
                    <Button variant="secondary" loading={busy} onClick={() => go('despublicar')}>
                        {copy.unpublish}
                    </Button>
                </div>
            </div>
        );
    }
    return (
        <div className="space-y-3">
            <Button
                onClick={() => go('publicar')}
                loading={busy}
                disabled={!canPublish}
                aria-describedby={canPublish ? undefined : 'publish-blocked'}
            >
                {copy.publish}
            </Button>
            {!canPublish && (
                <p id="publish-blocked" className="text-sm font-semibold">
                    {copy.publishBlocked}
                </p>
            )}
        </div>
    );
}

export default function RegionPartner({
    partner,
    origin,
}: {
    partner: Partner | null;
    origin: { lat: number; lng: number };
}) {
    const form = useForm<FormData>({
        name: partner?.name ?? '',
        type: partner?.type ?? 'inn',
        shortDescription: partner?.shortDescription ?? '',
        city: partner?.city ?? 'Lages',
        address: partner?.address ?? '',
        lat: partner?.lat ?? null,
        lng: partner?.lng ?? null,
        phone: partner?.phone ?? '',
        whatsapp: partner?.whatsapp ?? '',
        instagram: partner?.instagram ?? '',
        website: partner?.website ?? '',
        isFeatured: partner?.isFeatured ?? false,
        consentGivenAt: partner?.consentGivenAt ?? '',
        cover: null,
        consentProof: null,
    });
    const [locating, setLocating] = useState(false);
    const [notFound, setNotFound] = useState(false);

    const locate = async () => {
        setLocating(true);
        setNotFound(false);
        try {
            const { point } = await postJson<{ point: { lat: number; lng: number } | null }>(
                '/painel/regiao/localizar',
                {
                    address: [form.data.address, form.data.city].filter(Boolean).join(', '),
                },
            );
            if (point) form.setData((data) => ({ ...data, lat: point.lat, lng: point.lng }));
            else setNotFound(true);
        } catch {
            setNotFound(true);
        } finally {
            setLocating(false);
        }
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(partner ? `/painel/regiao/${partner.id}` : '/painel/regiao', {
            preserveScroll: true,
            forceFormData: true,
        });
    };

    const text = (
        key: 'name' | 'shortDescription' | 'city' | 'address' | 'phone' | 'whatsapp' | 'instagram' | 'website',
        label: string,
        type = 'text',
    ) => (
        <TextField
            type={type}
            label={label}
            value={form.data[key]}
            onChange={(e) => form.setData(key, e.target.value)}
            error={form.errors[key]}
        />
    );

    return (
        <>
            <Head title={`${partner?.name ?? copy.newPartner} · ${t.panel.title}`} />
            <Link href="/painel/regiao" className="min-h-11 font-semibold underline underline-offset-4">
                ← {copy.title}
            </Link>
            <Display as="h1" className="mt-6 text-[clamp(1.6rem,1.1rem+2vw,2.4rem)]!">
                {partner?.name ?? copy.newPartner}
            </Display>

            <form onSubmit={submit} className="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
                <div className="space-y-5">
                    <div className="grid gap-4 sm:grid-cols-2">
                        {text('name', copy.name)}
                        <SelectField
                            label={copy.type}
                            value={form.data.type}
                            onChange={(e) => form.setData('type', e.target.value)}
                            options={TYPE_OPTIONS}
                        />
                    </div>
                    {text('shortDescription', copy.shortDescription)}
                    <div className="grid gap-4 sm:grid-cols-[minmax(0,1fr)_12rem]">
                        {text('address', copy.address)}
                        {text('city', copy.city)}
                    </div>
                    <div className="flex flex-wrap items-center gap-3">
                        <Button
                            variant="secondary"
                            onClick={locate}
                            loading={locating}
                            disabled={!form.data.address && !form.data.city}
                        >
                            {locating ? copy.locating : copy.locate}
                        </Button>
                        {notFound && <p className="text-sm font-semibold">{copy.notFound}</p>}
                    </div>
                    <div>
                        <PointPicker
                            point={
                                form.data.lat !== null && form.data.lng !== null
                                    ? { lat: form.data.lat, lng: form.data.lng }
                                    : null
                            }
                            center={origin}
                            onChange={(point) => form.setData((data) => ({ ...data, lat: point.lat, lng: point.lng }))}
                            label={copy.point}
                            keyboardHint={t.report.when.mapKeyboardHint}
                            className="aspect-[4/3] w-full rounded-[22px]"
                        />
                        <p className="mt-2 text-sm text-night/60">
                            {copy.pointHint} {copy.mapCredit}
                        </p>
                        {form.errors.lat && <p className="text-sm font-semibold">{form.errors.lat}</p>}
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        {text('phone', copy.phone, 'tel')}
                        {text('whatsapp', copy.whatsapp, 'tel')}
                        {text('instagram', copy.instagram)}
                        {text('website', copy.website, 'url')}
                    </div>
                    <CheckboxField
                        label={copy.featured}
                        checked={form.data.isFeatured}
                        onChange={(e) => form.setData('isFeatured', e.target.checked)}
                    />
                </div>

                <div className="space-y-6">
                    <PanelSection title={copy.consentTitle}>
                        <div className="space-y-4">
                            <TextField
                                type="date"
                                label={copy.consentGivenAt}
                                value={form.data.consentGivenAt}
                                onChange={(e) => form.setData('consentGivenAt', e.target.value)}
                                error={form.errors.consentGivenAt}
                            />
                            <label className="block">
                                <span className="mb-2 block text-sm font-semibold">
                                    {copy.consentProof}{' '}
                                    <span className="font-normal text-night/60">{common.optional}</span>
                                </span>
                                <input
                                    type="file"
                                    accept=".pdf,image/*"
                                    onChange={(e) => form.setData('consentProof', e.target.files?.[0] ?? null)}
                                    className={fileInput}
                                />
                            </label>
                            {partner?.hasConsentProof && (
                                <p className="text-sm">
                                    {copy.consentProofSaved} ·{' '}
                                    <a
                                        href={`/painel/regiao/${partner.id}/consentimento`}
                                        className="font-semibold underline underline-offset-4"
                                    >
                                        {copy.consentProofDownload}
                                    </a>
                                </p>
                            )}
                            {partner && <Publication partner={partner} />}
                        </div>
                    </PanelSection>

                    <PanelSection title={copy.cover}>
                        <div className="space-y-3">
                            {partner?.cover && (
                                <img
                                    src={partner.cover}
                                    alt={partner.name}
                                    className="aspect-[16/10] w-full rounded-xl object-cover"
                                />
                            )}
                            <input
                                type="file"
                                accept="image/*"
                                aria-label={copy.cover}
                                onChange={(e) => form.setData('cover', e.target.files?.[0] ?? null)}
                                className={fileInput}
                            />
                            {form.errors.cover && <p className="text-sm font-semibold">{form.errors.cover}</p>}
                        </div>
                    </PanelSection>

                    <div className="flex flex-wrap gap-3">
                        <Button type="submit" loading={form.processing}>
                            {common.save}
                        </Button>
                        {partner && (
                            <ConfirmButton
                                title={common.confirmRemove}
                                confirmLabel={copy.remove}
                                lead={partner.name}
                                onConfirm={(done) => router.delete(`/painel/regiao/${partner.id}`, { onFinish: done })}
                            >
                                {copy.remove}
                            </ConfirmButton>
                        )}
                    </div>
                </div>
            </form>
        </>
    );
}

RegionPartner.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
