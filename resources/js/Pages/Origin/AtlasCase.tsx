import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { AtlasPostcard } from '@/Components/Origin/AtlasPostcard';
import { CreditedImage } from '@/Components/Origin/CreditedImage';
import { ConfidenceSeal } from '@/Components/Origin/SourceBadge';
import { Timeline } from '@/Components/Origin/Timeline';
import { Button } from '@/Components/Ui/Button';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { AtlasCaseDetail, AtlasNeighbour, ImageCredit } from '@/types/origin';

const copy = t.origin.casePage;
const LAGES = 'lages';

/** Rows the page already shows better: the sources are listed with titles, the grade is on the seal. */
const SHOWN_ELSEWHERE = ['Fonte-chave', 'Fontes-chave', 'Confiança'];

interface Props {
    case: AtlasCaseDetail;
    image: ImageCredit | null;
    previous: AtlasNeighbour | null;
    next: AtlasNeighbour | null;
    total: number;
}

function SubTitle({ id, children }: { id: string; children: ReactNode }) {
    return (
        <h2 id={id} className="font-display text-xl leading-tight font-extrabold tracking-[0.04em] uppercase">
            {children}
        </h2>
    );
}

/** "Do lado de cá": the one case that is ours, told as what it is today. */
function LagesNote() {
    return (
        <aside className="rounded-[22px] border-2 border-dashed border-car bg-car/15 p-6 sm:p-8">
            <div className="flex flex-wrap items-center gap-3">
                <p className="font-display text-lg font-extrabold tracking-[0.04em] uppercase">{copy.lagesTitle}</p>
                <Badge tone="car">{t.origin.atlasPage.planned}</Badge>
            </div>
            <p className="mt-3 max-w-[56ch] leading-relaxed text-night/80">{copy.lagesBody}</p>
            <div className="mt-5">
                <Button href="/o-lugar" variant="ghost">
                    {copy.lagesCta}
                </Button>
            </div>
        </aside>
    );
}

export default function AtlasCase({ case: item, image, previous, next, total }: Props) {
    return (
        <>
            <SeoHead />
            <Section tone="light" innerClassName="pt-32! sm:pt-36!">
                <nav aria-label={copy.atlas} className="text-[0.9rem]">
                    <Link href="/origem/atlas" className="font-semibold text-horizon underline underline-offset-4">
                        {copy.atlas}
                    </Link>
                    <span aria-hidden className="mx-2 text-night/40">
                        /
                    </span>
                    <span className="text-night/70">{copy.position(item.number, total)}</span>
                </nav>

                <div className="mt-8 grid gap-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-start">
                    <div>
                        <Eyebrow>{item.country}</Eyebrow>
                        <div className="mt-3 flex flex-col items-start gap-5">
                            <Display
                                as="h1"
                                className="w-full min-w-0 text-[clamp(1.5rem,0.6rem+2.4vw,2.6rem)]! text-balance [overflow-wrap:anywhere]"
                            >
                                {item.name}
                            </Display>
                            <ConfidenceSeal seal={item.seal} />
                        </div>
                        {item.category && (
                            <p className="mt-6 max-w-[48ch] text-lg leading-relaxed text-night/80">{item.category}</p>
                        )}
                        {item.confidence && (
                            <p className="mt-4 max-w-[52ch] text-[0.95rem] text-night/65">
                                <span className="font-semibold text-night/80">{t.origin.confidence}: </span>
                                {item.confidence}
                            </p>
                        )}
                        {item.slug === LAGES && (
                            <div className="mt-8">
                                <LagesNote />
                            </div>
                        )}
                        {item.slug === 'cachi' && (
                            <div className="mt-8">
                                <Button href="/origem/cachi">{copy.readCachi}</Button>
                            </div>
                        )}
                    </div>
                    <div className="flex flex-col gap-8">
                        <AtlasPostcard
                            caseSlug={item.slug}
                            sizes="(min-width: 1024px) 40rem, 92vw"
                            className="rounded-[18px]"
                            priority
                        />
                        {image && (
                            <CreditedImage
                                credit={image}
                                caption={item.image?.caption}
                                sizes="(min-width: 1024px) 40rem, 92vw"
                            />
                        )}
                    </div>
                </div>
            </Section>

            <Section tone="dusk" wave>
                <div className="grid gap-16 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
                    <section aria-labelledby="dados">
                        <SubTitle id="dados">{copy.factsTitle}</SubTitle>
                        <dl className="mt-6 divide-y divide-night/10 border-y border-night/10">
                            {item.facts
                                .filter((fact) => !SHOWN_ELSEWHERE.includes(fact.label))
                                .map((fact) => (
                                    <div
                                        key={fact.label}
                                        className="grid gap-1 py-4 sm:grid-cols-[12rem_minmax(0,1fr)] sm:gap-6"
                                    >
                                        <dt className="text-[0.9rem] font-semibold text-night/65">{fact.label}</dt>
                                        <dd className="leading-relaxed text-night/85">{fact.value}</dd>
                                    </div>
                                ))}
                        </dl>

                        {item.claims.length > 0 && (
                            <section aria-labelledby="afirmacoes" className="mt-14">
                                <SubTitle id="afirmacoes">{copy.claimsTitle}</SubTitle>
                                <ul className="mt-6 space-y-4">
                                    {item.claims.map((claim) => (
                                        <li
                                            key={claim.text}
                                            className="flex gap-4 rounded-[18px] bg-moonlight p-4 ring-1 ring-night/10"
                                        >
                                            <ConfidenceSeal seal={claim.seal} size="sm" />
                                            <p className="leading-relaxed text-night/80">{claim.text}</p>
                                        </li>
                                    ))}
                                </ul>
                            </section>
                        )}

                        {item.chronology.length > 0 && (
                            <section aria-labelledby="cronologia-caso" className="mt-14">
                                <SubTitle id="cronologia-caso">{copy.chronologyTitle}</SubTitle>
                                <div className="mt-8">
                                    <Timeline
                                        items={item.chronology.map((row) => ({
                                            when: row.date,
                                            title: row.event.split('. ')[0] ?? row.event,
                                            body: `${row.event.split('. ').slice(1).join('. ')} (${t.origin.confidence} ${row.grade})`,
                                        }))}
                                    />
                                </div>
                            </section>
                        )}
                    </section>

                    <div className="flex flex-col gap-14">
                        <section aria-labelledby="fontes-caso">
                            <SubTitle id="fontes-caso">{copy.sourcesTitle}</SubTitle>
                            {item.sources.length > 0 ? (
                                <ol className="mt-6 space-y-5">
                                    {item.sources.map((source) => (
                                        <li key={source.id}>
                                            {source.url ? (
                                                <a
                                                    href={source.url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="font-semibold underline decoration-night/30 underline-offset-4 hover:decoration-horizon"
                                                >
                                                    {source.title}
                                                </a>
                                            ) : (
                                                <span className="font-semibold">{source.title}</span>
                                            )}
                                            <span className="block text-[0.85rem] text-night/60">
                                                {[
                                                    source.publisher,
                                                    source.date,
                                                    source.grade && `${t.origin.confidence} ${source.grade}`,
                                                    source.accessed && t.origin.atlasPage.accessed(source.accessed),
                                                ]
                                                    .filter(Boolean)
                                                    .join(' · ')}
                                            </span>
                                            <span className="mt-1 block text-[0.92rem] leading-relaxed text-night/75">
                                                {source.note}
                                            </span>
                                        </li>
                                    ))}
                                </ol>
                            ) : (
                                <p className="mt-6 font-script text-2xl text-horizon">{copy.noSources}</p>
                            )}
                        </section>

                        <section aria-labelledby="questoes">
                            <SubTitle id="questoes">{copy.questionsTitle}</SubTitle>
                            <ul className="mt-6 space-y-3">
                                {item.openQuestions.map((q) => (
                                    <li key={q} className="border-l-4 border-car pl-4 leading-relaxed text-night/80">
                                        {q}
                                    </li>
                                ))}
                            </ul>
                        </section>
                    </div>
                </div>
            </Section>

            <Section tone="light">
                <nav aria-label={copy.atlas} className="grid gap-4 sm:grid-cols-3 sm:items-center">
                    <div>
                        {previous && (
                            <Link href={`/origem/atlas/${previous.slug}`} className="group flex min-h-11 flex-col">
                                <span className="font-script text-xl text-horizon">← {copy.previous}</span>
                                <span className="font-display text-[0.95rem] font-bold tracking-[0.03em] uppercase group-hover:underline">
                                    {previous.name}
                                </span>
                            </Link>
                        )}
                    </div>
                    <div className="sm:text-center">
                        <Link
                            href="/origem/atlas"
                            className="inline-flex min-h-11 items-center font-semibold text-night/75 underline underline-offset-4 hover:text-horizon"
                        >
                            {copy.backToAtlas}
                        </Link>
                    </div>
                    <div className="sm:text-right">
                        {next && (
                            <Link
                                href={`/origem/atlas/${next.slug}`}
                                className="group flex min-h-11 flex-col sm:items-end"
                            >
                                <span className="font-script text-xl text-horizon">{copy.next} →</span>
                                <span className="font-display text-[0.95rem] font-bold tracking-[0.03em] uppercase group-hover:underline">
                                    {next.name}
                                </span>
                            </Link>
                        )}
                    </div>
                </nav>
            </Section>
        </>
    );
}

AtlasCase.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
