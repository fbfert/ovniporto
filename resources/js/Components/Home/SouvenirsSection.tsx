import { SealArt } from '@/Components/Brand/Seal';
import { Button } from '@/Components/Ui/Button';
import { ConceptImage } from '@/Components/Ui/ConceptImage';
import { ContentImage } from '@/Components/Ui/Picture';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { TicketCard } from '@/Components/Ui/TicketCard';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { ProductStub } from '@/Components/Store/ProductStub';
import type { ProductCard } from '@/types';

const SLOTS = 3;

/** The future customs-and-shop building, faded into the night behind the tickets. */
function CustomsBackdrop() {
    return (
        <ConceptImage
            slug="customs-shop"
            sizes="100vw"
            className="h-full w-full"
            pictureClassName="opacity-45 [mask-image:radial-gradient(85%_75%_at_70%_20%,#000_30%,transparent)]"
            imgClassName="h-full w-full object-cover object-[50%_35%]"
            badgeClassName="top-12 right-5 sm:right-8"
        />
    );
}

/** Section 06. Souvenirs as admission tickets; empty slots stay as dashed stubs, never fake products. */
export function SouvenirsSection({ lead, products }: { lead: string; products: ProductCard[] }) {
    const stubs = Math.max(0, SLOTS - products.length);

    return (
        <Section tone="dark" wave labelledBy="lembrancas" backdrop={<CustomsBackdrop />}>
            <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div>
                    <Eyebrow tone="dark">{t.store.eyebrow}</Eyebrow>
                    <Display as="h2" id="lembrancas" className="mt-3">
                        {t.store.title}
                    </Display>
                    <p className="mt-5 font-script text-[1.9rem] leading-none text-car">{lead}</p>
                </div>
                <Button href="/loja" variant="car" size="lg">
                    {t.store.cta}
                </Button>
            </div>

            <Reveal stagger as="ul" className="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
                                product.image ? (
                                    <ContentImage
                                        src={product.image}
                                        alt={product.imageAlt ?? product.name}
                                        sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
                                        className="h-full w-full"
                                    />
                                ) : (
                                    <div className="flex h-full items-center justify-center bg-[radial-gradient(circle_at_50%_40%,rgb(84_201_51/0.22),transparent_60%)]">
                                        <div className="w-[52%] -rotate-6 drop-shadow-[0_14px_20px_rgb(6_17_33/0.6)] transition-transform duration-500 ease-snap [@media(hover:hover)]:group-hover/ticket:rotate-0">
                                            <SealArt title={product.imageAlt ?? product.name} sizes="12rem" />
                                        </div>
                                    </div>
                                )
                            }
                        />
                    </RevealItem>
                ))}
                <ProductStub freeSlots={stubs} storeEmpty={products.length === 0} />
            </Reveal>
        </Section>
    );
}
