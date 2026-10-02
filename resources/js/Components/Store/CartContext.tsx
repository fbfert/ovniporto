import { router } from '@inertiajs/react';
import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';
import type { SharedProps } from '@/types';

interface CartDrawerState {
    open: boolean;
    setOpen: (open: boolean) => void;
}

const CartDrawerContext = createContext<CartDrawerState>({ open: false, setOpen: () => undefined });

export const useCartDrawer = () => useContext(CartDrawerContext);

/**
 * Opens the drawer when the server says an item was just added (flash
 * "cartOpen"), so adding from any page lands the person in the cart.
 */
export function CartDrawerProvider({ children }: { children: ReactNode }) {
    const [open, setOpen] = useState(false);

    useEffect(
        () =>
            router.on('success', (event) => {
                const props = event.detail.page.props as Partial<SharedProps>;
                if (props.flash?.cartOpen) setOpen(true);
            }),
        [],
    );

    return <CartDrawerContext.Provider value={{ open, setOpen }}>{children}</CartDrawerContext.Provider>;
}
