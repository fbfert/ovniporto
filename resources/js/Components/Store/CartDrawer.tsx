import { Link, router, usePage } from '@inertiajs/react';
import { AnimatePresence, motion } from 'motion/react';
import { useRef } from 'react';
import { BagIcon, CloseIcon } from '@/Components/Icons';
import { Button } from '@/Components/Ui/Button';
import { useFocusTrap } from '@/hooks/useFocusTrap';
import { usePrefersReducedMotion } from '@/hooks/usePrefersReducedMotion';
import { money, t } from '@/i18n/pt-BR';
import { ease } from '@/lib/motion';
import type { CartItem, SharedProps } from '@/types';
import { useCartDrawer } from './CartContext';
import { ProductArt } from './ProductArt';
import { QuantityStepper } from './QuantityStepper';

const copy = t.cart;

/** Every change goes to the server; the cart prop that comes back is the only truth on screen. */
const keep = { preserveScroll: true, preserveState: true } as const;

function Line({ item }: { item: CartItem }) {
    return (
        <li className="grid grid-cols-[4.5rem_minmax(0,1fr)] gap-4 py-4">
            <Link
                href={`/loja/${item.productSlug}`}
                className="block aspect-square overflow-hidden rounded-xl bg-night-blue"
                tabIndex={-1}
                aria-hidden
            >
                <ProductArt image={item.image} alt={item.imageAlt ?? item.productName} sizes="5rem" />
            </Link>
            <div className="min-w-0">
                <div className="flex items-start justify-between gap-3">
                    <div className="min-w-0">
                        <Link href={`/loja/${item.productSlug}`} className="font-semibold hover:underline">
                            {item.productName}
                        </Link>
                        <p className="text-sm text-moonlight/65">
                            {item.variantName}
                            {item.madeToOrder && ` · ${copy.madeToOrder(item.productionDays)}`}
                        </p>
                    </div>
                    <p className="font-semibold text-car tabular-nums">{money(item.lineCents)}</p>
                </div>
                <div className="mt-2 flex items-center justify-between gap-3">
                    <QuantityStepper
                        tone="dark"
                        value={item.quantity}
                        max={item.max}
                        onChange={(quantity) => router.patch(`/carrinho/itens/${item.variantId}`, { quantity }, keep)}
                        label={`${t.storePage.quantity}: ${item.productName}`}
                    />
                    <button
                        type="button"
                        onClick={() => router.delete(`/carrinho/itens/${item.variantId}`, keep)}
                        className="min-h-11 text-sm text-moonlight/70 underline underline-offset-4 hover:text-moonlight"
                    >
                        {copy.remove}
                    </button>
                </div>
            </div>
        </li>
    );
}

/** The cart slides in from the right edge (from the bottom edge on phones). */
export function CartDrawer() {
    const { open, setOpen } = useCartDrawer();
    const { cart, errors } = usePage<SharedProps>().props;
    const panelRef = useRef<HTMLDivElement>(null);
    const reduced = usePrefersReducedMotion();
    useFocusTrap(panelRef, open, () => setOpen(false), 'cart-trigger');

    const motionProps = reduced
        ? { initial: { opacity: 0 }, animate: { opacity: 1 }, exit: { opacity: 0 } }
        : {
              initial: { transform: 'translateX(100%)' },
              animate: { transform: 'translateX(0%)' },
              exit: { transform: 'translateX(100%)' },
          };

    return (
        <AnimatePresence>
            {open && (
                <div className="fixed inset-0 z-[65]">
                    <motion.button
                        type="button"
                        aria-label={copy.close}
                        tabIndex={-1}
                        onClick={() => setOpen(false)}
                        className="absolute inset-0 bg-night/60 backdrop-blur-[2px]"
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.2 }}
                    />
                    <motion.div
                        ref={panelRef}
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="cart-title"
                        data-tone="dark"
                        {...motionProps}
                        transition={{ duration: 0.32, ease: ease.drawer }}
                        className="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-night text-moonlight shadow-[-24px_0_60px_-30px_rgb(0_0_0/0.8)]"
                    >
                        <header className="flex items-center justify-between border-b border-moonlight/10 px-5 py-4">
                            <h2
                                id="cart-title"
                                className="font-display text-lg font-extrabold tracking-[0.04em] uppercase"
                            >
                                {copy.title}
                                <span className="ml-3 font-sans text-sm font-semibold tracking-normal text-moonlight/60 normal-case">
                                    {copy.count(cart.count)}
                                </span>
                            </h2>
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                aria-label={copy.close}
                                className="inline-flex size-11 press items-center justify-center rounded-full ring-1 ring-moonlight/25"
                            >
                                <CloseIcon />
                            </button>
                        </header>

                        {cart.items.length === 0 ? (
                            <div className="flex flex-1 flex-col items-center justify-center gap-5 p-8 text-center">
                                <p className="font-script text-2xl text-beam-glow">{copy.empty}</p>
                                <Button href="/loja" variant="secondary" tone="dark" onClick={() => setOpen(false)}>
                                    {copy.toStore}
                                </Button>
                            </div>
                        ) : (
                            <>
                                <ul className="flex-1 divide-y divide-moonlight/10 overflow-y-auto px-5">
                                    {cart.items.map((item) => (
                                        <Line key={item.variantId} item={item} />
                                    ))}
                                </ul>
                                <footer className="border-t-2 border-dashed border-moonlight/15 px-5 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                                    {errors.quantity && (
                                        <p
                                            role="alert"
                                            className="mb-3 rounded-xl bg-car px-4 py-2 text-sm font-semibold text-night"
                                        >
                                            {errors.quantity}
                                        </p>
                                    )}
                                    <div className="flex items-baseline justify-between">
                                        <span className="text-moonlight/75">{copy.subtotal}</span>
                                        <span className="font-display text-2xl font-extrabold text-car tabular-nums">
                                            {money(cart.subtotalCents)}
                                        </span>
                                    </div>
                                    <p className="mt-1 text-sm text-moonlight/60">{copy.shippingNote}</p>
                                    <Button href="/checkout" variant="car" size="lg" className="mt-4 w-full">
                                        {copy.checkout}
                                    </Button>
                                </footer>
                            </>
                        )}
                    </motion.div>
                </div>
            )}
        </AnimatePresence>
    );
}

/** The bag in the header, with the item count from the server. */
export function CartButton() {
    const { setOpen } = useCartDrawer();
    const { cart } = usePage<SharedProps>().props;
    const count = cart?.count ?? 0;
    return (
        <button
            type="button"
            id="cart-trigger"
            onClick={() => setOpen(true)}
            aria-label={`${copy.open} (${copy.count(count)})`}
            className="relative inline-flex size-11 press items-center justify-center rounded-full"
        >
            <BagIcon size="1.5rem" />
            {count > 0 && (
                <span
                    aria-hidden
                    className="absolute -top-0.5 -right-0.5 flex min-w-5 items-center justify-center rounded-full bg-car px-1 text-[0.7rem] font-bold text-night tabular-nums"
                >
                    {count > 99 ? '99+' : count}
                </span>
            )}
        </button>
    );
}
