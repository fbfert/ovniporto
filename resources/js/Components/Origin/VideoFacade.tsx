import { useState } from 'react';
import { SaucerShape } from '@/Components/Scene/Art';
import { t } from '@/i18n/pt-BR';
import type { CachiVideo } from '@/types/origin';

const copy = t.origin.video;

/**
 * A video of the dossier. YouTube videos show a local poster and load the no-cookie player only
 * after "Assistir" (nothing reaches YouTube before that). Other platforms stay as a link card.
 */
export function VideoFacade({ video }: { video: CachiVideo }) {
    const [playing, setPlaying] = useState(false);
    const platform = copy.platforms[video.platform];

    if (!video.youtubeId) {
        return (
            <a
                href={video.url}
                target="_blank"
                rel="noopener noreferrer"
                className="group flex h-full flex-col rounded-[18px] bg-night-blue p-6 ring-1 ring-moonlight/10 transition-colors hover:ring-beam-glow/50"
            >
                <span className="font-script text-xl text-beam-glow">{copy.openOn(platform)}</span>
                <span className="mt-2 font-display text-base leading-tight font-bold tracking-[0.03em] uppercase">
                    {video.title}
                </span>
                <span className="mt-3 text-[0.95rem] leading-relaxed text-moonlight/75">{video.description}</span>
                <span className="mt-auto pt-5 text-[0.8rem] text-moonlight/55">{copy.external}</span>
            </a>
        );
    }

    return (
        <figure className="flex h-full flex-col">
            <div className="relative aspect-video overflow-hidden rounded-[18px] bg-night">
                {playing ? (
                    <iframe
                        ref={(node) => node?.focus()}
                        src={`https://www.youtube-nocookie.com/embed/${video.youtubeId}?autoplay=1&rel=0`}
                        title={video.title}
                        allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                        referrerPolicy="strict-origin-when-cross-origin"
                        className="absolute inset-0 h-full w-full"
                    />
                ) : (
                    <button
                        type="button"
                        onClick={() => setPlaying(true)}
                        className="group absolute inset-0 flex flex-col items-center justify-center gap-4 bg-[radial-gradient(80%_70%_at_50%_30%,var(--color-night-blue),var(--color-night))] text-moonlight"
                    >
                        <svg
                            aria-hidden
                            viewBox="-120 -50 240 80"
                            className="w-28 opacity-80 transition-transform duration-300 group-hover:-translate-y-1"
                        >
                            <SaucerShape />
                        </svg>
                        <span className="inline-flex min-h-11 items-center gap-2 rounded-full bg-beam px-5 font-semibold text-night">
                            <svg aria-hidden viewBox="0 0 24 24" className="size-4" fill="currentColor">
                                <path d="M8 5.5v13l11-6.5z" />
                            </svg>
                            {copy.watch}
                            <span className="sr-only">: {video.title}</span>
                        </span>
                    </button>
                )}
            </div>
            <figcaption className="mt-3 px-1">
                <span className="block font-display text-[0.95rem] leading-tight font-bold tracking-[0.03em] uppercase">
                    {video.title}
                </span>
                <span className="mt-1.5 block text-[0.95rem] leading-relaxed text-moonlight/75">
                    {video.description}
                </span>
                <span className="mt-2 block text-[0.8rem] text-moonlight/55">
                    {copy.notice}{' '}
                    <a
                        href={video.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-beam-glow underline underline-offset-2"
                    >
                        {copy.openOn(platform)}
                    </a>
                </span>
            </figcaption>
        </figure>
    );
}
