type Size = 'sm' | 'md' | 'lg';

const sizes: Record<Size, string> = {
    sm: 'size-12',
    md: 'size-40',
    lg: 'size-[clamp(13rem,9rem+18vw,22rem)]',
};

/** Rendered width per size, for picking the right file. */
const imageSizes: Record<Size, string> = {
    sm: '3rem',
    md: '10rem',
    lg: 'clamp(13rem, 9rem + 18vw, 22rem)',
};

const SEAL_WIDTHS = [128, 256, 512, 768];

function sealSrcSet(format: 'avif' | 'webp') {
    return SEAL_WIDTHS.map((w) => `/brand/seal-${w}.${format} ${w}w`).join(', ');
}

/**
 * The printed sticker, cut out of its artwork by `php artisan brand:seal`
 * (public/brand). `sizes` should match the rendered width so the header gets
 * the 128px file and the cover the 768px one.
 */
export function SealArt({
    title = 'Selo OVNIPORTO · Lages SC',
    sizes = '22rem',
    priority = false,
}: {
    title?: string;
    sizes?: string;
    priority?: boolean;
}) {
    return (
        <picture className="block h-full w-full">
            <source type="image/avif" srcSet={sealSrcSet('avif')} sizes={sizes} />
            <source type="image/webp" srcSet={sealSrcSet('webp')} sizes={sizes} />
            <img
                src="/brand/seal.png"
                alt={title}
                width={512}
                height={512}
                loading={priority ? 'eager' : 'lazy'}
                fetchPriority={priority ? 'high' : 'auto'}
                decoding="async"
                className="h-full w-full object-contain"
            />
        </picture>
    );
}

export function Seal({
    size = 'md',
    glow = false,
    className = '',
}: {
    size?: Size;
    glow?: boolean;
    className?: string;
}) {
    return (
        <span className={`relative inline-block ${sizes[size]} ${className}`}>
            {glow && (
                <span
                    aria-hidden
                    className="absolute -inset-[9%] animate-seal-glow rounded-full bg-[radial-gradient(closest-side,rgb(84_201_51/0.55),rgb(84_201_51/0.18)_55%,transparent_72%)] motion-reduce:animate-none"
                />
            )}
            <span className="relative block h-full w-full rounded-full shadow-[0_18px_50px_-18px_rgb(6_17_33/0.8)]">
                <SealArt sizes={imageSizes[size]} priority={size === 'lg'} />
            </span>
        </span>
    );
}
