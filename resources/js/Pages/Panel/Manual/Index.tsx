import { Head, Link, router } from '@inertiajs/react';
import { useMemo, type ReactNode } from 'react';
import { Seal } from '@/Components/Brand/Seal';
import { MindMap, type MapNode } from '@/Components/Panel/MindMap';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';
import { reviewedOn } from '@/Components/Panel/reviewedOn';

const copy = t.panel.manual;

export interface ChapterSummary {
    slug: string;
    group: string;
    title: string;
    summary: string;
    reviewedAt: string;
}

/** The overview map: one branch per group, one leaf per chapter the reader may open. */
function overview(chapters: ChapterSummary[]): MapNode {
    const groups = [...new Set(chapters.map((c) => c.group))];
    return {
        label: copy.center,
        children: groups.map((group) => ({
            label: copy.groups[group] ?? group,
            children: chapters
                .filter((c) => c.group === group)
                .map((c) => ({ label: c.title, href: `/painel/manual/${c.slug}` })),
        })),
    };
}

const number = (n: number) => String(n).padStart(2, '0');

export default function Index({ chapters }: { chapters: ChapterSummary[] }) {
    const map = useMemo(() => overview(chapters), [chapters]);

    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Eyebrow>{copy.eyebrow}</Eyebrow>
            <Display as="h1" className="mt-4 text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.title}
            </Display>
            <p className="mt-3 max-w-[62ch] text-night/70">{copy.lead}</p>

            <div className="mt-8">
                <MindMap
                    root={map}
                    label={copy.overviewLabel}
                    center={<Seal size="sm" className="-my-1 size-8!" />}
                    onActivate={(node) => node.href && router.visit(node.href)}
                />
                <p className="mt-3 text-sm text-night/60">{copy.mapHint}</p>
            </div>

            <h2 className="mt-12 font-display text-sm font-bold tracking-[0.06em] uppercase">{copy.chaptersTitle}</h2>
            <ol className="mt-4 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                {chapters.map((chapter, i) => (
                    <li key={chapter.slug}>
                        <Link
                            href={`/painel/manual/${chapter.slug}`}
                            className="group grid min-h-11 grid-cols-[3.25rem_minmax(0,1fr)] gap-x-4 py-4 sm:grid-cols-[4rem_minmax(0,1fr)_auto]"
                        >
                            <span
                                aria-hidden
                                className="row-span-2 font-display text-2xl font-extrabold text-horizon text-outline sm:text-3xl"
                            >
                                {number(i + 1)}
                            </span>
                            <span className="font-semibold underline-offset-4 group-hover:underline">
                                {chapter.title}
                            </span>
                            <span className="col-start-2 text-sm text-night/70">{chapter.summary}</span>
                            <span className="col-start-2 mt-1 text-xs text-night/70 sm:col-start-3 sm:row-start-1 sm:mt-0 sm:text-right">
                                {copy.reviewed(reviewedOn(chapter.reviewedAt))}
                            </span>
                        </Link>
                    </li>
                ))}
            </ol>
        </>
    );
}

Index.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
