import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { ConfirmButton } from '@/Components/Panel/ConfirmButton';
import { MoveButtons, PanelSection } from '@/Components/Panel/PanelSection';
import { Button } from '@/Components/Ui/Button';
import { hasConcept, Picture } from '@/Components/Ui/Picture';
import { SelectField, TextareaField, TextField } from '@/Components/Ui/Fields';
import { Badge, Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.place;
const common = t.panel.content;

interface Space {
    id: number;
    slug: string;
    name: string;
    role: string;
    description: string | null;
    phase: number;
    status: string;
    concept: string | null;
    conceptUrl: string | null;
}

interface Photo {
    id: number;
    url: string;
    alt: string;
    caption: string | null;
    takenAt: string | null;
}

interface Props {
    spaces: Space[];
    photos: Photo[];
    map3d: string | null;
    embedHosts: string[];
}

function ConceptThumb({
    concept,
    conceptUrl,
    name,
}: {
    concept: string | null;
    conceptUrl: string | null;
    name: string;
}) {
    if (hasConcept(concept)) {
        return (
            <Picture
                slug={concept}
                alt={name}
                sizes="10rem"
                className="size-full"
                imgClassName="size-full object-cover"
            />
        );
    }
    if (conceptUrl) {
        return <img src={conceptUrl} alt={name} className="size-full object-cover" />;
    }
    return (
        <span className="flex size-full items-center justify-center text-xs text-moonlight/60">
            {copy.conceptPending}
        </span>
    );
}

function SpaceRow({ space, first, last }: { space: Space; first: boolean; last: boolean }) {
    const [open, setOpen] = useState(false);
    const form = useForm({
        name: space.name,
        role: space.role,
        description: space.description ?? '',
        phase: String(space.phase),
        status: space.status,
    });

    const save = (event: FormEvent) => {
        event.preventDefault();
        form.put(`/painel/lugar/espacos/${space.id}`, { preserveScroll: true, onSuccess: () => setOpen(false) });
    };
    const upload = (file: File | null) => {
        if (!file) return;
        router.post(
            `/painel/lugar/espacos/${space.id}/conceito`,
            { image: file },
            { preserveScroll: true, forceFormData: true },
        );
    };

    return (
        <li className="py-4">
            <div className="grid grid-cols-[5rem_minmax(0,1fr)] gap-4 sm:grid-cols-[6rem_minmax(0,1fr)_auto] sm:items-center">
                <span className="relative block aspect-[4/3] overflow-hidden rounded-xl bg-night">
                    <ConceptThumb concept={space.concept} conceptUrl={space.conceptUrl} name={space.name} />
                </span>
                <span className="min-w-0">
                    <span className="flex flex-wrap items-center gap-2">
                        <span className="font-semibold">{space.name}</span>
                        <Badge tone="horizon">{`${t.placePage.phaseTitle(space.phase)}`}</Badge>
                        <Badge>{copy.statuses.find((s) => s.value === space.status)?.label ?? space.status}</Badge>
                    </span>
                    <span className="mt-0.5 block text-sm text-night/65">{space.role}</span>
                </span>
                <span className="col-span-2 flex flex-wrap gap-2 sm:col-span-1">
                    <MoveButtons
                        onMove={(direction) =>
                            router.post(
                                `/painel/lugar/espacos/${space.id}/mover`,
                                { direction },
                                { preserveScroll: true },
                            )
                        }
                        first={first}
                        last={last}
                        labels={{ up: common.moveUp, down: common.moveDown }}
                    />
                    <Button variant="secondary" size="sm" onClick={() => setOpen(!open)} aria-expanded={open}>
                        {common.edit}
                    </Button>
                </span>
            </div>
            {open && (
                <form
                    onSubmit={save}
                    className="mt-4 grid gap-4 rounded-[18px] bg-moonlight p-4 ring-1 ring-night/10 sm:grid-cols-2"
                >
                    <TextField
                        label={copy.name}
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        error={form.errors.name}
                    />
                    <TextField
                        label={copy.role}
                        value={form.data.role}
                        onChange={(e) => form.setData('role', e.target.value)}
                        error={form.errors.role}
                    />
                    <TextareaField
                        label={copy.description}
                        value={form.data.description}
                        onChange={(e) => form.setData('description', e.target.value)}
                        error={form.errors.description}
                        rows={3}
                        className="sm:col-span-2"
                    />
                    <SelectField
                        label={copy.phase}
                        value={form.data.phase}
                        onChange={(e) => form.setData('phase', e.target.value)}
                        options={[1, 2, 3, 4, 5].map((n) => ({ value: String(n), label: t.placePage.phaseTitle(n) }))}
                    />
                    <SelectField
                        label={copy.status}
                        value={form.data.status}
                        onChange={(e) => form.setData('status', e.target.value)}
                        options={copy.statuses}
                        error={form.errors.status}
                    />
                    <label className="sm:col-span-2">
                        <span className="mb-2 block text-sm font-semibold">{copy.concept}</span>
                        <input
                            type="file"
                            accept="image/*"
                            onChange={(e) => upload(e.target.files?.[0] ?? null)}
                            className="block min-h-11 w-full text-sm file:mr-4 file:min-h-11 file:rounded-full file:border-0 file:bg-night file:px-4 file:font-semibold file:text-moonlight"
                        />
                        <span className="mt-1 block text-xs text-night/60">{copy.conceptHint}</span>
                    </label>
                    <div className="sm:col-span-2">
                        <Button type="submit" loading={form.processing}>
                            {common.save}
                        </Button>
                    </div>
                </form>
            )}
        </li>
    );
}

function PhotoRow({ photo, first, last }: { photo: Photo; first: boolean; last: boolean }) {
    const form = useForm({ alt: photo.alt, caption: photo.caption ?? '', takenAt: photo.takenAt ?? '' });
    const save = (event: FormEvent) => {
        event.preventDefault();
        form.put(`/painel/lugar/fotos/${photo.id}`, { preserveScroll: true });
    };
    return (
        <li className="grid gap-4 py-4 sm:grid-cols-[10rem_minmax(0,1fr)]">
            <img src={photo.url} alt={photo.alt} className="aspect-[4/3] w-full rounded-xl object-cover" />
            <form onSubmit={save} className="grid gap-3">
                <TextField
                    label={copy.photoAlt}
                    value={form.data.alt}
                    onChange={(e) => form.setData('alt', e.target.value)}
                    error={form.errors.alt}
                />
                <div className="grid gap-3 sm:grid-cols-2">
                    <TextField
                        label={copy.photoCaption}
                        value={form.data.caption}
                        onChange={(e) => form.setData('caption', e.target.value)}
                    />
                    <TextField
                        type="date"
                        label={copy.photoDate}
                        value={form.data.takenAt}
                        onChange={(e) => form.setData('takenAt', e.target.value)}
                        error={form.errors.takenAt}
                    />
                </div>
                <div className="flex flex-wrap gap-2">
                    <Button type="submit" size="sm" loading={form.processing}>
                        {common.save}
                    </Button>
                    <MoveButtons
                        onMove={(direction) =>
                            router.post(
                                `/painel/lugar/fotos/${photo.id}/mover`,
                                { direction },
                                { preserveScroll: true },
                            )
                        }
                        first={first}
                        last={last}
                        labels={{ up: common.moveUp, down: common.moveDown }}
                    />
                    <ConfirmButton
                        title={common.confirmRemove}
                        confirmLabel={common.remove}
                        lead={photo.alt}
                        onConfirm={(done) =>
                            router.delete(`/painel/lugar/fotos/${photo.id}`, { preserveScroll: true, onFinish: done })
                        }
                    >
                        {common.remove}
                    </ConfirmButton>
                </div>
            </form>
        </li>
    );
}

function AddPhoto() {
    const form = useForm<{ image: File | null; alt: string; caption: string; takenAt: string }>({
        image: null,
        alt: '',
        caption: '',
        takenAt: '',
    });
    const [inputKey, setInputKey] = useState(0);
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/painel/lugar/fotos', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                setInputKey((k) => k + 1);
            },
        });
    };
    return (
        <form
            onSubmit={submit}
            className="mt-6 grid gap-3 border-t-2 border-dashed border-night/12 pt-6 sm:grid-cols-2"
        >
            <label className="sm:col-span-2">
                <span className="mb-2 block text-sm font-semibold">{copy.photoFile}</span>
                <input
                    key={inputKey}
                    type="file"
                    accept="image/*"
                    onChange={(e) => form.setData('image', e.target.files?.[0] ?? null)}
                    className="block min-h-11 w-full text-sm file:mr-4 file:min-h-11 file:rounded-full file:border-0 file:bg-night file:px-4 file:font-semibold file:text-moonlight"
                />
                {form.errors.image && <span className="mt-1 block text-sm font-semibold">{form.errors.image}</span>}
            </label>
            <TextField
                label={copy.photoAlt}
                value={form.data.alt}
                onChange={(e) => form.setData('alt', e.target.value)}
                error={form.errors.alt}
                className="sm:col-span-2"
            />
            <TextField
                label={copy.photoCaption}
                value={form.data.caption}
                onChange={(e) => form.setData('caption', e.target.value)}
            />
            <TextField
                type="date"
                label={copy.photoDate}
                value={form.data.takenAt}
                onChange={(e) => form.setData('takenAt', e.target.value)}
                error={form.errors.takenAt}
            />
            <p className="text-sm text-night/60 sm:col-span-2">{copy.photoHint}</p>
            <div>
                <Button type="submit" loading={form.processing} disabled={!form.data.image}>
                    {copy.photoAdd}
                </Button>
            </div>
        </form>
    );
}

function Map3dForm({ map3d, hosts }: { map3d: string | null; hosts: string[] }) {
    const form = useForm({ embed: map3d ?? '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put('/painel/lugar/mapa-3d', { preserveScroll: true });
    };
    return (
        <form onSubmit={submit} className="space-y-3">
            <TextareaField
                label={copy.map3dLabel}
                value={form.data.embed}
                onChange={(e) => form.setData('embed', e.target.value)}
                error={form.errors.embed}
                rows={3}
            />
            <p className="text-sm text-night/60">{copy.map3dHosts(hosts.join(', '))}</p>
            {!map3d && <p className="text-sm text-night/60">{copy.map3dEmpty}</p>}
            <Button type="submit" loading={form.processing}>
                {common.save}
            </Button>
        </form>
    );
}

export default function Place({ spaces, photos, map3d, embedHosts }: Props) {
    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Link href="/painel/conteudo" className="min-h-11 font-semibold underline underline-offset-4">
                ← {common.back}
            </Link>
            <Display as="h1" className="mt-6 text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.title}
            </Display>
            <p className="mt-4 max-w-[60ch] rounded-2xl bg-car/25 px-4 py-3 font-semibold">{copy.notice}</p>

            <PanelSection title={copy.spacesTitle} className="mt-8">
                <ol className="divide-y-2 divide-dashed divide-night/12">
                    {spaces.map((space) => {
                        const samePhase = spaces.filter((s) => s.phase === space.phase);
                        return (
                            <SpaceRow
                                key={space.id}
                                space={space}
                                first={samePhase[0]?.id === space.id}
                                last={samePhase[samePhase.length - 1]?.id === space.id}
                            />
                        );
                    })}
                </ol>
            </PanelSection>

            <PanelSection title={copy.photosTitle} className="mt-8">
                {photos.length === 0 ? (
                    <p className="text-night/60">{copy.photosEmpty}</p>
                ) : (
                    <ol className="divide-y-2 divide-dashed divide-night/12">
                        {photos.map((photo, i) => (
                            <PhotoRow key={photo.id} photo={photo} first={i === 0} last={i === photos.length - 1} />
                        ))}
                    </ol>
                )}
                <AddPhoto />
            </PanelSection>

            <PanelSection title={copy.map3dTitle} className="mt-8">
                <Map3dForm map3d={map3d} hosts={embedHosts} />
            </PanelSection>
        </>
    );
}

Place.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
