import { useState } from 'react';
import { PinIcon } from '@/Components/Icons';
import { Button } from '@/Components/Ui/Button';
import { ChipGroup, TextField } from '@/Components/Ui/Fields';
import { Badge } from '@/Components/Ui/Typography';
import { longDate, t } from '@/i18n/pt-BR';
import { Compass } from './Compass';
import { GAZE, type DraftUpdate, type LatLng, type ReportDraft } from './draft';
import { PointPicker } from './PointPicker';

const copy = t.report.when;
const GAZE_OPTIONS = GAZE.map((value) => ({ value, label: value }));

/** An offer coming from the photo's EXIF: applied only when the person taps "Usar". */
function Suggestion({ text, onUse, onIgnore }: { text: string; onUse: () => void; onIgnore: () => void }) {
    return (
        <div className="flex flex-wrap items-center gap-3 rounded-2xl bg-beam/12 px-4 py-3 ring-1 ring-beam/40">
            <p className="flex-1 text-[0.95rem]">{text}</p>
            <Button size="sm" onClick={onUse}>
                {copy.useSuggestion}
            </Button>
            <Button size="sm" variant="ghost" tone="dark" onClick={onIgnore}>
                {copy.ignoreSuggestion}
            </Button>
        </div>
    );
}

function FromPhotoBadge() {
    return <Badge tone="beam">{copy.fromPhoto}</Badge>;
}

export function StepWhenWhere({
    draft,
    update,
    errors,
    today,
    lages,
}: {
    draft: ReportDraft;
    update: DraftUpdate;
    errors: Record<string, string>;
    today: string;
    lages: LatLng;
}) {
    const [locating, setLocating] = useState(false);
    const [locationProblem, setLocationProblem] = useState<string | null>(null);
    const { takenAt, point: suggestedPoint } = draft.suggestion;
    const offerDate = takenAt && !draft.dateFromPhoto && takenAt.slice(0, 10) <= today;
    const offerPoint = suggestedPoint && !draft.pointFromPhoto;

    const useDate = () => {
        if (!takenAt) return;
        update({
            observedDate: takenAt.slice(0, 10),
            timeMode: 'exact',
            exactTime: takenAt.slice(11, 16),
            timeRange: null,
            dateFromPhoto: true,
        });
    };
    const ignoreSuggestion = (key: 'takenAt' | 'point') =>
        update((current) => ({ suggestion: { ...current.suggestion, [key]: undefined } }));

    const locate = () => {
        if (!('geolocation' in navigator)) {
            setLocationProblem(copy.locationDenied);
            return;
        }
        setLocating(true);
        setLocationProblem(null);
        navigator.geolocation.getCurrentPosition(
            (position) => {
                setLocating(false);
                update({
                    point: { lat: position.coords.latitude, lng: position.coords.longitude },
                    pointFromPhoto: false,
                });
            },
            () => {
                setLocating(false);
                setLocationProblem(copy.locationDenied);
            },
            { enableHighAccuracy: true, timeout: 10000 },
        );
    };

    return (
        <div className="flex flex-col gap-8">
            {offerDate && (
                <Suggestion
                    text={copy.suggestDate(`${longDate(takenAt.slice(0, 10))}, ${takenAt.slice(11, 16)}`)}
                    onUse={useDate}
                    onIgnore={() => ignoreSuggestion('takenAt')}
                />
            )}

            <div className="grid gap-6 sm:grid-cols-2">
                <div>
                    <TextField
                        tone="dark"
                        type="date"
                        label={copy.dateLabel}
                        value={draft.observedDate}
                        max={today}
                        onChange={(event) => update({ observedDate: event.target.value, dateFromPhoto: false })}
                        error={errors.observedDate}
                    />
                    {draft.dateFromPhoto && (
                        <p className="mt-2 pl-5">
                            <FromPhotoBadge />
                        </p>
                    )}
                </div>
                <ChipGroup
                    tone="dark"
                    label={copy.timeLabel}
                    options={copy.modes}
                    value={draft.timeMode}
                    onChange={(timeMode) =>
                        update({ timeMode: timeMode as ReportDraft['timeMode'], dateFromPhoto: false })
                    }
                />
            </div>

            {draft.timeMode === 'range' ? (
                <ChipGroup
                    tone="dark"
                    label={copy.timeLabel}
                    hideLabel
                    options={copy.ranges}
                    value={draft.timeRange}
                    onChange={(timeRange) => update({ timeRange })}
                    error={errors.time}
                />
            ) : (
                <TextField
                    tone="dark"
                    type="time"
                    label={copy.exactLabel}
                    value={draft.exactTime}
                    onChange={(event) => update({ exactTime: event.target.value, dateFromPhoto: false })}
                    error={errors.time}
                    className="max-w-xs"
                />
            )}

            <div className="flex flex-col gap-3">
                {offerPoint && (
                    <Suggestion
                        text={copy.suggestPoint}
                        onUse={() => update({ point: suggestedPoint, pointFromPhoto: true })}
                        onIgnore={() => ignoreSuggestion('point')}
                    />
                )}
                <p className="flex items-start gap-2 rounded-2xl bg-car/15 px-4 py-3 font-semibold text-car">
                    <PinIcon size="1.15rem" className="mt-0.5" />
                    {copy.mapWarning}
                </p>
                <PointPicker
                    point={draft.point}
                    center={draft.point ?? suggestedPoint ?? lages}
                    onChange={(point) => update({ point, pointFromPhoto: false })}
                    label={copy.mapLabel}
                    className="-mx-5 h-[min(60svh,26rem)] rounded-none sm:mx-0 sm:rounded-[22px]"
                    keyboardHint={copy.mapKeyboardHint}
                />
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <p className="text-sm text-moonlight/65">{copy.mapHint}</p>
                    {draft.pointFromPhoto && <FromPhotoBadge />}
                </div>
                <div>
                    <Button variant="secondary" tone="dark" size="sm" onClick={locate} loading={locating}>
                        {locating ? copy.locating : copy.myLocation}
                    </Button>
                    <p className="mt-2 text-sm text-moonlight/60">{locationProblem ?? copy.locationWhy}</p>
                </div>
                {errors.point && (
                    <p role="alert" className="text-sm font-medium text-car">
                        {errors.point}
                    </p>
                )}
            </div>

            <div className="flex flex-wrap items-center gap-6">
                <ChipGroup
                    tone="dark"
                    label={copy.gazeLabel}
                    options={GAZE_OPTIONS}
                    value={draft.gaze}
                    onChange={(gaze) => update({ gaze: draft.gaze === gaze ? null : gaze })}
                    className="max-w-sm"
                />
                <Compass direction={draft.gaze} />
            </div>
        </div>
    );
}
