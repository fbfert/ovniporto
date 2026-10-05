import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { ProductArt } from '@/Components/Store/ProductArt';
import { Button } from '@/Components/Ui/Button';
import { Marquee } from '@/Components/Ui/Marquee';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { TicketCard } from '@/Components/Ui/TicketCard';
import { Display } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import { ProductStub } from '@/Components/Store/ProductStub';
import type { ProductCard } from '@/types';

const copy = t.storePage;
const SLOTS = 3;

/** /loja. Empty slots stay as dashed stubs: inactive products are never shown, not even as teasers. */
export default function Index({
    products,
    storeSharePercent,
}: {
    products: ProductCard[];
    storeSharePercent: number | null;
}) {
    const stubs = Math.max(0, SLOTS - products.length);

    return (
        <>
            <SeoHead />
            <PageCover eyebrow={copy.eyebrow} title={copy.title}>
                <p className="mx-auto mt-6 max-w-[46ch] text-lg leading-relaxed text-moonlight/85">{copy.lead}</p>
            </PageCover>

            <Section tone="dark" pattern="stars" labelledBy="produtos">
                <h2 id="produtos" className="sr-only">
                    {copy.listLabel}
                </h2>
                <Reveal stagger as="ul" className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {products.map((product) => (
                        <RevealItem as="li" key={product.id}>
                            <TicketCard
                                href={`/loja/${product.slug}`}
                                label={product.label}
                                title={product.name}
                                price={money(product.priceCents)}
                                comparePrice={product.comparePriceCents ? money(product.comparePriceCents) : null}
                                meta={product.madeToOrder ? t.store.madeToOrder(product.productionDays) : t.store.ready}
                                art={
                                    <ProductArt
                                        image={product.image}
                                        alt={product.imageAlt ?? product.name}
                                        sizes="14rem"
                                    />
                                }
                            />
                        </RevealItem>
                    ))}
                    <ProductStub freeSlots={stubs} storeEmpty={products.length === 0} />
                </Reveal>
            </Section>

            <Marquee items={copy.strip} />

            {storeSharePercent !== null && (
                <Section tone="light" labelledBy="pista">
                    <div className="grid gap-6 lg:grid-cols-[auto_minmax(0,1fr)] lg:items-center lg:gap-12">
                        <p className="font-display text-[clamp(4rem,2rem+8vw,8rem)] leading-none font-extrabold text-horizon">
                            {storeSharePercent.toLocaleString('pt-BR')}%
                        </p>
                        <div>
                            <Display as="h2" id="pista" className="text-[clamp(1.6rem,1rem+2.4vw,2.6rem)]!">
                                {copy.runwayTitle}
                            </Display>
                            <p className="mt-4 max-w-[52ch] text-lg text-night/75">
                                {t.supportPage.storeShare(storeSharePercent.toLocaleString('pt-BR'))}
                            </p>
                            <div className="mt-6">
                                <Button href="/apoie" variant="secondary">
                                    {copy.runwayLink}
                                </Button>
                            </div>
                        </div>
                    </div>
                </Section>
            )}
        </>
    );
}

Index.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
