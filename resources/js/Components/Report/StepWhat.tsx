import { BeamIcon, StarIcon, UfoIcon } from '@/Components/Icons';
import { ChipGroup, TextareaField } from '@/Components/Ui/Fields';
import { t } from '@/i18n/pt-BR';
import type { DraftUpdate, ReportDraft } from './draft';

const copy = t.report.what;

const TYPES = [
    { value: 'light', label: copy.types.light!, icon: <StarIcon size="1.05rem" /> },
    { value: 'object', label: copy.types.object!, icon: <UfoIcon size="1.05rem" /> },
    { value: 'trail', label: copy.types.trail!, icon: <BeamIcon size="1.05rem" /> },
    { value: 'other', label: copy.types.other! },
];

export function StepWhat({
    draft,
    update,
    errors,
    limits,
}: {
    draft: ReportDraft;
    update: DraftUpdate;
    errors: Record<string, string>;
    limits: { descriptionMin: number; descriptionMax: number };
}) {
    return (
        <div className="flex flex-col gap-8">
            <ChipGroup
                tone="dark"
                label={copy.typeLabel}
                options={TYPES}
                value={draft.type}
                onChange={(type) => update({ type })}
                error={errors.type}
            />
            <TextareaField
                tone="dark"
                label={copy.descriptionLabel}
                placeholder={copy.descriptionPlaceholder}
                value={draft.description}
                onChange={(event) => update({ description: event.target.value })}
                minLength={limits.descriptionMin}
                maxLength={limits.descriptionMax}
                showCount
                rows={6}
                error={errors.description}
                className="text-[1.125rem]"
            />
        </div>
    );
}
