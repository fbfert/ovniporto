import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { Prose } from '@/Components/Content/Prose';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import { Section } from '@/Components/Ui/Section';
import { Badge } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import { postDate, type DiaryPostCard } from './Diary';

const copy = t.diaryPage;

interface Post extends DiaryPostCard {
    bodyHtml: string;
    gallery: { url: string; alt: string }[];
}

export default function DiaryPost({ post }: { post: Post }) {
    return (
        <>
            <SeoHead />
            <PageCover eyebrow={copy.title} title={post.title}>
                <p className="mt-6 flex flex-wrap items-center justify-center gap-3 text-sm text-moonlight/75">
                    <Badge tone="beam">{t.place.phase(post.phase)}</Badge>
                    <time dateTime={post.publishedAt}>{postDate(post.publishedAt)}</time>
                </p>
            </PageCover>

            <Section tone="light">
                <article className="mx-auto max-w-[68ch]">
                    {post.cover && (
                        <img
                            src={post.cover}
                            alt={post.coverAlt ?? ''}
                            className="mb-10 aspect-[16/10] w-full rounded-[22px] object-cover"
                        />
                    )}
                    <Prose html={post.bodyHtml} className="text-night/85" />
                    {post.gallery.length > 0 && (
                        <section aria-labelledby="galeria" className="mt-14">
                            <h2
                                id="galeria"
                                className="text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase"
                            >
                                {copy.gallery}
                            </h2>
                            <ul className="mt-4 grid grid-cols-2 gap-3">
                                {post.gallery.map((image) => (
                                    <li key={image.url}>
                                        <img
                                            src={image.url}
                                            alt={image.alt}
                                            loading="lazy"
                                            className="aspect-square w-full rounded-[14px] object-cover"
                                        />
                                    </li>
                                ))}
                            </ul>
                        </section>
                    )}
                    <div className="mt-14">
                        <Button href="/obra" variant="secondary">
                            {copy.back}
                        </Button>
                    </div>
                </article>
            </Section>
        </>
    );
}

DiaryPost.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
