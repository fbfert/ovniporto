import { useId, type InputHTMLAttributes, type ReactNode } from 'react';

type Tone = 'light' | 'dark';

export function TextField({
    label,
    error,
    tone = 'light',
    hideLabel = false,
    className = '',
    ...input
}: InputHTMLAttributes<HTMLInputElement> & { label: string; error?: string; tone?: Tone; hideLabel?: boolean }) {
    const id = useId();
    const errorId = `${id}-error`;
    const field =
        tone === 'dark'
            ? 'bg-night/60 text-moonlight placeholder:text-moonlight/45 border-moonlight/25 focus:border-beam'
            : 'bg-moonlight text-night placeholder:text-night/40 border-night/25 focus:border-horizon';
    return (
        <div className={className}>
            <label htmlFor={id} className={hideLabel ? 'sr-only' : 'mb-1.5 block text-sm font-semibold'}>
                {label}
            </label>
            <input
                id={id}
                aria-invalid={error ? true : undefined}
                aria-describedby={error ? errorId : undefined}
                className={`h-12 w-full rounded-full border-[1.5px] px-5 text-base transition-colors duration-200 ease-snap outline-none ${field} ${error ? 'border-car!' : ''}`}
                {...input}
            />
            {error && (
                <p
                    id={errorId}
                    className={`mt-2 pl-5 text-sm font-medium ${tone === 'dark' ? 'text-car' : 'text-night'}`}
                >
                    {error}
                </p>
            )}
        </div>
    );
}

export function CheckboxField({
    label,
    error,
    tone = 'light',
    ...input
}: InputHTMLAttributes<HTMLInputElement> & { label: ReactNode; error?: string; tone?: Tone }) {
    const id = useId();
    return (
        <div>
            <label htmlFor={id} className="flex min-h-11 cursor-pointer items-start gap-3 py-1 text-sm leading-snug">
                <input
                    id={id}
                    type="checkbox"
                    aria-invalid={error ? true : undefined}
                    className={`peer mt-0.5 size-5 shrink-0 cursor-pointer appearance-none rounded-md border-[1.5px] transition-colors duration-150 ease-snap checked:border-beam checked:bg-beam ${
                        tone === 'dark' ? 'border-moonlight/50' : 'border-night/50'
                    } checked:bg-[url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23061121' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7.5'/%3E%3C/svg%3E")] bg-[length:14px] bg-center bg-no-repeat`}
                    {...input}
                />
                <span className={tone === 'dark' ? 'text-moonlight/85' : 'text-night/80'}>{label}</span>
            </label>
            {error && (
                <p className={`mt-1 pl-8 text-sm font-medium ${tone === 'dark' ? 'text-car' : 'text-night'}`}>
                    {error}
                </p>
            )}
        </div>
    );
}
