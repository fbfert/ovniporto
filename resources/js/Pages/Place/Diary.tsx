import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import { ConceptImage } from '@/Components/Ui/ConceptImage';
import { NightSkyArt } from '@/Components/Ui/Polaroid';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge } from '@/Components/Ui/Typography';
import { longDate, t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.diaryPage;

export interface DiaryPostCard {
    slug: string;
    title: string;
    excerpt: string | null;
    cover: string | null;
    coverAlt: string | null;
    phase: number;
    publishedAt: string;
}

export const postDate = (iso: string) => longDate(iso.slice(0, 10));

/** No post yet: the runway concept art and the promise of what the first post will be. */
function EmptyDiary() {
    return (
        <div className="grid items-center gap-10 md:grid-cols-2 md:gap-16">
            <ConceptImage
                slug="cover-alt"
                sizes="(min-width: 768px) 50vw, 100vw"
                className="aspect-[16/10] rounded-[22px] bg-night"
                badgeClassName="top-4 right-4"
            />
            <p className="max-w-[30ch] font-script text-[clamp(1.7rem,1.3rem+1.6vw,2.4rem)] leading-tight text-horizon">
                {copy.empty}
            </p>
        </div>
    );
}

export default function Diary({ posts }: { posts: DiaryPostCard[] }) {
    return (
        <>
            <SeoHead />
            <PageCover eyebrow={copy.eyebrow} title={copy.title} />

            <Section tone="light">
                {posts.length === 0 ? (
                    <EmptyDiary />
                ) : (
                    <Reveal stagger as="ol" className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                        {posts.map((post) => (
                            <RevealItem as="li" key={post.slug}>
                                <Link href={`/obra/${post.slug}`} className="group block">
                                    <div className="relative aspect-[16/10] overflow-hidden rounded-[22px] bg-night">
                                        {post.cover ? (
                                            <img
                                                src={post.cover}
                                                alt={post.coverAlt ?? ''}
                                                loading="lazy"
                                                className="h-full w-full object-cover transition-transform duration-500 ease-snap [@media(hover:hover)]:group-hover:scale-[1.03]"
                                            />
                                        ) : (
                                            <NightSkyArt label={t.place.phase(post.phase)} seed={post.slug.length} />
                                        )}
                                    </div>
                                    <p className="mt-4 flex flex-wrap items-center gap-2 text-sm text-night/60">
                                        <Badge tone={post.phase === 1 ? 'beam' : 'neutral'}>
                                            {t.place.phase(post.phase)}
                                        </Badge>
                                        <time dateTime={post.publishedAt}>{postDate(post.publishedAt)}</time>
                                    </p>
                                    <h2 className="mt-2 font-display text-lg leading-tight font-bold tracking-[0.03em] uppercase group-hover:underline">
                                        {post.title}
                                    </h2>
                                    {post.excerpt && (
                                        <p className="mt-2 leading-relaxed text-night/75">{post.excerpt}</p>
                                    )}
                                </Link>
                            </RevealItem>
                        ))}
                    </Reveal>
                )}
                <div className="mt-14">
                    <Button href="/obra.rss" external variant="ghost">
                        {copy.rss}
                    </Button>
                </div>
            </Section>
        </>
    );
}

Diary.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
