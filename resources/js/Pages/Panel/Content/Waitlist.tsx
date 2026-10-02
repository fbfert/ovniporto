import { Head, Link, router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { ConfirmButton } from '@/Components/Panel/ConfirmButton';
import { Badge, Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.waitlist;
const common = t.panel.content;

interface Subscriber {
    id: number;
    email: string;
    source: string;
    consentedAt: string;
    confirmedAt: string | null;
}

const day = (iso: string) => new Date(iso).toLocaleDateString('pt-BR', { timeZone: 'America/Sao_Paulo' });

export default function Waitlist({ subscribers, confirmed }: { subscribers: Subscriber[]; confirmed: number }) {
    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Link href="/painel/conteudo" className="min-h-11 font-semibold underline underline-offset-4">
                ← {common.back}
            </Link>
            <div className="mt-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                        {copy.title}
                    </Display>
                    <p className="mt-2 font-script text-2xl text-horizon">{copy.lead(subscribers.length, confirmed)}</p>
                </div>
                {/* A plain link: the browser downloads the CSV, Inertia stays out of it. */}
                <a
                    href="/painel/avise-me/exportar"
                    className="inline-flex min-h-11 items-center rounded-full border-[1.5px] border-night/70 px-6 font-semibold hover:bg-night/5"
                >
                    {copy.export}
                </a>
            </div>

            {subscribers.length === 0 ? (
                <p className="mt-10 rounded-[22px] border-2 border-dashed border-night/20 p-10 text-center font-script text-2xl text-horizon">
                    {copy.empty}
                </p>
            ) : (
                <ul className="mt-10 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                    {subscribers.map((s) => (
                        <li key={s.id} className="flex flex-wrap items-center justify-between gap-3 py-3">
                            <span className="min-w-0">
                                <span className="block font-semibold break-all">{s.email}</span>
                                <span className="text-sm text-night/60">
                                    {copy.source}: {s.source} · {day(s.consentedAt)}
                                </span>
                            </span>
                            <span className="flex items-center gap-2">
                                <Badge tone={s.confirmedAt ? 'beam' : 'neutral'}>
                                    {s.confirmedAt ? copy.confirmed : copy.pending}
                                </Badge>
                                <ConfirmButton
                                    title={common.confirmRemove}
                                    confirmLabel={copy.remove}
                                    lead={s.email}
                                    onConfirm={(done) =>
                                        router.delete(`/painel/avise-me/${s.id}`, {
                                            preserveScroll: true,
                                            onFinish: done,
                                        })
                                    }
                                >
                                    {copy.remove}
                                </ConfirmButton>
                            </span>
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}

Waitlist.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
