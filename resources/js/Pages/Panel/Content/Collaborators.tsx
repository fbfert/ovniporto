import { Head, Link, router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { ConfirmButton } from '@/Components/Panel/ConfirmButton';
import { Badge, Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.collaborators;
const common = t.panel.content;
const areaLabels: Record<string, string> = Object.fromEntries(
    t.collaborator.areaOptions.map((option) => [option.value, option.label]),
);

interface Collaborator {
    id: number;
    name: string;
    email: string;
    location: string;
    areas: string[];
    message: string;
    consentedAt: string;
}

const day = (iso: string) => new Date(iso).toLocaleDateString('pt-BR', { timeZone: 'America/Sao_Paulo' });

export default function Collaborators({ collaborators }: { collaborators: Collaborator[] }) {
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
                    <p className="mt-2 font-script text-2xl text-horizon">{copy.lead(collaborators.length)}</p>
                </div>
                {/* A plain link: the browser downloads the CSV, Inertia stays out of it. */}
                <a
                    href="/painel/colaboradores/exportar"
                    className="inline-flex min-h-11 items-center rounded-full border-[1.5px] border-night/70 px-6 font-semibold hover:bg-night/5"
                >
                    {copy.export}
                </a>
            </div>

            {collaborators.length === 0 ? (
                <p className="mt-10 rounded-[22px] border-2 border-dashed border-night/20 p-10 text-center font-script text-2xl text-horizon">
                    {copy.empty}
                </p>
            ) : (
                <ul className="mt-10 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                    {collaborators.map((c) => (
                        <li key={c.id} className="flex flex-col gap-3 py-5">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <span className="min-w-0">
                                    <span className="block font-semibold">{c.name}</span>
                                    <a
                                        href={`mailto:${c.email}`}
                                        className="block break-all underline underline-offset-4"
                                    >
                                        {c.email}
                                    </a>
                                    <span className="text-sm text-night/60">
                                        {c.location} · {day(c.consentedAt)}
                                    </span>
                                </span>
                                <ConfirmButton
                                    title={common.confirmRemove}
                                    confirmLabel={copy.remove}
                                    lead={c.email}
                                    onConfirm={(done) =>
                                        router.delete(`/painel/colaboradores/${c.id}`, {
                                            preserveScroll: true,
                                            onFinish: done,
                                        })
                                    }
                                >
                                    {copy.remove}
                                </ConfirmButton>
                            </div>
                            <div className="flex flex-wrap gap-2" aria-label={copy.areas}>
                                {c.areas.map((area) => (
                                    <Badge key={area} tone="neutral">
                                        {areaLabels[area] ?? area}
                                    </Badge>
                                ))}
                            </div>
                            <p className="max-w-[70ch] leading-relaxed whitespace-pre-line text-night/80">
                                {c.message}
                            </p>
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}

Collaborators.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
