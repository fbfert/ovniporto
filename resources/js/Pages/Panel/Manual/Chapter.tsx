import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { MindMap, MindMapLegend, type MapNode } from '@/Components/Panel/MindMap';
import { reviewedOn } from '@/Components/Panel/reviewedOn';
import { Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.manual;

interface Section {
    id: string;
    title: string;
    html: string;
}

interface Neighbour {
    slug: string;
    title: string;
}

interface Props {
    chapter: { slug: string; title: string; summary: string; reviewedAt: string; map: MapNode; sections: Section[] };
    previous: Neighbour | null;
    next: Neighbour | null;
}

/** Bring a section into view and put the reader there, so the next Tab continues from it. */
function openSection(id: string) {
    const heading = document.getElementById(`secao-${id}`);
    if (!heading) return;
    heading.scrollIntoView({ block: 'start' });
    heading.focus({ preventScroll: true });
    history.replaceState(history.state, '', `#${id}`);
}

export default function Chapter({ chapter, previous, next }: Props) {
    return (
        <>
            <Head title={`${chapter.title} · ${copy.title}`} />
            <Link
                href="/painel/manual"
                className="inline-flex min-h-11 items-center text-sm font-semibold underline underline-offset-4"
            >
                {copy.back}
            </Link>
            <Display as="h1" className="mt-2 text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {chapter.title}
            </Display>
            <p className="mt-3 max-w-[62ch] text-night/70">{chapter.summary}</p>
            <p className="mt-1 text-sm text-night/70">{copy.reviewed(reviewedOn(chapter.reviewedAt))}</p>

            <div className="mt-8">
                <MindMap
                    root={chapter.map}
                    label={copy.mapLabel(chapter.title)}
                    onActivate={(node) => node.section && openSection(node.section)}
                />
                <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <MindMapLegend labels={copy.kinds} />
                    <p className="text-sm text-night/60">{copy.mapHint}</p>
                </div>
            </div>

            <div className="mt-12 grid gap-10 lg:grid-cols-[14rem_minmax(0,1fr)]">
                <nav aria-label={copy.inThisChapter} className="lg:sticky lg:top-36 lg:self-start">
                    <h2 className="font-display text-sm font-bold tracking-[0.06em] uppercase">{copy.inThisChapter}</h2>
                    <ol className="mt-3 space-y-0.5 text-sm">
                        {chapter.sections.map((section) => (
                            <li key={section.id}>
                                <a
                                    href={`#${section.id}`}
                                    className="flex min-h-11 items-center text-night/75 underline-offset-4 hover:text-night hover:underline lg:min-h-9"
                                >
                                    {section.title}
                                </a>
                            </li>
                        ))}
                    </ol>
                </nav>

                <div className="min-w-0">
                    {chapter.sections.map((section) => (
                        <section
                            key={section.id}
                            id={section.id}
                            className="scroll-mt-36 border-t-2 border-dashed border-night/12 py-8 first:border-t-0 first:pt-0"
                        >
                            <h2
                                id={`secao-${section.id}`}
                                tabIndex={-1}
                                className="font-display text-lg font-bold tracking-[0.03em] uppercase"
                            >
                                {section.title}
                            </h2>
                            {/* Safe HTML: rendered on the server from the versioned manual, raw HTML stripped. */}
                            <div
                                className="prose-ovni mt-4 text-base!"
                                dangerouslySetInnerHTML={{ __html: section.html }}
                            />
                        </section>
                    ))}
                </div>
            </div>

            <nav
                aria-label={copy.neighbours}
                className="mt-6 grid gap-3 border-t-2 border-night/12 pt-6 sm:grid-cols-2"
            >
                {previous ? (
                    <Link
                        href={`/painel/manual/${previous.slug}`}
                        className="flex min-h-11 flex-col justify-center rounded-2xl px-4 py-3 ring-1 ring-night/15 hover:ring-night/40"
                    >
                        <span className="text-xs text-night/60">{copy.previous}</span>
                        <span className="font-semibold">{previous.title}</span>
                    </Link>
                ) : (
                    <span />
                )}
                {next && (
                    <Link
                        href={`/painel/manual/${next.slug}`}
                        className="flex min-h-11 flex-col justify-center rounded-2xl px-4 py-3 text-right ring-1 ring-night/15 hover:ring-night/40"
                    >
                        <span className="text-xs text-night/60">{copy.next}</span>
                        <span className="font-semibold">{next.title}</span>
                    </Link>
                )}
            </nav>
        </>
    );
}

Chapter.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
