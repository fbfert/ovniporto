import { t } from '@/i18n/pt-BR';
import type { GradeMeaning, SourceKindLabel } from '@/types/origin';

/** Colour helps, words decide: each kind has its tint, and its name is always written. */
const KIND_TONE: Record<SourceKindLabel['kind'], string> = {
    document: 'bg-beam/15 ring-beam/50',
    voice: 'bg-horizon/12 ring-horizon/40',
    report: 'bg-car/25 ring-car/60',
    archive: 'bg-night/6 ring-night/20',
    ordinary: 'bg-moonlight ring-night/25',
    press: 'bg-night/6 ring-night/20',
};

export function SourceBadge({ kind, detail }: { kind: SourceKindLabel; detail?: string }) {
    return (
        <span
            className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[0.72rem] leading-none font-semibold text-night ring-1 ring-inset ${KIND_TONE[kind.kind]}`}
        >
            <span className="sr-only">{t.origin.sourceKind}: </span>
            {kind.label}
            {detail && <span className="font-normal text-night/70">· {detail}</span>}
        </span>
    );
}

/**
 * The confidence stamp of an Atlas case: a round passport-style seal with the grade ("A/B"),
 * read out with what each grade means.
 */
export function ConfidenceSeal({ seal, size = 'md' }: { seal: GradeMeaning[]; size?: 'sm' | 'md' }) {
    const grades = seal.map((g) => g.grade).join('/');
    const description = `${t.origin.confidenceOf(grades)}: ${seal.map((g) => g.meaning).join('; ')}`;
    const dimensions = size === 'sm' ? 'size-14 text-base' : 'size-20 text-xl';

    return (
        <span
            role="img"
            aria-label={description}
            title={description}
            className={`relative inline-flex shrink-0 -rotate-6 flex-col items-center justify-center rounded-full border-2 border-dashed border-horizon font-display font-extrabold tracking-[0.04em] text-horizon ${dimensions}`}
        >
            <span aria-hidden className="text-[0.5rem] font-bold tracking-[0.16em] uppercase">
                {t.origin.confidence}
            </span>
            <span aria-hidden className="leading-none">
                {grades}
            </span>
        </span>
    );
}
