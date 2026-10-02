import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Button } from '@/Components/Ui/Button';
import { Badge, Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.diary;
const common = t.panel.content;

interface Post {
    id: number;
    slug: string;
    title: string;
    phase: number;
    publishedAt: string | null;
    cover: string | null;
}

const day = (iso: string) =>
    new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });

/** Draft, scheduled or published, decided by the date alone (as the public site decides). */
function State({ publishedAt, now }: { publishedAt: string | null; now: number }) {
    if (publishedAt === null) return <Badge>{copy.draft}</Badge>;
    return Date.parse(publishedAt) > now ? (
        <Badge tone="car">{copy.scheduled(day(publishedAt))}</Badge>
    ) : (
        <Badge tone="beam">{copy.published(day(publishedAt))}</Badge>
    );
}

export default function Diary({ posts, now }: { posts: Post[]; now: string }) {
    const nowMs = Date.parse(now);
    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Link href="/painel/conteudo" className="min-h-11 font-semibold underline underline-offset-4">
                ← {common.back}
            </Link>
            <div className="mt-6 flex flex-wrap items-end justify-between gap-4">
                <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                    {copy.title}
                </Display>
                <Button href="/painel/obra/novo">{copy.newPost}</Button>
            </div>

            {posts.length === 0 ? (
                <p className="mt-10 rounded-[22px] border-2 border-dashed border-night/20 p-10 text-center font-script text-2xl text-horizon">
                    {copy.empty}
                </p>
            ) : (
                <ul className="mt-10 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                    {posts.map((post) => (
                        <li key={post.id}>
                            <Link
                                href={`/painel/obra/${post.id}`}
                                className="grid grid-cols-[4.5rem_minmax(0,1fr)] items-center gap-4 rounded-2xl px-2 py-4 outline-none hover:bg-night/4 focus-visible:ring-2 focus-visible:ring-beam"
                            >
                                <span className="block aspect-square overflow-hidden rounded-xl bg-night">
                                    {post.cover && <img src={post.cover} alt="" className="size-full object-cover" />}
                                </span>
                                <span className="min-w-0">
                                    <span className="block font-semibold">{post.title}</span>
                                    <span className="mt-1 flex flex-wrap items-center gap-2 text-sm text-night/65">
                                        {t.placePage.phaseTitle(post.phase)}
                                        <State publishedAt={post.publishedAt} now={nowMs} />
                                    </span>
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}

Diary.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
