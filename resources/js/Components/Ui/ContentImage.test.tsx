import { cleanup, render } from '@testing-library/react';
import { afterEach, describe, expect, it } from 'vitest';
import { ContentImage, uploadedSources } from './Picture';

afterEach(cleanup);

const UPLOAD = '/storage/products/0b4a8f2e-3c1d-4e5f-9a7b-1c2d3e4f5a6b.webp';
const BASE = '/storage/products/0b4a8f2e-3c1d-4e5f-9a7b-1c2d3e4f5a6b';

describe('uploadedSources', () => {
    it('derives AVIF and WebP srcsets in 400–1600 and the placeholder from a panel upload', () => {
        expect(uploadedSources(UPLOAD)).toEqual({
            avif: `${BASE}-400.avif 400w, ${BASE}-800.avif 800w, ${BASE}-1200.avif 1200w, ${BASE}-1600.avif 1600w`,
            webp: `${BASE}-400.webp 400w, ${BASE}-800.webp 800w, ${BASE}-1200.webp 1200w, ${BASE}-1600.webp 1600w`,
            placeholder: `${BASE}-20.webp`,
        });
    });

    it('leaves seeded demo files and other URLs alone', () => {
        expect(uploadedSources('/storage/demo/0b4a8f2e-3c1d-4e5f-9a7b-1c2d3e4f5a6b.webp')).toBeNull();
        expect(uploadedSources('/concept/cover.jpg')).toBeNull();
        expect(uploadedSources(null)).toBeNull();
    });
});

describe('ContentImage', () => {
    it('offers AVIF first, then WebP, over the blurred placeholder, lazy by default', () => {
        const { container } = render(<ContentImage src={UPLOAD} alt="Adesivo" sizes="50vw" />);
        const sources = container.querySelectorAll('source');
        const img = container.querySelector('img');

        expect([...sources].map((s) => s.getAttribute('type'))).toEqual(['image/avif', 'image/webp']);
        expect(sources[0]?.getAttribute('srcset')).toContain(`${BASE}-1200.avif 1200w`);
        expect(sources[1]?.getAttribute('sizes')).toBe('50vw');
        expect(container.querySelector('picture')?.getAttribute('style')).toContain(`${BASE}-20.webp`);
        expect(img?.getAttribute('loading')).toBe('lazy');
        expect(img?.getAttribute('alt')).toBe('Adesivo');
    });

    it('loads the hero eagerly with high priority', () => {
        const { container } = render(<ContentImage src={UPLOAD} alt="Capa" priority />);
        const img = container.querySelector('img');

        expect(img?.getAttribute('loading')).toBe('eager');
        expect(img?.getAttribute('fetchpriority')).toBe('high');
    });

    it('uses the server sources of a report photo, without AVIF when it has none', () => {
        const { container } = render(
            <ContentImage
                src="/fotos/relatos/7/1600"
                alt="Luz"
                sources={{
                    webp: '/fotos/relatos/7/400 400w',
                    avif: null,
                    placeholder: null,
                    width: 1600,
                    height: 1200,
                }}
            />,
        );

        expect([...container.querySelectorAll('source')].map((s) => s.getAttribute('type'))).toEqual(['image/webp']);
        expect(container.querySelector('img')?.getAttribute('width')).toBe('1600');
    });

    it('falls back to a plain lazy image without sources', () => {
        const { container } = render(<ContentImage src="/concept/cover.jpg" alt="Conceito" />);

        expect(container.querySelector('picture')).toBeNull();
        expect(container.querySelector('img')?.getAttribute('loading')).toBe('lazy');
    });
});
