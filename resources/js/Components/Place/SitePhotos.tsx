import { useState, type KeyboardEvent } from 'react';
import { CameraIcon } from '@/Components/Icons';
import { Button } from '@/Components/Ui/Button';
import { Modal } from '@/Components/Ui/Modal';
import { t } from '@/i18n/pt-BR';

export interface SitePhoto {
    url: string;
    alt: string;
    caption: string | null;
    takenAt: string | null;
}

const copy = t.placePage;

/** Real photos of the land, as thumbnails that open in an accessible lightbox. Empty: a dashed frame, never borrowed images. */
export function SitePhotos({ photos, className = '' }: { photos: SitePhoto[]; className?: string }) {
    const [index, setIndex] = useState<number | null>(null);

    if (photos.length === 0) {
        return (
            <div
                className={`flex flex-col items-center justify-center gap-3 rounded-[22px] border-2 border-dashed border-night/25 bg-night/[0.03] p-6 text-center ${className}`}
            >
                <CameraIcon size="3rem" className="text-horizon" />
                <p className="font-script text-2xl text-horizon">{copy.todayEmpty}</p>
            </div>
        );
    }

    const current = index === null ? null : photos[index];
    const step = (delta: number) => setIndex((i) => (i === null ? null : (i + delta + photos.length) % photos.length));
    const onKeyDown = (event: KeyboardEvent) => {
        if (event.key === 'ArrowRight') step(1);
        if (event.key === 'ArrowLeft') step(-1);
    };

    return (
        <>
            <ul className={`grid grid-cols-2 gap-3 ${className}`}>
                {photos.map((photo, i) => (
                    <li key={photo.url} className={i === 0 ? 'col-span-2' : ''}>
                        <button
                            type="button"
                            onClick={() => setIndex(i)}
                            className="group block w-full overflow-hidden rounded-[18px] bg-night"
                        >
                            <img
                                src={photo.url}
                                alt={photo.alt}
                                loading="lazy"
                                className={`w-full object-cover transition-transform duration-500 ease-snap [@media(hover:hover)]:group-hover:scale-[1.03] ${i === 0 ? 'aspect-[16/10]' : 'aspect-square'}`}
                            />
                        </button>
                    </li>
                ))}
            </ul>
            <Modal
                open={current !== null && current !== undefined}
                onClose={() => setIndex(null)}
                title={index === null ? '' : copy.photoOf(index + 1, photos.length)}
                className="max-w-3xl!"
            >
                {current && (
                    <figure onKeyDown={onKeyDown}>
                        <img src={current.url} alt={current.alt} className="w-full rounded-[14px]" />
                        {current.caption && (
                            <figcaption className="mt-3 font-script text-xl">{current.caption}</figcaption>
                        )}
                        {photos.length > 1 && (
                            <div className="mt-5 flex justify-between gap-3">
                                <Button variant="secondary" tone="dark" onClick={() => step(-1)}>
                                    {copy.previous}
                                </Button>
                                <Button variant="secondary" tone="dark" onClick={() => step(1)}>
                                    {copy.next}
                                </Button>
                            </div>
                        )}
                    </figure>
                )}
            </Modal>
        </>
    );
}
