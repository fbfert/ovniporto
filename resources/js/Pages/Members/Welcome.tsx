import { Link, useForm } from '@inertiajs/react';
import { useEffect, useId, useState, type FormEvent, type ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField, TextField } from '@/Components/Ui/Fields';
import { NightSkyArt, Polaroid } from '@/Components/Ui/Polaroid';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.members;

type Availability = { state: 'idle' | 'checking' } | { state: 'free' } | { state: 'taken'; message: string };

interface CheckResult {
    value: string;
    available: boolean;
    message: string | null;
}

/**
 * Debounced live check against /apelido-disponivel while the nickname is typed.
 * The effect only stores the server's answer; the state shown is derived from
 * whether that answer belongs to the current text.
 */
function useNicknameAvailability(nickname: string): Availability {
    const value = nickname.trim().toLowerCase();
    const [result, setResult] = useState<CheckResult | null>(null);

    useEffect(() => {
        if (value.length < 3) return;
        const controller = new AbortController();
        const timer = setTimeout(async () => {
            try {
                const response = await fetch(`/apelido-disponivel?apelido=${encodeURIComponent(value)}`, {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                const body = (await response.json()) as { available: boolean; message: string | null };
                setResult({ value, available: body.available, message: body.message });
            } catch {
                // aborted by a newer keystroke, or offline: the server validates again on submit
            }
        }, 350);
        return () => {
            clearTimeout(timer);
            controller.abort();
        };
    }, [value]);

    if (value.length < 3) return { state: 'idle' };
    if (result?.value !== value) return { state: 'checking' };
    return result.available ? { state: 'free' } : { state: 'taken', message: result.message ?? '' };
}

export default function Welcome({ firstName, suggestion }: { firstName: string; suggestion: string }) {
    const form = useForm({ nickname: suggestion, city: '', terms: false as boolean });
    const availability = useNicknameAvailability(form.data.nickname);
    const nicknameId = useId();
    const statusId = `${nicknameId}-status`;
    const nicknameError = form.errors.nickname ?? (availability.state === 'taken' ? availability.message : undefined);
    const preview = form.data.nickname.trim().toLowerCase() || suggestion;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (availability.state === 'taken') return;
        form.post('/boas-vindas');
    };

    return (
        <>
            <SeoHead />
            <Section tone="dark" pattern="stars" className="min-h-svh" innerClassName="pt-32! sm:pt-36!">
                <div className="grid items-center gap-14 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:gap-20">
                    <form onSubmit={submit} noValidate>
                        <Eyebrow tone="dark">{copy.welcomeEyebrow(firstName)}</Eyebrow>
                        <Display as="h1" className="mt-3 text-[clamp(1.9rem,1rem+3.6vw,3.8rem)]! text-balance">
                            {copy.welcomeTitle}
                        </Display>
                        <p className="mt-5 max-w-[46ch] text-lg leading-relaxed text-moonlight/80">
                            {copy.welcomeLead}
                        </p>

                        <div className="mt-10">
                            <label htmlFor={nicknameId} className="text-sm font-semibold">
                                {copy.nicknameLabel}
                            </label>
                            <div className="relative mt-2">
                                <span
                                    aria-hidden
                                    className="pointer-events-none absolute top-1/2 left-6 -translate-y-1/2 font-display text-2xl text-moonlight/40"
                                >
                                    @
                                </span>
                                <input
                                    id={nicknameId}
                                    name="nickname"
                                    autoComplete="off"
                                    autoCapitalize="none"
                                    spellCheck={false}
                                    maxLength={20}
                                    value={form.data.nickname}
                                    onChange={(event) => form.setData('nickname', event.target.value)}
                                    aria-invalid={nicknameError ? true : undefined}
                                    aria-describedby={statusId}
                                    className={`h-16 w-full rounded-full border-[1.5px] bg-night/60 pr-28 pl-14 font-display text-[clamp(1.2rem,1rem+1vw,1.6rem)] font-bold tracking-[0.02em] text-moonlight transition-colors duration-200 ease-snap outline-none focus:border-beam ${nicknameError ? 'border-car' : 'border-moonlight/25'}`}
                                />
                                <span
                                    aria-hidden
                                    className={`absolute top-1/2 right-6 -translate-y-1/2 text-sm font-semibold ${availability.state === 'free' ? 'text-beam' : 'text-moonlight/50'}`}
                                >
                                    {availability.state === 'checking' && copy.nicknameChecking}
                                    {availability.state === 'free' && copy.nicknameFree}
                                </span>
                            </div>
                            <p
                                id={statusId}
                                aria-live="polite"
                                className={`mt-2 px-6 text-sm ${nicknameError ? 'font-medium text-car' : 'text-moonlight/60'}`}
                            >
                                {nicknameError ?? copy.nicknameHint}
                            </p>
                        </div>

                        <TextField
                            tone="dark"
                            label={copy.cityLabel}
                            placeholder={copy.cityPlaceholder}
                            value={form.data.city}
                            onChange={(event) => form.setData('city', event.target.value)}
                            error={form.errors.city}
                            autoComplete="address-level2"
                            className="mt-6 max-w-sm"
                        />

                        <div className="mt-6">
                            <CheckboxField
                                tone="dark"
                                checked={form.data.terms}
                                onChange={(event) => form.setData('terms', event.target.checked)}
                                error={form.errors.terms}
                                label={
                                    <>
                                        {copy.termsLabel}{' '}
                                        <Link href="/termos" className="underline underline-offset-4">
                                            {copy.terms}
                                        </Link>
                                        {' · '}
                                        <Link href="/privacidade" className="underline underline-offset-4">
                                            {copy.privacy}
                                        </Link>
                                    </>
                                }
                            />
                        </div>

                        <div className="mt-8">
                            <Button
                                type="submit"
                                size="lg"
                                loading={form.processing}
                                disabled={availability.state === 'taken'}
                            >
                                {copy.enter}
                            </Button>
                        </div>
                    </form>

                    <figure aria-label={copy.previewLabel} className="mx-auto w-full max-w-xs lg:mx-0">
                        <Polaroid
                            rotate={4}
                            tape="corner"
                            art={<NightSkyArt label={copy.sightingTypes.light} seed={preview.length + 3} />}
                            caption={copy.previewCaption(`@${preview}`)}
                        />
                        <figcaption className="mt-6 text-center font-script text-xl text-beam-glow">
                            {copy.previewLabel}
                        </figcaption>
                    </figure>
                </div>
            </Section>
        </>
    );
}

Welcome.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
