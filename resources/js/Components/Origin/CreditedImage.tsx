import manifest from '@/data/origin-images.json';
import { ManifestPicture, type ManifestEntry } from '@/Components/Ui/Picture';
import { t } from '@/i18n/pt-BR';
import type { ImageCredit } from '@/types/origin';

const entries: Record<string, ManifestEntry | undefined> = manifest;
const copy = t.origin.credit;

/**
 * A third-party image of the origin pages. The credit (author, licence with its link, source page)
 * is part of the component, so no photo can appear without it; location maps say they are maps.
 */
export function CreditedImage({
    credit,
    caption,
    sizes = '100vw',
    priority = false,
    tone = 'light',
    className = '',
    frameClassName = 'aspect-[4/3]',
}: {
    credit: ImageCredit;
    caption?: string;
    sizes?: string;
    priority?: boolean;
    tone?: 'light' | 'dark';
    className?: string;
    frameClassName?: string;
}) {
    const entry = entries[credit.slug];
    const isMap = credit.kind === 'location-map';
    const muted = tone === 'dark' ? 'text-moonlight/65' : 'text-night/60';
    const link = tone === 'dark' ? 'text-beam-glow' : 'text-horizon';

    return (
        <figure className={className}>
            <div className={`relative overflow-hidden rounded-[18px] bg-night-blue ${frameClassName}`}>
                {entry && (
                    <ManifestPicture
                        base="/origin"
                        slug={credit.slug}
                        entry={entry}
                        alt={credit.alt}
                        sizes={sizes}
                        priority={priority}
                        className="h-full w-full"
                        imgClassName={
                            isMap ? 'h-full w-full object-contain bg-moonlight' : 'h-full w-full object-cover'
                        }
                    />
                )}
                {isMap && (
                    <span className="absolute top-3 left-3 rounded-full bg-night/80 px-2.5 py-1 text-[0.72rem] font-semibold text-moonlight">
                        {copy.map}
                    </span>
                )}
            </div>
            <figcaption className={`mt-2.5 px-1 text-[0.8rem] leading-snug ${muted}`}>
                {caption && <span className="block text-[0.9rem] text-current">{caption}</span>}
                <span>
                    {isMap ? copy.mapBy : copy.photoBy} {credit.author},{' '}
                    {credit.licenseUrl ? (
                        <a
                            href={credit.licenseUrl}
                            target="_blank"
                            rel="noopener noreferrer license"
                            className={`underline underline-offset-2 ${link}`}
                        >
                            {credit.license}
                        </a>
                    ) : (
                        credit.license
                    )}
                    {' · '}
                    <a
                        href={credit.sourceUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className={`underline underline-offset-2 ${link}`}
                    >
                        {copy.source}
                    </a>
                </span>
            </figcaption>
        </figure>
    );
}
