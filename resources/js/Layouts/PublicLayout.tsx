import type { ReactNode } from 'react';
import { Footer } from '@/Components/Layout/Footer';
import { Header } from '@/Components/Layout/Header';
import { SmoothScroll } from '@/Components/Layout/SmoothScroll';
import { CartDrawerProvider } from '@/Components/Store/CartContext';
import { CartDrawer } from '@/Components/Store/CartDrawer';
import { FlashToasts, ToastProvider } from '@/Components/Ui/Toast';
import { t } from '@/i18n/pt-BR';

export function PublicLayout({ children }: { children: ReactNode }) {
    return (
        <ToastProvider>
            <CartDrawerProvider>
                <a
                    href="#conteudo"
                    className="fixed top-3 left-3 z-[80] -translate-y-[200%] rounded-full bg-beam px-5 py-3 font-semibold text-night transition-transform duration-200 ease-snap focus-visible:translate-y-0"
                >
                    {t.nav.skip}
                </a>
                <SmoothScroll />
                <FlashToasts />
                <Header />
                <main id="conteudo" tabIndex={-1} className="outline-none">
                    {children}
                </main>
                <Footer />
                <CartDrawer />
            </CartDrawerProvider>
        </ToastProvider>
    );
}
