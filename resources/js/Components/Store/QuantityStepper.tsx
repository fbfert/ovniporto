import { MinusIcon, PlusIcon } from '@/Components/Icons';
import { t } from '@/i18n/pt-BR';

/** − 3 + with 44px targets; the number itself is announced as the field's value. */
export function QuantityStepper({
    value,
    max,
    onChange,
    disabled = false,
    tone = 'light',
    label = t.storePage.quantity,
}: {
    value: number;
    max: number;
    onChange: (value: number) => void;
    disabled?: boolean;
    tone?: 'light' | 'dark';
    label?: string;
}) {
    const ring = tone === 'dark' ? 'ring-moonlight/30' : 'ring-night/25';
    const button = 'inline-flex size-11 press items-center justify-center rounded-full disabled:opacity-30';
    return (
        <div
            role="group"
            aria-label={label}
            className={`inline-flex items-center gap-1 rounded-full p-0.5 ring-1 ${ring}`}
        >
            <button
                type="button"
                className={button}
                onClick={() => onChange(value - 1)}
                disabled={disabled || value <= 1}
                aria-label={t.storePage.decrease}
            >
                <MinusIcon size="1.1rem" />
            </button>
            <output aria-live="polite" className="min-w-8 text-center font-semibold tabular-nums">
                {value}
            </output>
            <button
                type="button"
                className={button}
                onClick={() => onChange(value + 1)}
                disabled={disabled || value >= max}
                aria-label={t.storePage.increase}
            >
                <PlusIcon size="1.1rem" />
            </button>
        </div>
    );
}
