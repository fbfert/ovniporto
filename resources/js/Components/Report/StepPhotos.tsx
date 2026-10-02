import { useId, useState } from 'react';
import { CameraIcon, CloseIcon } from '@/Components/Icons';
import { t } from '@/i18n/pt-BR';
import { browserPipeline, discardPhoto, preparePhoto, UnreadablePhotoError, type PhotoPipeline } from '@/lib/photos';
import type { DraftUpdate, ReportDraft } from './draft';

const copy = t.report.photos;

/**
 * Up to 3 optional photos. Each one is read for a suggestion, re-encoded
 * without metadata and uploaded right away to the private temporary area;
 * only its id and a tiny thumbnail stay in the draft.
 */
export function StepPhotos({
    draft,
    update,
    maxPhotos,
    error,
    pipeline = browserPipeline,
}: {
    draft: ReportDraft;
    update: DraftUpdate;
    maxPhotos: number;
    error?: string;
    pipeline?: PhotoPipeline;
}) {
    const inputId = useId();
    const [busy, setBusy] = useState(0);
    const [problem, setProblem] = useState<string | null>(null);
    const room = maxPhotos - draft.photos.length - busy;

    const add = async (files: FileList | null) => {
        if (!files) return;
        setProblem(null);
        const picked = Array.from(files).slice(0, Math.max(0, room));
        setBusy((n) => n + picked.length);
        for (const file of picked) {
            try {
                const photo = await preparePhoto(file, pipeline);
                update((current) => ({
                    photos: [...current.photos, { id: photo.id, thumb: photo.thumb }],
                    // keep only the first suggestion found; it is offered in step 3, never applied silently
                    suggestion:
                        current.suggestion.point || current.suggestion.takenAt ? current.suggestion : photo.suggestion,
                }));
            } catch (e) {
                setProblem(
                    e instanceof UnreadablePhotoError
                        ? copy.unreadable
                        : e instanceof Error && e.message !== 'upload failed'
                          ? e.message
                          : copy.failed,
                );
            } finally {
                setBusy((n) => n - 1);
            }
        }
    };

    const remove = (id: string) => {
        update((current) => ({ photos: current.photos.filter((photo) => photo.id !== id) }));
        void discardPhoto(id);
    };

    return (
        <div className="flex flex-col gap-6">
            <ul className="grid grid-cols-3 gap-3">
                {draft.photos.map((photo, i) => (
                    <li key={photo.id} className="relative aspect-square overflow-hidden rounded-2xl bg-night-blue">
                        <img src={photo.thumb} alt={`Foto ${i + 1}`} className="h-full w-full object-cover" />
                        <button
                            type="button"
                            onClick={() => remove(photo.id)}
                            aria-label={`${copy.remove} ${i + 1}`}
                            className="absolute top-1.5 right-1.5 inline-flex size-11 items-center justify-center rounded-full bg-night/80 text-moonlight"
                        >
                            <CloseIcon size="1.1rem" />
                        </button>
                    </li>
                ))}
                {Array.from({ length: busy }, (_, i) => (
                    <li
                        key={`busy-${i}`}
                        className="flex aspect-square items-center justify-center rounded-2xl bg-night-blue text-sm text-moonlight/70"
                        aria-live="polite"
                    >
                        {copy.preparing}
                    </li>
                ))}
                {room > 0 && (
                    <li>
                        <label
                            htmlFor={inputId}
                            className="flex aspect-square cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-moonlight/30 text-center text-sm font-semibold text-moonlight/80 transition-colors focus-within:border-beam hover:border-beam"
                        >
                            <CameraIcon size="1.75rem" className="text-beam" />
                            {copy.add}
                        </label>
                        <input
                            id={inputId}
                            type="file"
                            accept="image/jpeg,image/png,image/heic,image/heif"
                            capture="environment"
                            multiple
                            className="sr-only"
                            onChange={(event) => {
                                void add(event.target.files);
                                event.target.value = '';
                            }}
                        />
                    </li>
                )}
            </ul>
            {(problem ?? error) && (
                <p role="alert" className="text-sm font-medium text-car">
                    {problem ?? error}
                </p>
            )}
            <div className="space-y-2 rounded-2xl bg-night-blue/70 p-4 text-[0.95rem] leading-relaxed text-moonlight/80">
                <p>{copy.exifNotice}</p>
                <p className="font-semibold text-moonlight">{copy.facesNotice}</p>
            </div>
        </div>
    );
}
