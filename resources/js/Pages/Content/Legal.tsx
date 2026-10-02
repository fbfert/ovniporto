import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { Prose } from '@/Components/Content/Prose';
import { StampIcon } from '@/Components/Icons';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Section } from '@/Components/Ui/Section';
import { longDate, t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.legalPage;

interface Props {
    kind: 'privacy' | 'terms';
    html: string;
    toc: { id: string; title: string }[];
    updatedAt: string | null;
    draft: boolean;
}

/** Long reading page with a fixed index on desktop. The draft notice stays until the text is final. */
export default function Legal({ kind, html, toc, updatedAt, draft }: Props) {
    const page = copy[kind];

    return (
        <>
            <SeoHead title={page.title} description={page.description} />
            <PageCover eyebrow={page.eyebrow} title={page.title}>
                {updatedAt && <p className="mt-6 text-sm text-moonlight/70">{copy.updated(longDate(updatedAt))}</p>}
            </PageCover>

            <Section tone="light">
                {draft && (
                    <div
                        role="note"
                        className="mb-14 flex max-w-4xl items-start gap-4 rounded-[22px] border-2 border-dashed border-car bg-car/15 p-5 sm:p-6"
                    >
                        <StampIcon size="1.75rem" className="mt-0.5 text-night" />
                        <div>
                            <p className="font-display text-sm leading-snug font-bold tracking-[0.04em] uppercase">
                                {copy.draft}
                            </p>
                            <p className="mt-1.5 text-night/75">{copy.draftLead}</p>
                        </div>
                    </div>
                )}

                <div className="grid gap-12 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-20">
                    {toc.length > 0 && (
                        <nav aria-label={copy.toc} className="self-start lg:sticky lg:top-28">
                            <p className="text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase">
                                {copy.toc}
                            </p>
                            <ol className="mt-4 space-y-1 border-l-2 border-dashed border-night/15">
                                {toc.map((item) => (
                                    <li key={item.id}>
                                        <a
                                            href={`#${item.id}`}
                                            className="-ml-0.5 block border-l-2 border-transparent py-1.5 pl-4 text-[0.95rem] text-night/75 transition-colors duration-150 hover:border-horizon hover:text-night"
                                        >
                                            {item.title}
                                        </a>
                                    </li>
                                ))}
                            </ol>
                        </nav>
                    )}
                    <Prose html={html} className="text-night/85" />
                </div>
            </Section>
        </>
    );
}

Legal.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
