import {
    useId,
    useRef,
    type InputHTMLAttributes,
    type KeyboardEvent,
    type ReactNode,
    type SelectHTMLAttributes,
    type TextareaHTMLAttributes,
} from 'react';
import { ChevronDownIcon } from '@/Components/Icons';
import { t } from '@/i18n/pt-BR';

type Tone = 'light' | 'dark';

const surface: Record<Tone, string> = {
    dark: 'bg-night/60 text-moonlight placeholder:text-moonlight/50 border-moonlight/25 focus:border-beam',
    light: 'bg-moonlight text-night placeholder:text-night/60 border-night/25 focus:border-horizon',
};

const control =
    'w-full border-[1.5px] text-base transition-colors duration-200 ease-snap outline-none disabled:opacity-50';

/** Label, control, then the error and the counter, all wired to the control by id. */
function FieldShell({
    id,
    label,
    hideLabel,
    error,
    tone,
    counter,
    className = '',
    children,
}: {
    id: string;
    label: ReactNode;
    hideLabel?: boolean;
    error?: string;
    tone: Tone;
    counter?: ReactNode;
    className?: string;
    children: ReactNode;
}) {
    return (
        <div className={className}>
            <label htmlFor={id} className={hideLabel ? 'sr-only' : 'mb-1.5 block text-sm font-semibold'}>
                {label}
            </label>
            {children}
            {(error || counter) && (
                <div className="mt-2 flex items-start justify-between gap-4 px-5">
                    {error ? (
                        <p
                            id={`${id}-error`}
                            className={`text-sm font-medium ${tone === 'dark' ? 'text-car' : 'text-night'}`}
                        >
                            {error}
                        </p>
                    ) : (
                        <span />
                    )}
                    {counter}
                </div>
            )}
        </div>
    );
}

/**
 * "n/limit" under a field. Announced politely only past 90% of the limit, so a
 * screen reader isn't told the count on every keystroke.
 */
function Counter({ id, length, max, tone }: { id: string; length: number; max: number; tone: Tone }) {
    const near = length >= max * 0.9;
    const muted = tone === 'dark' ? 'text-moonlight/55' : 'text-night/60';
    const warn = tone === 'dark' ? 'text-car' : 'text-horizon';
    return (
        <p
            id={`${id}-count`}
            aria-live={near ? 'polite' : 'off'}
            className={`shrink-0 text-sm font-medium tabular-nums transition-colors duration-200 ${near ? warn : muted}`}
        >
            <span className="sr-only">{t.form.charactersUsed}: </span>
            {length}/{max}
        </p>
    );
}

function describedBy(id: string, error?: string, counted?: boolean) {
    return [error && `${id}-error`, counted && `${id}-count`].filter(Boolean).join(' ') || undefined;
}

function textLength(value: unknown) {
    return typeof value === 'string' ? value.length : 0;
}

type FieldProps = { label: string; error?: string; tone?: Tone; hideLabel?: boolean; showCount?: boolean };

export function TextField({
    label,
    error,
    tone = 'light',
    hideLabel = false,
    showCount = false,
    className = '',
    ...input
}: InputHTMLAttributes<HTMLInputElement> & FieldProps) {
    const id = useId();
    const counted = showCount && input.maxLength !== undefined;
    return (
        <FieldShell
            id={id}
            label={label}
            hideLabel={hideLabel}
            error={error}
            tone={tone}
            className={className}
            counter={counted && <Counter id={id} length={textLength(input.value)} max={input.maxLength!} tone={tone} />}
        >
            <input
                id={id}
                aria-invalid={error ? true : undefined}
                aria-describedby={describedBy(id, error, counted)}
                className={`${control} h-12 rounded-full px-5 ${surface[tone]} ${error ? 'border-car!' : ''}`}
                {...input}
            />
        </FieldShell>
    );
}

export function TextareaField({
    label,
    error,
    tone = 'light',
    hideLabel = false,
    showCount = false,
    className = '',
    rows = 5,
    ...textarea
}: TextareaHTMLAttributes<HTMLTextAreaElement> & FieldProps) {
    const id = useId();
    const counted = showCount && textarea.maxLength !== undefined;
    return (
        <FieldShell
            id={id}
            label={label}
            hideLabel={hideLabel}
            error={error}
            tone={tone}
            className={className}
            counter={
                counted && <Counter id={id} length={textLength(textarea.value)} max={textarea.maxLength!} tone={tone} />
            }
        >
            <textarea
                id={id}
                rows={rows}
                aria-invalid={error ? true : undefined}
                aria-describedby={describedBy(id, error, counted)}
                className={`${control} block min-h-28 resize-y rounded-[22px] px-5 py-3.5 leading-relaxed ${surface[tone]} ${error ? 'border-car!' : ''}`}
                {...textarea}
            />
        </FieldShell>
    );
}

/** Native select (best picker on phones), styled as a pill with our chevron. */
export function SelectField({
    label,
    error,
    tone = 'light',
    hideLabel = false,
    className = '',
    placeholder,
    options,
    ...select
}: SelectHTMLAttributes<HTMLSelectElement> &
    Omit<FieldProps, 'showCount'> & {
        placeholder?: string;
        options: ReadonlyArray<{ value: string; label: string }>;
    }) {
    const id = useId();
    return (
        <FieldShell id={id} label={label} hideLabel={hideLabel} error={error} tone={tone} className={className}>
            <div className="relative">
                <select
                    id={id}
                    aria-invalid={error ? true : undefined}
                    aria-describedby={describedBy(id, error)}
                    className={`${control} h-12 cursor-pointer appearance-none rounded-full pr-12 pl-5 ${surface[tone]} ${error ? 'border-car!' : ''}`}
                    {...select}
                >
                    {placeholder && (
                        <option value="" disabled>
                            {placeholder}
                        </option>
                    )}
                    {options.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
                <ChevronDownIcon
                    size="1.1rem"
                    className={`pointer-events-none absolute top-1/2 right-5 -translate-y-1/2 ${tone === 'dark' ? 'text-moonlight/70' : 'text-night/60'}`}
                />
            </div>
        </FieldShell>
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
                    aria-describedby={error ? `${id}-error` : undefined}
                    className={`peer mt-0.5 size-5 shrink-0 cursor-pointer appearance-none rounded-md border-[1.5px] transition-colors duration-150 ease-snap checked:border-beam checked:bg-beam ${
                        tone === 'dark' ? 'border-moonlight/50' : 'border-night/50'
                    } checked:bg-[url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23061121' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7.5'/%3E%3C/svg%3E")] bg-[length:14px] bg-center bg-no-repeat`}
                    {...input}
                />
                <span className={tone === 'dark' ? 'text-moonlight/85' : 'text-night/80'}>{label}</span>
            </label>
            {error && (
                <p
                    id={`${id}-error`}
                    className={`mt-1 pl-8 text-sm font-medium ${tone === 'dark' ? 'text-car' : 'text-night'}`}
                >
                    {error}
                </p>
            )}
        </div>
    );
}

export interface ChipOption {
    value: string;
    label: string;
    icon?: ReactNode;
}

type ChipGroupProps = {
    label: string;
    options: ReadonlyArray<ChipOption>;
    tone?: Tone;
    hideLabel?: boolean;
    error?: string;
    className?: string;
} & (
    | { multiple?: false; value: string | null; onChange: (value: string) => void }
    | { multiple: true; value: string[]; onChange: (value: string[]) => void }
);

/**
 * Choice as pills. Single mode is a radio group (one tab stop, arrows move the
 * selection); multiple mode is a row of toggle buttons (aria-pressed).
 */
export function ChipGroup(props: ChipGroupProps) {
    const { label, options, tone = 'light', hideLabel = false, error, className = '' } = props;
    const id = useId();
    const refs = useRef<Array<HTMLButtonElement | null>>([]);

    const isSelected = (value: string) => (props.multiple ? props.value.includes(value) : props.value === value);

    const choose = (value: string) => {
        if (props.multiple) {
            props.onChange(isSelected(value) ? props.value.filter((v) => v !== value) : [...props.value, value]);
        } else {
            props.onChange(value);
        }
    };

    const onKeyDown = (event: KeyboardEvent<HTMLButtonElement>, index: number) => {
        if (props.multiple) return;
        const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[event.key];
        if (!step) return;
        event.preventDefault();
        const next = (index + step + options.length) % options.length;
        const option = options[next];
        if (!option) return;
        choose(option.value);
        refs.current[next]?.focus();
    };

    const selectedIndex = props.multiple ? -1 : options.findIndex((o) => o.value === props.value);
    const pill = {
        dark: 'border-moonlight/30 text-moonlight/85 aria-[checked=true]:border-beam aria-[checked=true]:bg-beam aria-[checked=true]:text-night aria-[pressed=true]:border-beam aria-[pressed=true]:bg-beam aria-[pressed=true]:text-night [@media(hover:hover)]:hover:border-moonlight/60',
        light: 'border-night/25 text-night/80 aria-[checked=true]:border-night aria-[checked=true]:bg-night aria-[checked=true]:text-moonlight aria-[pressed=true]:border-night aria-[pressed=true]:bg-night aria-[pressed=true]:text-moonlight [@media(hover:hover)]:hover:border-night/50',
    }[tone];

    return (
        <div className={className}>
            <p id={`${id}-label`} className={hideLabel ? 'sr-only' : 'mb-2 text-sm font-semibold'}>
                {label}
            </p>
            <div
                role={props.multiple ? 'group' : 'radiogroup'}
                aria-labelledby={`${id}-label`}
                aria-describedby={error ? `${id}-error` : undefined}
                aria-invalid={error && !props.multiple ? true : undefined}
                className="flex flex-wrap gap-2"
            >
                {options.map((option, index) => {
                    const selected = isSelected(option.value);
                    const tabbable = props.multiple || index === (selectedIndex === -1 ? 0 : selectedIndex);
                    return (
                        <button
                            key={option.value}
                            ref={(el) => {
                                refs.current[index] = el;
                            }}
                            type="button"
                            role={props.multiple ? undefined : 'radio'}
                            aria-checked={props.multiple ? undefined : selected}
                            aria-pressed={props.multiple ? selected : undefined}
                            tabIndex={tabbable ? 0 : -1}
                            onClick={() => choose(option.value)}
                            onKeyDown={(event) => onKeyDown(event, index)}
                            className={`inline-flex min-h-11 items-center gap-2 rounded-full border-[1.5px] px-4 text-[0.95rem] font-semibold transition-[transform,background-color,color,border-color] duration-150 ease-snap active:scale-[0.97] ${pill}`}
                        >
                            {option.icon}
                            {option.label}
                        </button>
                    );
                })}
            </div>
            {error && (
                <p
                    id={`${id}-error`}
                    className={`mt-2 pl-1 text-sm font-medium ${tone === 'dark' ? 'text-car' : 'text-night'}`}
                >
                    {error}
                </p>
            )}
        </div>
    );
}
