import type { ReactNode } from 'react';
import { Button } from '@/Components/Ui/Button';
import type { ListRequestStatus } from '@/hooks/useListRequest';
import { t } from '@/i18n/pt-BR';

const copy = t.listStatus;

type Tone = 'light' | 'dark';

const skeletonTone: Record<Tone, string> = {
    light: 'bg-night/[0.07]',
    dark: 'bg-moonlight/[0.08]',
};

/**
 * The four states every list has: loading (placeholders in the section's tone), error
 * (what happened and a retry), empty (the list's own invitation) and the items.
 */
export function ListState({
    status,
    isEmpty,
    empty,
    onRetry,
    tone = 'light',
    placeholders = 4,
    placeholderClassName = 'aspect-[4/5] rounded-[18px]',
    className = 'mt-12 grid grid-cols-2 gap-5 sm:gap-8 lg:grid-cols-4',
    children,
}: {
    status: ListRequestStatus;
    isEmpty: boolean;
    empty: ReactNode;
    onRetry: () => void;
    tone?: Tone;
    placeholders?: number;
    placeholderClassName?: string;
    className?: string;
    children: ReactNode;
}) {
    if (status === 'loading') {
        return (
            <div aria-busy="true" className={className}>
                <span className="sr-only" role="status">
                    {copy.loading}
                </span>
                {Array.from({ length: placeholders }, (_, i) => (
                    <div
                        key={i}
                        aria-hidden
                        className={`${skeletonTone[tone]} ${placeholderClassName} motion-safe:animate-pulse`}
                    />
                ))}
            </div>
        );
    }

    if (status === 'error') {
        return (
            <div
                role="alert"
                className={`mt-10 rounded-[22px] border-2 border-dashed p-8 text-center ${
                    tone === 'dark' ? 'border-moonlight/20' : 'border-night/20'
                }`}
            >
                <p className={`font-script text-2xl ${tone === 'dark' ? 'text-beam-glow' : 'text-horizon'}`}>
                    {copy.error}
                </p>
                <div className="mt-5">
                    <Button variant="secondary" tone={tone} onClick={onRetry}>
                        {copy.retry}
                    </Button>
                </div>
            </div>
        );
    }

    return <>{isEmpty ? empty : children}</>;
}
