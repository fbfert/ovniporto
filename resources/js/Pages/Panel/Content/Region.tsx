import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Button } from '@/Components/Ui/Button';
import { Badge, Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.region;
const common = t.panel.content;

interface Partner {
    id: number;
    name: string;
    slug: string;
    type: string;
    city: string;
    consentGivenAt: string | null;
    publishedAt: string | null;
    isDemo: boolean;
}

export default function Region({ partners }: { partners: Partner[] }) {
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
                <Button href="/painel/regiao/novo">{copy.newPartner}</Button>
            </div>

            {partners.length === 0 ? (
                <p className="mt-10 rounded-[22px] border-2 border-dashed border-night/20 p-10 text-center font-script text-2xl text-horizon">
                    {copy.empty}
                </p>
            ) : (
                <ul className="mt-10 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                    {partners.map((partner) => (
                        <li key={partner.id}>
                            <Link
                                href={`/painel/regiao/${partner.id}`}
                                className="flex min-h-11 flex-wrap items-center justify-between gap-3 rounded-2xl px-2 py-4 outline-none hover:bg-night/4 focus-visible:ring-2 focus-visible:ring-beam"
                            >
                                <span>
                                    <span className="font-semibold">{partner.name}</span>
                                    <span className="ml-2 text-sm text-night/60">
                                        {t.region.types[partner.type]} · {partner.city}
                                    </span>
                                </span>
                                <span className="flex flex-wrap gap-2">
                                    {partner.isDemo && <Badge tone="horizon">{copy.demo}</Badge>}
                                    {!partner.consentGivenAt && <Badge tone="car">{copy.noConsent}</Badge>}
                                    {partner.publishedAt ? (
                                        <Badge tone="beam">{copy.published}</Badge>
                                    ) : (
                                        <Badge>{copy.draft}</Badge>
                                    )}
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}

Region.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
