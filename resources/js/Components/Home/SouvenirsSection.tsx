import { SealArt } from '@/Components/Brand/Seal';
import { Button } from '@/Components/Ui/Button';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { TicketCard } from '@/Components/Ui/TicketCard';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import type { ProductCard } from '@/types';

const SLOTS = 3;

/** Section 06. Souvenirs as admission tickets; empty slots stay as dashed stubs, never fake products. */
export function SouvenirsSection({ lead, products }: { lead: string; products: ProductCard[] }) {
    const stubs = Math.max(0, SLOTS - products.length);

    return (
        <Section tone="dark" wave labelledBy="lembrancas">
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
                            href={`/loja#${product.slug}`}
                            label={product.label}
                            title={product.name}
                            price={money(product.priceCents)}
                            comparePrice={product.comparePriceCents ? money(product.comparePriceCents) : null}
                            meta={product.madeToOrder ? t.store.madeToOrder(product.productionDays) : t.store.ready}
                            art={
                                product.image ? (
                                    <img src={product.image} alt={product.imageAlt ?? product.name} loading="lazy" className="h-full w-full object-cover" />
                                ) : (
                                    <div className="flex h-full items-center justify-center bg-[radial-gradient(circle_at_50%_40%,rgb(84_201_51/0.22),transparent_60%)]">
                                        <div className="w-[52%] -rotate-6 drop-shadow-[0_14px_20px_rgb(6_17_33/0.6)] transition-transform duration-500 ease-snap [@media(hover:hover)]:group-hover/ticket:rotate-0">
                                            <SealArt title={product.imageAlt ?? product.name} />
                                        </div>
                                    </div>
                                )
                            }
                        />
                    </RevealItem>
                ))}
                {Array.from({ length: stubs }, (_, i) => (
                    <RevealItem as="li" key={`stub-${i}`} className={i > 0 ? 'hidden lg:block' : ''}>
                        <div className="flex h-full min-h-72 flex-col items-center justify-center gap-2 rounded-[18px] border-2 border-dashed border-moonlight/20 p-8 text-center">
                            <p className="font-script text-2xl text-beam-glow">{products.length === 0 ? t.store.empty : 'Próxima lembrança em produção'}</p>
                            <p className="text-sm text-moonlight/60">Camiseta, caneca e Kit Abdução, feitos sob pedido.</p>
                        </div>
                    </RevealItem>
                ))}
            </Reveal>
        </Section>
    );
}
