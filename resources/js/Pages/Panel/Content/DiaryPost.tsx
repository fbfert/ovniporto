import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { ConfirmButton } from '@/Components/Panel/ConfirmButton';
import { MarkdownEditor } from '@/Components/Panel/MarkdownEditor';
import { PanelSection } from '@/Components/Panel/PanelSection';
import { Button } from '@/Components/Ui/Button';
import { SelectField, TextField } from '@/Components/Ui/Fields';
import { Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.diary;
const common = t.panel.content;

interface Post {
    id: number;
    slug: string;
    title: string;
    excerpt: string | null;
    body: string;
    phase: number;
    publishedAt: string | null;
    cover: string | null;
    coverAlt: string | null;
}

/** "2026-11-01T22:00:00Z" → "2026-11-01T19:00" in the browser's own time zone, for datetime-local. */
function toLocalInput(iso: string | null): string {
    if (!iso) return '';
    const date = new Date(iso);
    const local = new Date(date.getTime() - date.getTimezoneOffset() * 60_000);
    return local.toISOString().slice(0, 16);
}

/** The server keeps UTC: the local time typed by the admin travels as a full ISO date. */
const fromLocalInput = (value: string) => (value ? new Date(value).toISOString() : '');

export default function DiaryPost({ post }: { post: Post | null }) {
    const form = useForm<{
        title: string;
        excerpt: string;
        body: string;
        phase: string;
        publishedAt: string;
        cover: File | null;
        coverAlt: string;
    }>({
        title: post?.title ?? '',
        excerpt: post?.excerpt ?? '',
        body: post?.body ?? '',
        phase: String(post?.phase ?? 1),
        publishedAt: toLocalInput(post?.publishedAt ?? null),
        cover: null,
        coverAlt: post?.coverAlt ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, publishedAt: fromLocalInput(data.publishedAt) }));
        form.post(post ? `/painel/obra/${post.id}` : '/painel/obra', { preserveScroll: true, forceFormData: true });
    };

    return (
        <>
            <Head title={`${post?.title ?? copy.newPost} · ${t.panel.title}`} />
            <Link href="/painel/obra" className="min-h-11 font-semibold underline underline-offset-4">
                ← {copy.title}
            </Link>
            <div className="mt-6 flex flex-wrap items-end justify-between gap-4">
                <Display as="h1" className="text-[clamp(1.6rem,1.1rem+2vw,2.4rem)]!">
                    {post?.title ?? copy.newPost}
                </Display>
                {post && (
                    <a
                        href={`/obra/${post.slug}`}
                        target="_blank"
                        rel="noreferrer"
                        className="min-h-11 font-semibold underline underline-offset-4"
                    >
                        {copy.view}
                    </a>
                )}
            </div>

            <form onSubmit={submit} className="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,0.6fr)]">
                <div className="space-y-5">
                    <TextField
                        label={copy.postTitle}
                        value={form.data.title}
                        onChange={(e) => form.setData('title', e.target.value)}
                        error={form.errors.title}
                        maxLength={160}
                    />
                    <TextField
                        label={copy.excerpt}
                        value={form.data.excerpt}
                        onChange={(e) => form.setData('excerpt', e.target.value)}
                        error={form.errors.excerpt}
                        maxLength={255}
                    />
                    <MarkdownEditor
                        label={copy.body}
                        value={form.data.body}
                        onChange={(body) => form.setData('body', body)}
                        error={form.errors.body}
                        rows={16}
                    />
                </div>

                <div className="space-y-6">
                    <PanelSection title={copy.publishedAt}>
                        <div className="space-y-4">
                            <TextField
                                type="datetime-local"
                                label={copy.publishedAt}
                                hideLabel
                                value={form.data.publishedAt}
                                onChange={(e) => form.setData('publishedAt', e.target.value)}
                                error={form.errors.publishedAt}
                            />
                            <p className="text-sm text-night/60">{copy.publishedAtHint}</p>
                            <SelectField
                                label={copy.phase}
                                value={form.data.phase}
                                onChange={(e) => form.setData('phase', e.target.value)}
                                options={[1, 2, 3, 4, 5].map((n) => ({
                                    value: String(n),
                                    label: t.placePage.phaseTitle(n),
                                }))}
                            />
                        </div>
                    </PanelSection>

                    <PanelSection title={copy.cover}>
                        <div className="space-y-4">
                            {post?.cover && (
                                <img
                                    src={post.cover}
                                    alt={post.coverAlt ?? ''}
                                    className="aspect-[16/10] w-full rounded-xl object-cover"
                                />
                            )}
                            <input
                                type="file"
                                accept="image/*"
                                aria-label={copy.cover}
                                onChange={(e) => form.setData('cover', e.target.files?.[0] ?? null)}
                                className="block min-h-11 w-full text-sm file:mr-4 file:min-h-11 file:rounded-full file:border-0 file:bg-night file:px-4 file:font-semibold file:text-moonlight"
                            />
                            {form.errors.cover && <p className="text-sm font-semibold">{form.errors.cover}</p>}
                            <TextField
                                label={copy.coverAlt}
                                value={form.data.coverAlt}
                                onChange={(e) => form.setData('coverAlt', e.target.value)}
                                error={form.errors.coverAlt}
                                maxLength={255}
                            />
                        </div>
                    </PanelSection>

                    <div className="flex flex-wrap gap-3">
                        <Button type="submit" loading={form.processing}>
                            {common.save}
                        </Button>
                        {post && (
                            <ConfirmButton
                                title={common.confirmRemove}
                                confirmLabel={copy.remove}
                                lead={post.title}
                                onConfirm={(done) => router.delete(`/painel/obra/${post.id}`, { onFinish: done })}
                            >
                                {copy.remove}
                            </ConfirmButton>
                        )}
                    </div>
                </div>
            </form>
        </>
    );
}

DiaryPost.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
