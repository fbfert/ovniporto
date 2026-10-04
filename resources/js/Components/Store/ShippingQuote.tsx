import { useState, type FormEvent } from 'react';
import { Button } from '@/Components/Ui/Button';
import { TextField } from '@/Components/Ui/Fields';
import { money, t } from '@/i18n/pt-BR';
import { xsrfToken } from '@/lib/http';

const copy = t.storePage;

interface Option {
    id: string;
    carrier: string;
    service: string;
    priceCents: number;
    days: number;
}

interface Quote {
    cep: string;
    simulated: boolean;
    options: Option[];
}

/** Keeps the field as the person types it, with the dash after the 5th digit. */
const maskCep = (value: string) => {
    const digits = value.replace(/\D/g, '').slice(0, 8);
    return digits.length > 5 ? `${digits.slice(0, 5)}-${digits.slice(5)}` : digits;
};

export type QuoteRequest = (cep: string) => Promise<Quote>;

/** Asks the store's freight endpoint; a 422 brings the CEP message from the server. */
export function requestQuote(variantId: number, quantity: number): QuoteRequest {
    return async (cep) => {
        const response = await fetch('/loja/frete', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
            body: JSON.stringify({ cep, variantId, quantity }),
        });
        const body = (await response.json().catch(() => null)) as (Quote & { message?: string }) | null;
        if (!response.ok || !body) throw new Error(body?.message ?? copy.shippingError);
        return body;
    };
}

/** "Calcular frete": a CEP with fewer than 8 digits is caught here and again on the server. */
export function ShippingQuote({ request }: { request: QuoteRequest }) {
    const [cep, setCep] = useState('');
    const [quote, setQuote] = useState<Quote | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);

    const submit = async (event: FormEvent) => {
        event.preventDefault();
        if (cep.replace(/\D/g, '').length !== 8) {
            setError(t.checkout.cepInvalid);
            setQuote(null);
            return;
        }
        setLoading(true);
        setError(null);
        try {
            setQuote(await request(cep));
        } catch (e) {
            setQuote(null);
            setError(e instanceof Error ? e.message : copy.shippingError);
        } finally {
            setLoading(false);
        }
    };

    return (
        <section aria-labelledby="frete" className="rounded-[22px] bg-night-blue p-5 ring-1 ring-moonlight/10">
            <h2 id="frete" className="font-display text-sm font-bold tracking-[0.06em] uppercase">
                {copy.shippingTitle}
            </h2>
            <form onSubmit={submit} className="mt-3 flex flex-wrap items-start gap-3">
                <TextField
                    tone="dark"
                    label={copy.cep}
                    hideLabel
                    inputMode="numeric"
                    autoComplete="postal-code"
                    placeholder={copy.cepPlaceholder}
                    value={cep}
                    onChange={(e) => setCep(maskCep(e.target.value))}
                    error={error ?? undefined}
                    className="min-w-40 flex-1"
                />
                <Button type="submit" variant="secondary" tone="dark" loading={loading}>
                    {loading ? copy.calculating : copy.calculate}
                </Button>
            </form>
            <a
                href="https://buscacepinter.correios.com.br/app/endereco/index.php"
                target="_blank"
                rel="noreferrer"
                className="mt-2 inline-block text-sm text-moonlight/65 underline underline-offset-4"
            >
                {copy.noCep}
            </a>
            {quote && (
                <div aria-live="polite" className="mt-4">
                    <ul className="divide-y divide-moonlight/10">
                        {quote.options.map((option) => (
                            <li key={option.id} className="flex items-baseline justify-between gap-3 py-2.5">
                                <span>
                                    <span className="font-semibold">{option.service}</span>
                                    <span className="ml-2 text-sm text-moonlight/60">
                                        {option.carrier} · {copy.shippingDays(option.days)}
                                    </span>
                                </span>
                                <span className="font-semibold text-car tabular-nums">{money(option.priceCents)}</span>
                            </li>
                        ))}
                    </ul>
                    {quote.simulated && <p className="mt-2 text-xs text-moonlight/60">{copy.shippingSimulated}</p>}
                </div>
            )}
        </section>
    );
}
