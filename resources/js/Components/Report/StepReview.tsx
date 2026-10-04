import { CheckboxField, TextField } from '@/Components/Ui/Fields';
import { longDate, t } from '@/i18n/pt-BR';
import type { ReportDraft, Step } from './draft';

const copy = t.report.review;

function Row({ label, children, onEdit }: { label: string; children: React.ReactNode; onEdit: () => void }) {
    return (
        <div className="flex items-start justify-between gap-4 border-b-2 border-dashed border-moonlight/12 py-4">
            <div>
                <dt className="text-[0.7rem] font-semibold tracking-[0.12em] text-moonlight/55 uppercase">{label}</dt>
                <dd className="mt-1 text-[1.05rem] leading-relaxed">{children}</dd>
            </div>
            <button
                type="button"
                onClick={onEdit}
                className="min-h-11 shrink-0 px-2 text-sm font-semibold text-beam-glow underline underline-offset-4"
            >
                {t.report.review.edit}
            </button>
        </div>
    );
}

export function StepReview({
    draft,
    update,
    goTo,
    errors,
}: {
    draft: ReportDraft;
    update: (patch: Partial<ReportDraft>) => void;
    goTo: (step: Step) => void;
    errors: Record<string, string>;
}) {
    const time =
        draft.timeMode === 'exact'
            ? draft.exactTime
            : (t.report.when.ranges.find((range) => range.value === draft.timeRange)?.label ?? '');

    return (
        <div className="flex flex-col gap-8">
            <dl>
                <Row label={copy.what} onEdit={() => goTo(1)}>
                    <strong className="font-semibold">{t.report.what.types[draft.type ?? ''] ?? ''}</strong>
                    <span className="mt-1 block text-moonlight/80">{draft.description}</span>
                </Row>
                {draft.photos.length > 0 && (
                    <Row label={t.report.photos.title} onEdit={() => goTo(2)}>
                        <span className="mt-1 flex gap-2">
                            {draft.photos.map((photo, i) => (
                                <img
                                    key={photo.id}
                                    src={photo.thumb}
                                    alt={t.report.reviewPhotoAlt(i + 1)}
                                    className="size-16 rounded-xl object-cover"
                                />
                            ))}
                        </span>
                    </Row>
                )}
                <Row label={copy.when} onEdit={() => goTo(3)}>
                    {longDate(draft.observedDate)} · {time}
                </Row>
                <Row label={copy.where} onEdit={() => goTo(3)}>
                    {draft.point ? `${draft.point.lat.toFixed(4)}, ${draft.point.lng.toFixed(4)}` : '—'}
                </Row>
                {draft.gaze && (
                    <Row label={copy.gaze} onEdit={() => goTo(3)}>
                        {draft.gaze}
                    </Row>
                )}
            </dl>

            <TextField
                tone="dark"
                label={copy.nicknameLabel}
                value={draft.nickname}
                maxLength={20}
                autoCapitalize="none"
                onChange={(event) => update({ nickname: event.target.value })}
                error={errors.nickname}
            />
            <p className="-mt-6 px-5 text-sm text-moonlight/60">{copy.nicknameHint}</p>

            <CheckboxField
                tone="dark"
                checked={draft.consent}
                onChange={(event) => update({ consent: event.target.checked })}
                label={copy.consent}
                error={errors.consent}
            />
        </div>
    );
}
