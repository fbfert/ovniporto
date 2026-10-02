import { xsrfToken } from './http';

/**
 * First metadata layer, in the browser. EXIF is read in memory only to
 * *suggest* place and time; the file that leaves the device is a fresh JPEG
 * drawn on a canvas, which carries pixels and nothing else.
 */

export interface PhotoSuggestion {
    point?: { lat: number; lng: number };
    /** Local date-time the photo says it was taken, "YYYY-MM-DDTHH:mm". */
    takenAt?: string;
}

export interface PreparedPhoto {
    id: string;
    /** Small data URL, only for the thumbnail and the local draft. */
    thumb: string;
    suggestion: PhotoSuggestion;
}

export class UnreadablePhotoError extends Error {}

export const MAX_SIDE = 2000;

const pad = (n: number) => String(n).padStart(2, '0');

/** GPS and DateTimeOriginal, read locally. Never sent as such. */
export async function readSuggestion(file: Blob): Promise<PhotoSuggestion> {
    try {
        const exifr = (await import('exifr')).default;
        const [gps, tags] = await Promise.all([
            exifr.gps(file).catch(() => undefined),
            exifr.parse(file, ['DateTimeOriginal']).catch(() => undefined),
        ]);
        const suggestion: PhotoSuggestion = {};
        if (gps && Number.isFinite(gps.latitude) && Number.isFinite(gps.longitude)) {
            suggestion.point = { lat: gps.latitude, lng: gps.longitude };
        }
        const taken = (tags as { DateTimeOriginal?: Date } | undefined)?.DateTimeOriginal;
        if (taken instanceof Date && !Number.isNaN(taken.getTime())) {
            suggestion.takenAt = `${taken.getFullYear()}-${pad(taken.getMonth() + 1)}-${pad(taken.getDate())}T${pad(taken.getHours())}:${pad(taken.getMinutes())}`;
        }
        return suggestion;
    } catch {
        return {};
    }
}

/** Decodes (orientation applied), scales to at most MAX_SIDE and re-encodes as JPEG: no metadata survives. */
export async function reencode(file: Blob, maxSide = MAX_SIDE, quality = 0.88): Promise<Blob> {
    let bitmap: ImageBitmap;
    try {
        bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    } catch {
        throw new UnreadablePhotoError();
    }
    const scale = Math.min(1, maxSide / Math.max(bitmap.width, bitmap.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * scale);
    canvas.height = Math.round(bitmap.height * scale);
    canvas.getContext('2d')?.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close();
    return new Promise((resolve, reject) =>
        canvas.toBlob((blob) => (blob ? resolve(blob) : reject(new UnreadablePhotoError())), 'image/jpeg', quality),
    );
}

export async function thumbnail(blob: Blob, size = 160): Promise<string> {
    const small = await reencode(blob, size, 0.7);
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = () => resolve(String(reader.result));
        reader.readAsDataURL(small);
    });
}

/** Sends the clean blob to the private temporary area; returns its upload id. */
export async function uploadPhoto(blob: Blob): Promise<string> {
    const body = new FormData();
    body.append('photo', blob, 'foto.jpg');
    const response = await fetch('/relatar/fotos', {
        method: 'POST',
        body,
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
    });
    if (!response.ok) {
        const error = (await response.json().catch(() => null)) as { errors?: { photo?: string[] } } | null;
        throw new Error(error?.errors?.photo?.[0] ?? 'upload failed');
    }
    return ((await response.json()) as { id: string }).id;
}

export async function discardPhoto(id: string): Promise<void> {
    await fetch(`/relatar/fotos/${id}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
    }).catch(() => undefined);
}

export interface PhotoPipeline {
    readSuggestion: (file: Blob) => Promise<PhotoSuggestion>;
    reencode: (file: Blob) => Promise<Blob>;
    thumbnail: (blob: Blob) => Promise<string>;
    upload: (blob: Blob) => Promise<string>;
}

export const browserPipeline: PhotoPipeline = { readSuggestion, reencode, thumbnail, upload: uploadPhoto };

/**
 * Read the suggestion from the original, then upload only the re-encoded copy.
 * The original File never reaches the network.
 */
export async function preparePhoto(file: File, pipeline: PhotoPipeline = browserPipeline): Promise<PreparedPhoto> {
    const suggestion = await pipeline.readSuggestion(file);
    const clean = await pipeline.reencode(file);
    const [id, thumb] = await Promise.all([pipeline.upload(clean), pipeline.thumbnail(clean)]);
    return { id, thumb, suggestion };
}
