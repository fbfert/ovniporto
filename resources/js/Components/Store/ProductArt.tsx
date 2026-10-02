import { SealArt } from '@/Components/Brand/Seal';

/** A product photo, or — while there is none — the sticker itself on a beam glow (never a fake photo). */
export function ProductArt({
    image,
    alt,
    sizes,
    className = '',
}: {
    image: string | null;
    alt: string;
    sizes: string;
    className?: string;
}) {
    if (image) {
        return <img src={image} alt={alt} loading="lazy" className={`h-full w-full object-cover ${className}`} />;
    }
    return (
        <div
            className={`flex h-full items-center justify-center bg-[radial-gradient(circle_at_50%_40%,rgb(84_201_51/0.22),transparent_60%)] ${className}`}
        >
            <div className="w-[52%] -rotate-6 drop-shadow-[0_14px_20px_rgb(6_17_33/0.6)] transition-transform duration-500 ease-snap [@media(hover:hover)]:group-hover/ticket:rotate-0">
                <SealArt title={alt} sizes={sizes} />
            </div>
        </div>
    );
}
