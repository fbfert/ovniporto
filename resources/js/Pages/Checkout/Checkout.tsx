import { Link, useForm, usePage } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { ProductArt } from '@/Components/Store/ProductArt';
import { Button } from '@/Components/Ui/Button';
import { TextField } from '@/Components/Ui/Fields';
import { Section } from '@/Components/Ui/Section';
import { Display } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { HttpError, postJson } from '@/lib/http';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { SharedProps } from '@/types';

const copy = t.checkout;

type Step = 1 | 2 | 3;

interface Option {
    id: string;
    carrier: string;
    service: string;
    priceCents: number;
    days: number;
}

interface Props {
    customer: { name: string; email: string };
    signedIn: boolean;
    shippingAvailable: boolean;
}

interface CheckoutForm {
    name: string;
    email: string;
    phone: string;
    cpf: string;
    pickup: boolean;
    address: {
        cep: string;
        street: string;
        number: string;
        complement: string;
        district: string;
        city: string;
        state: string;
    };
    shippingOptionId: string;
}

const STEP_OF: Record<string, Step> = { name: 1, email: 1, phone: 1, cpf: 1, shippingOptionId: 2 };
const stepOf = (field: string): Step => STEP_OF[field] ?? (field.startsWith('address') ? 2 : 3);

const maskCpf = (value: string) =>
    value
        .replace(/\D/g, '')
        .slice(0, 11)
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
const maskCep = (value: string) => {
    const digits = value.replace(/\D/g, '').slice(0, 8);
    return digits.length > 5 ? `${digits.slice(0, 5)}-${digits.slice(5)}` : digits;
};

function Progress({ step }: { step: Step }) {
    return (
        <ol className="mt-6 grid grid-cols-3 gap-2" aria-label={copy.stepOf(step)}>
            {copy.steps.map((label, i) => (
                <li key={label} aria-current={i + 1 === step ? 'step' : undefined}>
                    <span className={`block h-1.5 rounded-full ${i + 1 <= step ? 'bg-beam' : 'bg-moonlight/15'}`} />
                    <span
                        className={`mt-2 block text-sm font-semibold ${i + 1 === step ? 'text-moonlight' : 'text-moonlight/55'}`}
                    >
                        {String(i + 1).padStart(2, '0')} · {label}
                    </span>
                </li>
            ))}
        </ol>
    );
}

export default function Checkout({ customer, signedIn, shippingAvailable }: Props) {
    const { cart } = usePage<SharedProps>().props;
    const [step, setStep] = useState<Step>(1);
    const [options, setOptions] = useState<Option[]>([]);
    const [deliveryMessage, setDeliveryMessage] = useState<string | null>(shippingAvailable ? null : copy.shippingOff);
    const [looking, setLooking] = useState(false);
    const [cepError, setCepError] = useState<string | null>(null);
    const form = useForm<CheckoutForm>({
        name: customer.name,
        email: customer.email,
        phone: '',
        cpf: '',
        pickup: !shippingAvailable,
        address: { cep: '', street: '', number: '', complement: '', district: '', city: '', state: '' },
        shippingOptionId: '',
    });
    const { data } = form;
    const chosen = data.pickup ? null : (options.find((o) => o.id === data.shippingOptionId) ?? null);
    const shippingCents = chosen?.priceCents ?? 0;

    const sendBack = (errors: Record<string, string>) => {
        const first = Math.min(...Object.keys(errors).map(stepOf)) as Step;
        setStep(first);
    };

    const identify = (event: FormEvent) => {
        event.preventDefault();
        form.post('/checkout/identificacao', {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => setStep(2),
        });
    };

    const lookup = async () => {
        setLooking(true);
        setCepError(null);
        try {
            const result = await postJson<{
                cep: string;
                address: Omit<CheckoutForm['address'], 'cep' | 'number' | 'complement'> | null;
                options: Option[];
                message: string | null;
            }>('/checkout/entrega', { cep: data.address.cep });
            setOptions(result.options);
            setDeliveryMessage(result.message);
            form.setData((current) => ({
                ...current,
                address: { ...current.address, cep: result.cep, ...(result.address ?? {}) },
                shippingOptionId: result.options[0]?.id ?? '',
                pickup: result.options.length === 0 ? true : current.pickup,
            }));
        } catch (error) {
            setCepError(error instanceof HttpError && error.status === 422 ? copy.cepInvalid : copy.cepUnreachable);
        } finally {
            setLooking(false);
        }
    };

    const toReview = (event: FormEvent) => {
        event.preventDefault();
        if (!data.pickup && (!data.address.number.trim() || !chosen)) {
            form.setError(
                !chosen ? 'shippingOptionId' : 'address.number',
                !chosen ? copy.chooseDelivery : copy.numberMissing,
            );
            return;
        }
        form.clearErrors();
        setStep(3);
    };

    const place = () => {
        form.transform((current) =>
            current.pickup ? { ...current, address: undefined, shippingOptionId: undefined } : current,
        );
        form.post('/checkout', { onError: sendBack });
    };

    const address = (
        key: keyof CheckoutForm['address'],
        label: string,
        extra: { className?: string; maxLength?: number } = {},
    ) => (
        <TextField
            tone="dark"
            label={label}
            value={data.address[key]}
            onChange={(e) => form.setData('address', { ...data.address, [key]: e.target.value })}
            error={form.errors[`address.${key}` as keyof typeof form.errors]}
            {...extra}
        />
    );

    return (
        <>
            <SeoHead />
            <Section tone="dark" pattern="stars" innerClassName="pt-32! sm:pt-36!">
                <div className="mx-auto max-w-2xl">
                    <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                        {copy.title}
                    </Display>
                    <Progress step={step} />

                    {step === 1 && (
                        <form onSubmit={identify} className="mt-10 space-y-5">
                            {signedIn ? (
                                <p className="rounded-2xl bg-night-blue px-4 py-3 text-sm text-moonlight/80">
                                    {copy.signedInAs}
                                </p>
                            ) : (
                                <p className="rounded-2xl bg-night-blue px-4 py-3 text-sm text-moonlight/80">
                                    {copy.guestHint}{' '}
                                    <Link
                                        href="/entrar"
                                        className="font-semibold text-beam-glow underline underline-offset-4"
                                    >
                                        {copy.signIn}
                                    </Link>
                                </p>
                            )}
                            <TextField
                                tone="dark"
                                label={copy.name}
                                autoComplete="name"
                                value={data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                                error={form.errors.name}
                            />
                            <TextField
                                tone="dark"
                                type="email"
                                label={copy.email}
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => form.setData('email', e.target.value)}
                                error={form.errors.email}
                            />
                            <div>
                                <TextField
                                    tone="dark"
                                    type="tel"
                                    label={copy.phone}
                                    autoComplete="tel"
                                    value={data.phone}
                                    onChange={(e) => form.setData('phone', e.target.value)}
                                    error={form.errors.phone}
                                />
                                <p className="mt-1.5 text-sm text-moonlight/60">{copy.phoneHint}</p>
                            </div>
                            <div>
                                <TextField
                                    tone="dark"
                                    label={copy.cpf}
                                    inputMode="numeric"
                                    value={data.cpf}
                                    onChange={(e) => form.setData('cpf', maskCpf(e.target.value))}
                                    error={form.errors.cpf}
                                />
                                <p className="mt-1.5 text-sm text-moonlight/60">{copy.cpfWhy}</p>
                            </div>
                            <Button type="submit" size="lg" loading={form.processing}>
                                {copy.next}
                            </Button>
                        </form>
                    )}

                    {step === 2 && (
                        <form onSubmit={toReview} className="mt-10 space-y-6">
                            {shippingAvailable && (
                                <>
                                    <div className="flex items-start gap-3">
                                        <TextField
                                            tone="dark"
                                            label={copy.cep}
                                            inputMode="numeric"
                                            autoComplete="postal-code"
                                            value={data.address.cep}
                                            onChange={(e) =>
                                                form.setData('address', {
                                                    ...data.address,
                                                    cep: maskCep(e.target.value),
                                                })
                                            }
                                            error={cepError ?? form.errors['address.cep' as keyof typeof form.errors]}
                                            className="flex-1"
                                        />
                                        <Button
                                            variant="secondary"
                                            tone="dark"
                                            className="mt-7"
                                            onClick={lookup}
                                            loading={looking}
                                            disabled={data.address.cep.replace(/\D/g, '').length !== 8}
                                        >
                                            {copy.findCep}
                                        </Button>
                                    </div>
                                    {data.address.city && (
                                        <div className="grid gap-4 sm:grid-cols-[minmax(0,1fr)_8rem]">
                                            {address('street', copy.street)}
                                            {address('number', copy.number, { maxLength: 20 })}
                                            {address('complement', `${copy.complement} ${t.panel.content.optional}`, {
                                                className: 'sm:col-span-2',
                                            })}
                                            {address('district', copy.district)}
                                            {address('state', copy.state, { maxLength: 2 })}
                                            {address('city', copy.city, { className: 'sm:col-span-2' })}
                                        </div>
                                    )}
                                </>
                            )}

                            <fieldset>
                                <legend className="mb-3 font-display text-sm font-bold tracking-[0.06em] uppercase">
                                    {copy.howToReceive}
                                </legend>
                                {looking && <p className="text-sm text-moonlight/60">{copy.quoting}</p>}
                                {deliveryMessage && (
                                    <p className="mb-3 rounded-xl bg-night-blue px-4 py-3 text-sm text-moonlight/80">
                                        {deliveryMessage}
                                    </p>
                                )}
                                <div className="space-y-2">
                                    {options.map((option) => (
                                        <label
                                            key={option.id}
                                            className="flex min-h-14 cursor-pointer items-center justify-between gap-3 rounded-2xl px-4 py-3 ring-1 ring-moonlight/20 has-checked:ring-2 has-checked:ring-beam"
                                        >
                                            <span className="flex items-center gap-3">
                                                <input
                                                    type="radio"
                                                    name="shipping"
                                                    className="size-5 accent-[var(--color-beam)]"
                                                    checked={!data.pickup && data.shippingOptionId === option.id}
                                                    onChange={() =>
                                                        form.setData((d) => ({
                                                            ...d,
                                                            pickup: false,
                                                            shippingOptionId: option.id,
                                                        }))
                                                    }
                                                />
                                                <span>
                                                    <span className="font-semibold">{option.service}</span>
                                                    <span className="ml-2 text-sm text-moonlight/60">
                                                        {option.carrier} · {copy.days(option.days)}
                                                    </span>
                                                </span>
                                            </span>
                                            <span className="font-semibold text-car tabular-nums">
                                                {money(option.priceCents)}
                                            </span>
                                        </label>
                                    ))}
                                    <label className="flex min-h-14 cursor-pointer items-center justify-between gap-3 rounded-2xl px-4 py-3 ring-1 ring-moonlight/20 has-checked:ring-2 has-checked:ring-beam">
                                        <span className="flex items-center gap-3">
                                            <input
                                                type="radio"
                                                name="shipping"
                                                className="size-5 accent-[var(--color-beam)]"
                                                checked={data.pickup}
                                                onChange={() => form.setData('pickup', true)}
                                            />
                                            <span>
                                                <span className="font-semibold">{copy.pickup}</span>
                                                <span className="block text-sm text-moonlight/60">
                                                    {copy.pickupLead}
                                                </span>
                                            </span>
                                        </span>
                                        <span className="font-semibold text-beam-glow">{copy.free}</span>
                                    </label>
                                </div>
                                {form.errors.shippingOptionId && (
                                    <p className="mt-2 text-sm font-semibold text-car">
                                        {form.errors.shippingOptionId}
                                    </p>
                                )}
                            </fieldset>

                            <div className="flex flex-wrap gap-3">
                                <Button variant="secondary" tone="dark" size="lg" onClick={() => setStep(1)}>
                                    {copy.back}
                                </Button>
                                <Button type="submit" size="lg">
                                    {copy.next}
                                </Button>
                            </div>
                        </form>
                    )}

                    {step === 3 && (
                        <div className="mt-10 space-y-8">
                            <section aria-labelledby="itens" className="rounded-[22px] bg-night-blue p-5">
                                <h2 id="itens" className="font-display text-sm font-bold tracking-[0.06em] uppercase">
                                    {copy.review}
                                </h2>
                                <ul className="mt-3 divide-y divide-moonlight/10">
                                    {cart.items.map((item) => (
                                        <li
                                            key={item.variantId}
                                            className="grid grid-cols-[3.5rem_minmax(0,1fr)_auto] items-center gap-3 py-3"
                                        >
                                            <span className="block aspect-square overflow-hidden rounded-xl bg-night">
                                                <ProductArt
                                                    image={item.image}
                                                    alt={item.imageAlt ?? item.productName}
                                                    sizes="4rem"
                                                />
                                            </span>
                                            <span>
                                                <span className="font-semibold">
                                                    {item.quantity} × {item.productName}
                                                </span>
                                                <span className="block text-sm text-moonlight/60">
                                                    {item.variantName}
                                                    {item.madeToOrder && ` · ${copy.madeToOrder(item.productionDays)}`}
                                                </span>
                                            </span>
                                            <span className="tabular-nums">{money(item.lineCents)}</span>
                                        </li>
                                    ))}
                                </ul>
                                <dl className="mt-4 space-y-1 border-t-2 border-dashed border-moonlight/15 pt-4">
                                    <div className="flex justify-between">
                                        <dt className="text-moonlight/70">{copy.subtotal}</dt>
                                        <dd className="tabular-nums">{money(cart.subtotalCents)}</dd>
                                    </div>
                                    <div className="flex justify-between">
                                        <dt className="text-moonlight/70">{copy.shipping}</dt>
                                        <dd className="tabular-nums">
                                            {data.pickup ? copy.free : money(shippingCents)}
                                        </dd>
                                    </div>
                                    <div className="flex items-baseline justify-between pt-2">
                                        <dt className="font-semibold">{copy.total}</dt>
                                        <dd className="font-display text-2xl font-extrabold text-car tabular-nums">
                                            {money(cart.subtotalCents + shippingCents)}
                                        </dd>
                                    </div>
                                </dl>
                            </section>

                            <section aria-labelledby="entrega" className="rounded-[22px] bg-night-blue p-5">
                                <h2 id="entrega" className="font-display text-sm font-bold tracking-[0.06em] uppercase">
                                    {copy.deliverTo}
                                </h2>
                                <p className="mt-2 text-moonlight/85">
                                    {data.pickup
                                        ? copy.pickupLead
                                        : `${data.address.street}, ${data.address.number}${data.address.complement ? ` · ${data.address.complement}` : ''} · ${data.address.district} · ${data.address.city}/${data.address.state} · ${data.address.cep}`}
                                </p>
                                <p className="mt-1 text-sm text-moonlight/60">
                                    {data.name} · {data.email} · {data.phone}
                                </p>
                            </section>

                            {Object.values(form.errors).length > 0 && (
                                <p role="alert" className="rounded-xl bg-car px-4 py-3 font-semibold text-night">
                                    {Object.values(form.errors)[0]}
                                </p>
                            )}
                            <p className="text-sm text-moonlight/60">{copy.placeHint}</p>
                            <div className="flex flex-wrap gap-3">
                                <Button variant="secondary" tone="dark" size="lg" onClick={() => setStep(2)}>
                                    {copy.back}
                                </Button>
                                <Button variant="car" size="lg" onClick={place} loading={form.processing}>
                                    {copy.place}
                                </Button>
                            </div>
                        </div>
                    )}
                </div>
            </Section>
        </>
    );
}

Checkout.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
