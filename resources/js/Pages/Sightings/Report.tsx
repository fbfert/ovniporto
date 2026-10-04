import { Link, router } from '@inertiajs/react';
import { AnimatePresence, motion } from 'motion/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Seal } from '@/Components/Brand/Seal';
import { CloseIcon } from '@/Components/Icons';
import { SeoHead } from '@/Components/Layout/SeoHead';
import {
    draftFromSighting,
    emptyDraft,
    isKept,
    keptId,
    withFreshThumbs,
    type DraftUpdate,
    type EditingSighting,
    type ReportDraft,
    type Step,
} from '@/Components/Report/draft';
import { StepPhotos } from '@/Components/Report/StepPhotos';
import { StepReview } from '@/Components/Report/StepReview';
import { StepWhat } from '@/Components/Report/StepWhat';
import { StepWhenWhere } from '@/Components/Report/StepWhenWhere';
import { Starfield } from '@/Components/Scene/Starfield';
import { Button } from '@/Components/Ui/Button';
import { useDraft } from '@/hooks/useDraft';
import { usePrefersReducedMotion } from '@/hooks/usePrefersReducedMotion';
import { t } from '@/i18n/pt-BR';
import { ease } from '@/lib/motion';

const copy = t.report;
const DRAFT_KEY = 'ovniporto:relato';
const TITLES: Record<Step, string> = {
    1: copy.what.title,
    2: copy.photos.title,
    3: copy.when.title,
    4: copy.review.title,
};

/** Server field → the step that owns it, so an error sends the person back to the right screen. */
const FIELD_STEP: Record<string, Step> = {
    type: 1,
    description: 1,
    photos: 2,
    observedDate: 3,
    timeRange: 3,
    exactTime: 3,
    time: 3,
    lat: 3,
    lng: 3,
    point: 3,
    gaze: 3,
    nickname: 4,
    consent: 4,
    keptPhotos: 2,
    status: 4,
};

interface Props {
    nickname: string;
    today: string;
    lages: { lat: number; lng: number };
    limits: { descriptionMin: number; descriptionMax: number; maxPhotos: number; maxPhotoMb: number };
    /** Present when the author fixes a report the tower sent back. */
    editing?: EditingSighting;
}

function validate(step: Step, draft: ReportDraft, limits: Props['limits']): Record<string, string> {
    const errors: Record<string, string> = {};
    if (step === 1) {
        if (!draft.type) errors.type = 'Escolha o que você viu.';
        const length = draft.description.trim().length;
        if (length < limits.descriptionMin) errors.description = 'Conte um pouco mais: pelo menos 20 caracteres.';
        if (length > limits.descriptionMax) errors.description = 'No máximo 1000 caracteres.';
    }
    if (step === 3) {
        if (!draft.point) errors.point = copy.when.pointMissing;
        if (draft.timeMode === 'range' && !draft.timeRange) errors.time = 'Escolha a faixa de horário.';
        if (draft.timeMode === 'exact' && !draft.exactTime) errors.time = 'Diga a hora.';
    }
    if (step === 4) {
        if (!/^[a-z0-9_.]{3,20}$/i.test(draft.nickname.trim()))
            errors.nickname = 'Use de 3 a 20 letras, números, "_" ou ".".';
        if (!draft.consent) errors.consent = 'Marque a autorização para publicar o relato.';
    }
    return errors;
}

export default function Report({ nickname, today, lages, limits, editing }: Props) {
    const {
        state: stored,
        setState,
        wasRestored,
        clear,
    } = useDraft<ReportDraft>(
        editing ? `${DRAFT_KEY}:${editing.id}` : DRAFT_KEY,
        editing ? draftFromSighting(editing) : emptyDraft(today, nickname),
    );
    const draft = editing ? withFreshThumbs(stored, editing) : stored;
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [direction, setDirection] = useState(1);
    const [sending, setSending] = useState(false);
    const reduced = usePrefersReducedMotion();
    const headingRef = useRef<HTMLHeadingElement>(null);

    const update: DraftUpdate = useCallback(
        (patch) => setState((current) => ({ ...current, ...(typeof patch === 'function' ? patch(current) : patch) })),
        [setState],
    );

    const goTo = (step: Step) => {
        setDirection(step > draft.step ? 1 : -1);
        setErrors({});
        update({ step });
    };

    // Moving between steps puts focus on the new title (screen readers hear where they are).
    useEffect(() => {
        headingRef.current?.focus({ preventScroll: true });
        window.scrollTo({ top: 0 });
    }, [draft.step]);

    const next = () => {
        const found = validate(draft.step, draft, limits);
        if (Object.keys(found).length > 0) {
            setErrors(found);
            return;
        }
        if (draft.step < 4) {
            goTo((draft.step + 1) as Step);
            return;
        }
        send();
    };

    const send = () => {
        setSending(true);
        router.visit(editing ? `/relatar/${editing.id}` : '/relatar', {
            method: editing ? 'put' : 'post',
            data: {
                type: draft.type,
                description: draft.description.trim(),
                observedDate: draft.observedDate,
                timeRange: draft.timeMode === 'range' ? draft.timeRange : null,
                exactTime: draft.timeMode === 'exact' ? draft.exactTime : null,
                lat: draft.point?.lat ?? null,
                lng: draft.point?.lng ?? null,
                gaze: draft.gaze,
                nickname: draft.nickname.trim(),
                consent: draft.consent,
                photos: draft.photos.filter((photo) => !isKept(photo.id)).map((photo) => photo.id),
                keptPhotos: draft.photos.filter((photo) => isKept(photo.id)).map((photo) => keptId(photo.id)),
            },
            onSuccess: () => clear(),
            onError: (serverErrors) => {
                const fields = Object.keys(serverErrors);
                const step = Math.min(...fields.map((field) => FIELD_STEP[field] ?? 4)) as Step;
                setErrors(serverErrors);
                if (step !== draft.step) {
                    setDirection(-1);
                    update({ step });
                }
            },
            onFinish: () => setSending(false),
        });
    };

    const restart = () => {
        clear();
        window.location.reload();
    };

    const slide = reduced
        ? { initial: { opacity: 0 }, animate: { opacity: 1 }, exit: { opacity: 0 } }
        : {
              initial: { opacity: 0, transform: `translateX(${direction * 32}px)` },
              animate: { opacity: 1, transform: 'translateX(0px)' },
              exit: { opacity: 0, transform: `translateX(${direction * -32}px)` },
          };

    return (
        <div data-tone="dark" className="relative min-h-svh bg-night text-moonlight">
            <SeoHead />
            <Starfield className="fixed inset-0 opacity-60" density="low" />

            <header className="sticky top-0 z-20 bg-night/90 px-5 pt-4 pb-3 backdrop-blur-[10px]">
                <div className="mx-auto flex max-w-2xl items-center justify-between gap-4">
                    <Link href="/" aria-label={t.nav.home}>
                        <Seal size="sm" />
                    </Link>
                    <p className="text-sm font-semibold text-moonlight/70">{copy.progress(draft.step)}</p>
                    <Link
                        href="/"
                        aria-label={t.form.close}
                        className="inline-flex size-11 press items-center justify-center rounded-full ring-1 ring-moonlight/20"
                    >
                        <CloseIcon />
                    </Link>
                </div>
                <ol className="mx-auto mt-3 grid max-w-2xl grid-cols-4 gap-1.5" aria-hidden>
                    {([1, 2, 3, 4] as Step[]).map((step) => (
                        <li
                            key={step}
                            className={`h-1.5 rounded-full transition-colors duration-300 ${step <= draft.step ? 'bg-beam' : 'bg-moonlight/15'}`}
                        />
                    ))}
                </ol>
            </header>

            <main className="relative z-10 mx-auto max-w-2xl px-5 pt-6 pb-36">
                {editing && draft.step === 1 && (
                    <aside className="mb-6 rounded-[22px] bg-car px-5 py-4 text-night">
                        <p className="font-display text-sm font-extrabold tracking-[0.06em] uppercase">
                            {copy.editingNote}
                        </p>
                        {editing.moderationNote && (
                            <p className="mt-1 text-[1.05rem] font-semibold">{editing.moderationNote}</p>
                        )}
                        <p className="mt-2 text-sm">{copy.editingLead}</p>
                    </aside>
                )}
                {errors.status && (
                    <p role="alert" className="mb-6 rounded-2xl bg-car px-4 py-3 font-semibold text-night">
                        {errors.status}
                    </p>
                )}
                {wasRestored && draft.step === 1 && (
                    <p className="mb-6 flex flex-wrap items-center gap-3 rounded-2xl bg-night-blue/80 px-4 py-3 text-sm">
                        {copy.draftRestored}
                        <button
                            type="button"
                            onClick={restart}
                            className="min-h-11 font-semibold text-beam-glow underline underline-offset-4"
                        >
                            {copy.discardDraft}
                        </button>
                    </p>
                )}
                <AnimatePresence mode="wait" initial={false}>
                    <motion.section
                        key={draft.step}
                        {...slide}
                        transition={{ duration: 0.22, ease: ease.snap }}
                        aria-labelledby="passo-titulo"
                    >
                        <h1
                            id="passo-titulo"
                            ref={headingRef}
                            tabIndex={-1}
                            className="font-display text-[clamp(1.6rem,1.1rem+2.4vw,2.4rem)] leading-tight font-extrabold tracking-[0.03em] uppercase outline-none"
                        >
                            {TITLES[draft.step]}
                            {draft.step === 2 && (
                                <span className="ml-3 align-middle font-script text-2xl font-normal tracking-normal text-beam-glow normal-case">
                                    {copy.photos.optional}
                                </span>
                            )}
                        </h1>
                        <div className="mt-8 text-[1.125rem]">
                            {draft.step === 1 && (
                                <StepWhat draft={draft} update={update} errors={errors} limits={limits} />
                            )}
                            {draft.step === 2 && (
                                <StepPhotos
                                    draft={draft}
                                    update={update}
                                    maxPhotos={limits.maxPhotos}
                                    error={errors.photos}
                                />
                            )}
                            {draft.step === 3 && (
                                <StepWhenWhere
                                    draft={draft}
                                    update={update}
                                    errors={errors}
                                    today={today}
                                    lages={lages}
                                />
                            )}
                            {draft.step === 4 && (
                                <StepReview draft={draft} update={update} goTo={goTo} errors={errors} />
                            )}
                        </div>
                    </motion.section>
                </AnimatePresence>
            </main>

            <footer className="fixed inset-x-0 bottom-0 z-20 border-t border-moonlight/10 bg-night/95 px-5 pt-3 pb-[max(1rem,env(safe-area-inset-bottom))] backdrop-blur-[10px]">
                <div className="mx-auto flex max-w-2xl items-center gap-3">
                    {draft.step > 1 && (
                        <Button
                            variant="secondary"
                            tone="dark"
                            size="lg"
                            onClick={() => goTo((draft.step - 1) as Step)}
                        >
                            {copy.back}
                        </Button>
                    )}
                    {draft.step === 2 && draft.photos.length === 0 && (
                        <Button variant="ghost" tone="dark" size="lg" onClick={() => goTo(3)}>
                            {copy.skip}
                        </Button>
                    )}
                    <Button size="lg" className="ml-auto h-14 flex-1 sm:flex-none" onClick={next} loading={sending}>
                        {draft.step === 4 ? (editing ? copy.resend : copy.send) : copy.next}
                    </Button>
                </div>
            </footer>
        </div>
    );
}
