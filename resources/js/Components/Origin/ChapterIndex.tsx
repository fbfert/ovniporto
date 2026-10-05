import { useState } from 'react';
import { Modal } from '@/Components/Ui/Modal';
import { t } from '@/i18n/pt-BR';

export interface ChapterLink {
    id: string;
    title: string;
}

function Links({ chapters, onPick }: { chapters: ChapterLink[]; onPick?: () => void }) {
    return (
        <ol className="border-l-2 border-dashed border-night/15">
            {chapters.map((chapter, i) => (
                <li key={chapter.id}>
                    <a
                        href={`#${chapter.id}`}
                        onClick={onPick}
                        className="-ml-0.5 flex min-h-11 items-center gap-3 border-l-2 border-transparent py-1 pl-4 text-[0.95rem] text-night/75 transition-colors duration-150 hover:border-horizon hover:text-night"
                    >
                        <span aria-hidden className="w-5 font-display text-[0.7rem] font-bold text-horizon/80">
                            {String(i + 1).padStart(2, '0')}
                        </span>
                        {chapter.title}
                    </a>
                </li>
            ))}
        </ol>
    );
}

/**
 * The chapters of a long page: a sticky list beside the text on desktop, and on phones a floating
 * "Capítulos" pill that opens the same list in a dialog (focus trapped, Esc closes).
 */
export function ChapterIndex({ chapters }: { chapters: ChapterLink[] }) {
    const [open, setOpen] = useState(false);

    return (
        <>
            <nav aria-label={t.origin.chapters} className="hidden self-start lg:sticky lg:top-28 lg:block">
                <p className="mb-4 font-script text-xl text-horizon">{t.origin.chapters}</p>
                <Links chapters={chapters} />
            </nav>

            <button
                type="button"
                onClick={() => setOpen(true)}
                aria-haspopup="dialog"
                className="fixed bottom-5 left-1/2 z-30 inline-flex min-h-11 -translate-x-1/2 items-center gap-2 rounded-full bg-night px-5 font-semibold text-moonlight shadow-[0_12px_30px_-10px_rgb(6_17_33/0.7)] ring-1 ring-moonlight/15 lg:hidden"
            >
                <svg
                    aria-hidden
                    viewBox="0 0 24 24"
                    className="size-4"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth={2}
                >
                    <path d="M5 7h14M5 12h14M5 17h9" strokeLinecap="round" />
                </svg>
                {t.origin.chapters}
            </button>
            <Modal open={open} onClose={() => setOpen(false)} title={t.origin.chapters} tone="light">
                <nav aria-label={t.origin.chapters}>
                    <Links chapters={chapters} onPick={() => setOpen(false)} />
                </nav>
            </Modal>
        </>
    );
}
