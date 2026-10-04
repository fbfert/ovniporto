import { Head, Link, router, usePage } from '@inertiajs/react';
import { useRef, useState, type ReactNode } from 'react';
import { LazyMap } from '@/Components/Map/LazyMap';
import { DecisionDialogs, type Dialog } from '@/Components/Panel/DecisionDialogs';
import { Compass } from '@/Components/Report/Compass';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField } from '@/Components/Ui/Fields';
import { Badge, Display } from '@/Components/Ui/Typography';
import { useShortcuts } from '@/hooks/useShortcuts';
import { longDate, t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';
import type { SharedProps } from '@/types';

const copy = t.panel.review;

interface Sighting {
    id: number;
    status: 'pending' | 'approved' | 'changes_requested' | 'rejected';
    type: string;
    description: string;
    observedDate: string;
    timeRange: string | null;
    exactTime: string | null;
    lat: number;
    lng: number;
    gaze: string | null;
    nickname: string;
    city: string | null;
    placeLabel: string | null;
    moderationNote: string | null;
    submittedAt: string;
    waitingHours: number;
    publishedAt: string | null;
    consentAt: string;
    photos: { thumb: string | null; full: string | null; processing: boolean }[];
    author: { name: string; email: string; memberSince: string; reports: number } | null;
}

interface HistoryEntry {
    action: string;
    actor: string | null;
    context: Record<string, unknown>;
    at: string;
}

interface Props {
    sighting: Sighting;
    history: HistoryEntry[];
    neighbours: { previous: number | null; next: number | null };
    tab: string;
}

/** Which decisions each status allows (the server checks the same state machine). */
const ALLOWED: Record<Sighting['status'], { approve: boolean; changes: boolean; reject: boolean; unpublish: boolean }> =
    {
        pending: { approve: true, changes: true, reject: true, unpublish: false },
        approved: { approve: false, changes: true, reject: false, unpublish: true },
        changes_requested: { approve: false, changes: false, reject: true, unpublish: false },
        rejected: { approve: false, changes: false, reject: false, unpublish: false },
    };

const STATUS_TONE = { pending: 'neutral', approved: 'beam', changes_requested: 'car', rejected: 'horizon' } as const;

const dateTime = (iso: string) =>
    new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });

function Fact({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="border-b-2 border-dashed border-night/12 py-3">
            <dt className="text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase">{label}</dt>
            <dd className="mt-1">{children}</dd>
        </div>
    );
}

function Photos({ sighting }: { sighting: Sighting }) {
    if (sighting.photos.length === 0) {
        return (
            <p className="rounded-[22px] border-2 border-dashed border-night/20 p-8 text-center font-script text-2xl text-horizon">
                {copy.noPhotos}
            </p>
        );
    }
    return (
        <ul className="grid grid-cols-2 gap-3">
            {sighting.photos.map((photo, i) => (
                <li key={i} className={i === 0 ? 'col-span-2' : ''}>
                    {photo.full ? (
                        <a
                            href={photo.full}
                            target="_blank"
                            rel="noreferrer"
                            className="block overflow-hidden rounded-[18px] bg-night"
                        >
                            <img
                                src={(i === 0 ? photo.full : photo.thumb) ?? ''}
                                alt={copy.photoAlt(i + 1)}
                                className={`w-full object-cover ${i === 0 ? 'aspect-[4/3]' : 'aspect-square'}`}
                            />
                        </a>
                    ) : (
                        <span className="flex aspect-square items-center justify-center rounded-[18px] bg-night/8 text-sm text-night/60">
                            {copy.processing}
                        </span>
                    )}
                </li>
            ))}
        </ul>
    );
}

function ReviewScreen({ sighting, history, neighbours, tab }: Props) {
    const { errors } = usePage<SharedProps>().props;
    const allowed = ALLOWED[sighting.status];
    const [checked, setChecked] = useState<boolean[]>(() => copy.checklist.map(() => false));
    const [dialog, setDialog] = useState<Dialog>(null);
    const [checklistError, setChecklistError] = useState(false);
    const [approving, setApproving] = useState(false);
    const checklistRef = useRef<HTMLFieldSetElement>(null);
    const ready = checked.every(Boolean);
    const type = t.logbook.types[sighting.type as keyof typeof t.logbook.types] ?? sighting.type;
    const time =
        sighting.exactTime ?? t.report.when.ranges.find((range) => range.value === sighting.timeRange)?.label ?? '';

    const approve = () => {
        if (!allowed.approve) return;
        if (!ready) {
            setChecklistError(true);
            checklistRef.current?.querySelector('input')?.focus();
            return;
        }
        setApproving(true);
        router.post(`/painel/relatos/${sighting.id}/aprovar`, {}, { onFinish: () => setApproving(false) });
    };
    const go = (id: number | null) => id !== null && router.visit(`/painel/relatos/${id}`);

    useShortcuts({
        a: approve,
        r: () => allowed.reject && setDialog('reject'),
        j: () => go(neighbours.next),
        k: () => go(neighbours.previous),
    });

    return (
        <>
            <Head title={`${type} #${sighting.id} · ${t.panel.title}`} />
            <div className="flex flex-wrap items-center justify-between gap-3">
                <Link
                    href={`/painel/relatos?aba=${tab}`}
                    className="min-h-11 font-semibold underline underline-offset-4"
                >
                    ← {copy.back}
                </Link>
                <div className="flex gap-2">
                    <Button
                        variant="secondary"
                        size="sm"
                        disabled={neighbours.previous === null}
                        onClick={() => go(neighbours.previous)}
                    >
                        {copy.previous}
                    </Button>
                    <Button
                        variant="secondary"
                        size="sm"
                        disabled={neighbours.next === null}
                        onClick={() => go(neighbours.next)}
                    >
                        {copy.next}
                    </Button>
                </div>
            </div>

            <div className="mt-6 flex flex-wrap items-center gap-3">
                <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                    {type}
                </Display>
                <Badge tone={STATUS_TONE[sighting.status]}>{t.members.sightingStatus[sighting.status]}</Badge>
                <span className="text-sm text-night/60">
                    #{sighting.id} · {copy.submitted} {t.panel.since(sighting.waitingHours)}
                </span>
            </div>

            <div className="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
                <div className="space-y-6">
                    <Photos sighting={sighting} />
                    <LazyMap
                        markers={[{ id: String(sighting.id), lat: sighting.lat, lng: sighting.lng, label: type }]}
                        label={copy.mapLabel}
                        zoom={14}
                        className="aspect-[4/3] w-full"
                    />
                    <p className="text-sm text-night/60 tabular-nums">
                        {copy.point}: {sighting.lat.toFixed(5)}, {sighting.lng.toFixed(5)}
                    </p>
                </div>

                <div className="space-y-8">
                    <p className="prose-ovni text-[1.1rem] leading-relaxed whitespace-pre-line">
                        {sighting.description}
                    </p>

                    <dl>
                        <Fact label={copy.nickname}>@{sighting.nickname}</Fact>
                        <Fact label={copy.observed}>
                            {longDate(sighting.observedDate)}
                            {time && ` · ${time}`}
                        </Fact>
                        {sighting.gaze && (
                            <Fact label={copy.gaze}>
                                <span className="flex items-center gap-3">
                                    <Compass direction={sighting.gaze} />
                                    {sighting.gaze}
                                </span>
                            </Fact>
                        )}
                        <Fact label={copy.city}>
                            {sighting.city ?? sighting.placeLabel ?? t.panel.queue.cityUnknown}
                            {sighting.city && <span className="ml-2 text-xs text-night/50">{copy.cityCredit}</span>}
                        </Fact>
                        <Fact label={copy.consent}>{dateTime(sighting.consentAt)}</Fact>
                    </dl>

                    <section
                        data-tone="dark"
                        className="rounded-[22px] bg-night-blue p-5 text-moonlight"
                        aria-label={copy.author}
                    >
                        <p className="text-[0.7rem] font-semibold tracking-[0.12em] text-beam-glow uppercase">
                            {copy.author}
                        </p>
                        {sighting.author ? (
                            <>
                                <p className="mt-2 text-lg font-semibold">{sighting.author.name}</p>
                                <p className="break-all text-moonlight/80">{sighting.author.email}</p>
                                <p className="mt-2 text-sm text-moonlight/65">
                                    {copy.memberSince} {longDate(sighting.author.memberSince)} ·{' '}
                                    {copy.reports(sighting.author.reports)}
                                </p>
                            </>
                        ) : (
                            <p className="mt-2 text-moonlight/70">{copy.authorGone}</p>
                        )}
                    </section>

                    {sighting.moderationNote && (
                        <p className="rounded-2xl bg-car/20 px-4 py-3">
                            <strong className="font-semibold">{copy.noteToAuthor}:</strong> {sighting.moderationNote}
                        </p>
                    )}

                    {(allowed.approve || allowed.changes || allowed.reject || allowed.unpublish) && (
                        <section className="rounded-[22px] bg-night/5 p-5 ring-1 ring-night/10">
                            {allowed.approve && (
                                <fieldset ref={checklistRef}>
                                    <legend className="font-display text-sm font-bold tracking-[0.06em] uppercase">
                                        {copy.checklistTitle}
                                    </legend>
                                    <div className="mt-2">
                                        {copy.checklist.map((item, i) => (
                                            <CheckboxField
                                                key={item}
                                                label={item}
                                                checked={checked[i] ?? false}
                                                onChange={(e) => {
                                                    setChecklistError(false);
                                                    setChecked((current) =>
                                                        current.map((v, j) => (j === i ? e.target.checked : v)),
                                                    );
                                                }}
                                            />
                                        ))}
                                    </div>
                                    {checklistError && (
                                        <p role="alert" className="mt-2 text-sm font-semibold">
                                            {copy.checklistMissing}
                                        </p>
                                    )}
                                </fieldset>
                            )}
                            {errors.status && (
                                <p role="alert" className="mt-3 rounded-xl bg-car px-4 py-2 font-semibold">
                                    {errors.status}
                                </p>
                            )}
                            <div className="mt-5 flex flex-wrap gap-3">
                                {allowed.approve && (
                                    <Button onClick={approve} loading={approving} aria-disabled={!ready || undefined}>
                                        {copy.approve}
                                    </Button>
                                )}
                                {allowed.changes && (
                                    <Button variant="secondary" onClick={() => setDialog('changes')}>
                                        {copy.requestChanges}
                                    </Button>
                                )}
                                {allowed.reject && (
                                    <Button variant="secondary" onClick={() => setDialog('reject')}>
                                        {copy.reject}
                                    </Button>
                                )}
                                {allowed.unpublish && (
                                    <Button variant="car" onClick={() => setDialog('unpublish')}>
                                        {copy.unpublish}
                                    </Button>
                                )}
                            </div>
                            <p className="mt-4 hidden text-sm text-night/60 sm:block">{copy.shortcuts}</p>
                        </section>
                    )}

                    <section aria-labelledby="historico">
                        <h2 id="historico" className="font-display text-sm font-bold tracking-[0.06em] uppercase">
                            {copy.history}
                        </h2>
                        {history.length === 0 ? (
                            <p className="mt-2 text-night/60">{copy.historyEmpty}</p>
                        ) : (
                            <ol className="mt-3 space-y-3 border-l-2 border-dashed border-horizon/40 pl-4">
                                {history.map((entry, i) => (
                                    <li key={i}>
                                        <p className="text-sm">
                                            <strong className="font-semibold">
                                                {entry.actor ?? t.panel.audit.system}
                                            </strong>{' '}
                                            {t.panel.actions[entry.action] ?? entry.action}
                                            <span className="text-night/60"> · {dateTime(entry.at)}</span>
                                        </p>
                                        {typeof entry.context.note === 'string' && (
                                            <p className="mt-1 text-sm text-night/75">{entry.context.note}</p>
                                        )}
                                        {typeof entry.context.noteToAuthor === 'string' && (
                                            <p className="mt-1 text-sm text-night/75">“{entry.context.noteToAuthor}”</p>
                                        )}
                                    </li>
                                ))}
                            </ol>
                        )}
                    </section>
                </div>
            </div>

            <DecisionDialogs sightingId={sighting.id} open={dialog} onClose={() => setDialog(null)} />
        </>
    );
}

/** Keyed by report: going to the next one with J starts with a fresh checklist. */
export default function Review(props: Props) {
    return <ReviewScreen key={props.sighting.id} {...props} />;
}

Review.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
