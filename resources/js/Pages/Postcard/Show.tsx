import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { FlipPostcard } from '@/Components/Postcard/FlipPostcard';
import { PostcardShare } from '@/Components/Postcard/PostcardShare';
import { Section } from '@/Components/Ui/Section';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.postcardPage;

export default function PostcardPage({ shareUrl, imageUrl }: { shareUrl: string; imageUrl: string }) {
    return (
        <>
            <SeoHead />
            <PageCover eyebrow={copy.eyebrow} title={copy.title} lead={copy.lead} />

            <Section tone="light">
                <div className="grid items-center gap-12 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] lg:gap-16">
                    <FlipPostcard imageUrl={imageUrl} />
                    <div className="max-w-md">
                        <PostcardShare url={shareUrl} imageUrl={imageUrl} />
                        <p className="mt-8 text-sm leading-relaxed text-night/70">{copy.note}</p>
                    </div>
                </div>
            </Section>
        </>
    );
}

PostcardPage.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
