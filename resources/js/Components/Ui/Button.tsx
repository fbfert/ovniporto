import { Link } from '@inertiajs/react';
import type { ButtonHTMLAttributes, ReactNode } from 'react';

type Variant = 'primary' | 'secondary' | 'ghost' | 'car';
type Size = 'sm' | 'md' | 'lg';
type Tone = 'light' | 'dark';

interface CommonProps {
    variant?: Variant;
    size?: Size;
    /** Background the button sits on; only affects secondary and ghost. */
    tone?: Tone;
    iconLeft?: ReactNode;
    iconRight?: ReactNode;
    loading?: boolean;
    className?: string;
    children: ReactNode;
}

type AsLink = CommonProps & { href: string; external?: boolean; type?: never; onClick?: () => void };
type AsButton = CommonProps & { href?: undefined } & Omit<
        ButtonHTMLAttributes<HTMLButtonElement>,
        'className' | 'children'
    >;

export type ButtonProps = AsLink | AsButton;

const base =
    'group/btn relative inline-flex min-h-11 items-center justify-center gap-2 rounded-full font-sans font-semibold tracking-[0.01em] whitespace-nowrap select-none ' +
    'transition-[transform,background-color,color,box-shadow,border-color] duration-200 ease-snap active:scale-[0.97] ' +
    'disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50';

const sizes: Record<Size, string> = {
    sm: 'h-11 px-4 text-sm',
    md: 'h-12 px-6 text-[0.95rem]',
    lg: 'h-14 px-8 text-base',
};

function variantClass(variant: Variant, tone: Tone): string {
    switch (variant) {
        case 'primary':
            return 'bg-beam text-night [@media(hover:hover)]:hover:scale-[1.02] [@media(hover:hover)]:hover:shadow-beam';
        case 'car':
            return 'bg-car text-night [@media(hover:hover)]:hover:scale-[1.02] [@media(hover:hover)]:hover:shadow-[0_8px_28px_-8px_rgb(252_184_2/0.6)]';
        case 'secondary':
            return tone === 'dark'
                ? 'border-[1.5px] border-moonlight/70 text-moonlight [@media(hover:hover)]:hover:border-moonlight [@media(hover:hover)]:hover:bg-moonlight/8'
                : 'border-[1.5px] border-night/70 text-night [@media(hover:hover)]:hover:border-night [@media(hover:hover)]:hover:bg-night/5';
        case 'ghost':
            return `px-1! ${tone === 'dark' ? 'text-moonlight' : 'text-night'}`;
    }
}

function Content({ variant, loading, iconLeft, iconRight, children }: CommonProps) {
    return (
        <>
            {loading ? (
                <span
                    aria-hidden
                    className="size-4 animate-spin rounded-full border-2 border-current border-r-transparent [animation-duration:600ms]"
                />
            ) : (
                iconLeft
            )}
            <span className={variant === 'ghost' ? 'relative' : undefined}>
                {children}
                {variant === 'ghost' && (
                    <span
                        aria-hidden
                        className="absolute -bottom-0.5 left-0 h-[1.5px] w-full origin-left scale-x-[0.35] bg-current transition-transform duration-300 ease-snap group-hover/btn:scale-x-100 group-focus-visible/btn:scale-x-100"
                    />
                )}
            </span>
            {iconRight && (
                <span className="transition-transform duration-200 ease-snap [@media(hover:hover)]:group-hover/btn:translate-x-0.5">
                    {iconRight}
                </span>
            )}
        </>
    );
}

export function Button(props: ButtonProps) {
    const { variant = 'primary', size = 'md', tone = 'light', className = '' } = props;
    const classes = `${base} ${sizes[size]} ${variantClass(variant, tone)} ${className}`;

    if (props.href !== undefined) {
        const { href, external, onClick } = props;
        if (external || /^(https?:|mailto:|tel:)/.test(href)) {
            return (
                <a
                    href={href}
                    className={classes}
                    onClick={onClick}
                    {...(href.startsWith('http') ? { target: '_blank', rel: 'noopener noreferrer' } : {})}
                >
                    <Content {...props} />
                </a>
            );
        }
        return (
            <Link href={href} className={classes} onClick={onClick} prefetch>
                <Content {...props} />
            </Link>
        );
    }

    /* eslint-disable @typescript-eslint/no-unused-vars */
    const {
        variant: _v,
        size: _s,
        tone: _t,
        iconLeft: _l,
        iconRight: _r,
        loading,
        className: _c,
        children: _ch,
        href: _h,
        ...rest
    } = props;
    /* eslint-enable @typescript-eslint/no-unused-vars */

    return (
        <button
            type="button"
            {...rest}
            className={classes}
            aria-busy={loading || undefined}
            disabled={rest.disabled || loading}
        >
            <Content {...props} />
        </button>
    );
}
