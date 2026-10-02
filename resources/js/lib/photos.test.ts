import { describe, expect, it, vi } from 'vitest';
import { preparePhoto, type PhotoPipeline } from './photos';

describe('preparePhoto', () => {
    it('uploads only the re-encoded copy, never the original file with its EXIF', async () => {
        const original = new File([new Uint8Array([0xff, 0xd8, 0xff, 0xe1])], 'ceu.jpg', { type: 'image/jpeg' });
        const clean = new Blob(['pixels only'], { type: 'image/jpeg' });
        const pipeline: PhotoPipeline = {
            readSuggestion: vi.fn().mockResolvedValue({ point: { lat: -27.8, lng: -50.3 } }),
            reencode: vi.fn().mockResolvedValue(clean),
            thumbnail: vi.fn().mockResolvedValue('data:image/jpeg;base64,AA=='),
            upload: vi.fn().mockResolvedValue('upload-1'),
        };

        const photo = await preparePhoto(original, pipeline);

        expect(pipeline.readSuggestion).toHaveBeenCalledWith(original); // EXIF read in memory only
        expect(pipeline.reencode).toHaveBeenCalledWith(original);
        expect(pipeline.upload).toHaveBeenCalledTimes(1);
        expect(pipeline.upload).toHaveBeenCalledWith(clean);
        expect(pipeline.upload).not.toHaveBeenCalledWith(original);
        expect(photo).toEqual({
            id: 'upload-1',
            thumb: 'data:image/jpeg;base64,AA==',
            suggestion: { point: { lat: -27.8, lng: -50.3 } },
        });
    });
});
