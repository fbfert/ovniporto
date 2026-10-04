import { useState } from 'react';
import { PostcardBack } from '@/Components/Postcard/PostcardBack';
import { t } from '@/i18n/pt-BR';

const copy = t.postcardPage;

/** Same file people download, served as WebP where the browser takes it (widths from `brand:postal`). */
function PostcardFront({ imageUrl }: { imageUrl: string }) {
    return (
        <picture className="block aspect-[3/2] overflow-hidden rounded-[3px] bg-night">
            <source
                type="image/webp"
                srcSet="/brand/postal-800.webp 800w, /brand/postal-1500.webp 1500w"
                sizes="(min-width: 1024px) 40rem, 92vw"
            />
            <img
                src={imageUrl}
                alt={copy.frontAlt}
                width={1500}
                height={1000}
                fetchPriority="high"
                decoding="async"
                className="h-full w-full object-cover"
            />
        </picture>
    );
}

const paper = 'rounded-[6px] bg-moonlight text-night shadow-polaroid';

/**
 * The postcard as an object with two sides that turns over. On a phone only the
 * front shows: the written side doesn't fit a 3:2 card at 390px, and stacking it
 * would push the share and download buttons a full screen down.
 */
export function FlipPostcard({ imageUrl }: { imageUrl: string }) {
    const [flipped, setFlipped] = useState(false);

    return (
        <div>
            <div className="sm:[perspective:1800px]">
                <div
                    className={`grid motion-reduce:transition-none sm:transition-transform sm:duration-[520ms] sm:ease-[var(--ease-glide)] sm:[transform-style:preserve-3d] ${
                        flipped ? 'sm:[transform:rotateY(180deg)]' : ''
                    }`}
                >
                    <div className={`${paper} rotate-[-1.2deg] p-2 sm:[backface-visibility:hidden] sm:[grid-area:1/1]`}>
                        <PostcardFront imageUrl={imageUrl} />
                    </div>
                    <div
                        className={`${paper} hidden rotate-[1.5deg] p-5 sm:flex sm:[transform:rotateY(180deg)] sm:items-center sm:p-8 sm:[backface-visibility:hidden] sm:[grid-area:1/1]`}
                    >
                        <div className="w-full">
                            <PostcardBack />
                        </div>
                    </div>
                </div>
            </div>
            <button
                type="button"
                onClick={() => setFlipped((side) => !side)}
                aria-pressed={flipped}
                className="mt-6 hidden min-h-11 items-center gap-2 rounded-full px-4 font-script text-2xl text-horizon hover:bg-horizon/10 sm:inline-flex"
            >
                <svg
                    aria-hidden
                    viewBox="0 0 24 24"
                    className="size-5"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                >
                    <path d="M4 12a8 8 0 0 1 14-5.3M20 12a8 8 0 0 1-14 5.3" strokeLinecap="round" />
                    <path d="M18 3v4h-4M6 21v-4h4" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
                {flipped ? copy.flipToFront : copy.flipToBack}
            </button>
        </div>
    );
}
