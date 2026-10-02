import { useEffect, useRef, useState } from 'react';
import { t } from '@/i18n/pt-BR';

const copy = t.pay;

/** The slice of the PayPal JS SDK we use. */
interface PayPalNamespace {
    Buttons: (options: {
        style?: Record<string, string>;
        createOrder: () => Promise<string>;
        onApprove: () => Promise<void>;
        onError?: (error: unknown) => void;
    }) => { render: (container: HTMLElement) => Promise<void> };
}

declare global {
    interface Window {
        paypal?: PayPalNamespace;
    }
}

let sdk: Promise<PayPalNamespace> | null = null;

/** Loads the SDK once. Card fields live inside PayPal's own frames: card data never touches our pages' code. */
function loadSdk(clientId: string): Promise<PayPalNamespace> {
    sdk ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        const params = new URLSearchParams({
            'client-id': clientId,
            currency: 'BRL',
            intent: 'capture',
            components: 'buttons',
            'enable-funding': 'card',
            locale: 'pt_BR',
        });
        script.src = `https://www.paypal.com/sdk/js?${params.toString()}`;
        script.async = true;
        script.onload = () => (window.paypal ? resolve(window.paypal) : reject(new Error('paypal sdk')));
        script.onerror = () => {
            sdk = null;
            reject(new Error('paypal sdk'));
        };
        document.head.appendChild(script);
    });
    return sdk;
}

export function PayPalButtons({
    clientId,
    createOrder,
    onApprove,
    onError,
}: {
    clientId: string;
    createOrder: () => Promise<string>;
    onApprove: () => Promise<void>;
    onError: (message: string) => void;
}) {
    const container = useRef<HTMLDivElement>(null);
    const [state, setState] = useState<'loading' | 'ready' | 'failed'>('loading');
    const handlers = useRef({ createOrder, onApprove, onError });
    useEffect(() => {
        handlers.current = { createOrder, onApprove, onError };
    });

    useEffect(() => {
        let cancelled = false;
        loadSdk(clientId)
            .then((paypal) => {
                if (cancelled || !container.current) return;
                return paypal
                    .Buttons({
                        style: { layout: 'vertical', shape: 'pill', color: 'gold', label: 'pay' },
                        createOrder: () => handlers.current.createOrder(),
                        onApprove: () => handlers.current.onApprove(),
                        onError: () => handlers.current.onError(copy.failed),
                    })
                    .render(container.current)
                    .then(() => !cancelled && setState('ready'));
            })
            .catch(() => !cancelled && setState('failed'));
        return () => {
            cancelled = true;
        };
    }, [clientId]);

    return (
        <div>
            {state === 'loading' && <p className="text-sm text-night/60">{copy.loading}</p>}
            {state === 'failed' && (
                <p role="alert" className="font-semibold">
                    {copy.failed}
                </p>
            )}
            <div ref={container} className="min-h-12" />
        </div>
    );
}
