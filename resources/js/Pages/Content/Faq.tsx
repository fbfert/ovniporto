import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { Prose } from '@/Components/Content/Prose';
import { MailIcon } from '@/Components/Icons';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Accordion } from '@/Components/Ui/Accordion';
import { Button } from '@/Components/Ui/Button';
import { Section } from '@/Components/Ui/Section';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { SharedProps } from '@/types';

const copy = t.faqPage;

interface FaqItem {
    question: string;
    answerHtml: string;
}

export default function Faq({ faqs }: { faqs: FaqItem[] }) {
    const { community } = usePage<SharedProps>().props;

    return (
        <>
            <SeoHead title={copy.title} description={copy.description} />
            <PageCover eyebrow={copy.eyebrow} title={copy.title} />

            <Section tone="light">
                <div className="grid gap-14 lg:grid-cols-[minmax(0,1fr)_17rem] lg:gap-20">
                    {faqs.length > 0 ? (
                        <Accordion
                            numbered
                            items={faqs.map((faq) => ({
                                title: faq.question,
                                content: <Prose html={faq.answerHtml} className="text-night/80" />,
                            }))}
                        />
                    ) : (
                        <p className="font-script text-2xl text-horizon">{copy.empty}</p>
                    )}

                    <aside className="self-start rounded-[22px] bg-night/[0.035] p-6 ring-1 ring-night/10 lg:sticky lg:top-28">
                        <p className="font-script text-2xl leading-tight text-horizon">{copy.missing}</p>
                        <div className="mt-4">
                            <Button
                                href={`mailto:${community.email}`}
                                external
                                variant="secondary"
                                iconLeft={<MailIcon size="1.1rem" />}
                            >
                                {copy.write}
                            </Button>
                        </div>
                    </aside>
                </div>
            </Section>
        </>
    );
}

Faq.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
