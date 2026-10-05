import { cleanup, fireEvent, render, screen, waitFor, within } from '@testing-library/react';
import { afterEach, describe, expect, it } from 'vitest';
import type { CachiVideo, ImageCredit } from '@/types/origin';
import { ChapterIndex } from './ChapterIndex';
import { CreditedImage } from './CreditedImage';
import { ConfidenceSeal, SourceBadge } from './SourceBadge';
import { VideoFacade } from './VideoFacade';

afterEach(cleanup);

const photo: ImageCredit = {
    slug: 'cachi-aereo',
    title: 'Ovnipuerto de Cachi desde el aire',
    alt: 'Vista aérea da Estrella de la Esperanza.',
    author: 'Nora Aliessi (nraliessi)',
    license: 'CC BY 2.0',
    licenseUrl: 'https://creativecommons.org/licenses/by/2.0/',
    sourceUrl: 'https://commons.wikimedia.org/wiki/File:Ovnipuerto_de_Cachi_desde_el_aire.jpg',
    kind: 'photo',
};

describe('CreditedImage', () => {
    it('never shows a photo without author, licence and source', () => {
        render(<CreditedImage credit={photo} caption="Do alto" />);

        const caption = screen.getByText(/Nora Aliessi/).closest('figcaption')!;
        expect(caption.textContent).toContain('Foto: Nora Aliessi (nraliessi)');
        expect(within(caption).getByRole('link', { name: 'CC BY 2.0' }).getAttribute('href')).toBe(photo.licenseUrl);
        expect(within(caption).getByRole('link', { name: 'origem e licença' }).getAttribute('href')).toBe(
            photo.sourceUrl,
        );
        expect(screen.getByRole('img').getAttribute('alt')).toBe(photo.alt);
    });

    it('says a location map is a map, in the caption and on the image', () => {
        render(
            <CreditedImage
                credit={{ ...photo, slug: 'atlas-lages', kind: 'location-map', alt: 'Mapa de localização: Lages.' }}
            />,
        );

        expect(screen.getByText(/^Mapa de localização:/)).toBeTruthy();
        expect(screen.getByText('mapa de localização')).toBeTruthy();
        expect(screen.getByRole('img').getAttribute('alt')).toMatch(/^Mapa de localização/);
    });
});

describe('SourceBadge and ConfidenceSeal', () => {
    it('writes the source kind in words', () => {
        render(<SourceBadge kind={{ kind: 'report', label: 'relato de fenômeno' }} detail="múltiplas testemunhas" />);

        expect(screen.getByText('relato de fenômeno')).toBeTruthy();
        expect(document.body.textContent).toContain('Tipo de fonte: relato de fenômeno· múltiplas testemunhas');
    });

    it('reads the seal out with what each grade means', () => {
        render(
            <ConfidenceSeal
                seal={[
                    { grade: 'A', meaning: 'fonte primária ou ato oficial' },
                    { grade: 'B', meaning: 'fonte institucional ou documentação secundária muito sólida' },
                ]}
            />,
        );

        expect(screen.getByRole('img').getAttribute('aria-label')).toBe(
            'Grau de confiança A/B: fonte primária ou ato oficial; fonte institucional ou documentação secundária muito sólida',
        );
    });
});

describe('VideoFacade', () => {
    const youtube: CachiVideo = {
        title: 'Antonio Zuleta — Fenómeno OVNI en la Recta de Tin Tin',
        description: 'Entrevista de 2018.',
        platform: 'youtube',
        youtubeId: 'tPnh8GMCoXQ',
        url: 'https://www.youtube.com/watch?v=tPnh8GMCoXQ',
    };

    it('loads nothing from YouTube until "Assistir", then the no-cookie player takes focus', () => {
        render(<VideoFacade video={youtube} />);
        expect(document.querySelector('iframe')).toBeNull();

        fireEvent.click(screen.getByRole('button', { name: /Assistir/ }));

        const iframe = document.querySelector('iframe')!;
        expect(iframe.getAttribute('src')).toBe('https://www.youtube-nocookie.com/embed/tPnh8GMCoXQ?autoplay=1&rel=0');
        expect(document.activeElement).toBe(iframe);
    });

    it('keeps other platforms as a link that opens outside the site', () => {
        render(
            <VideoFacade
                video={{
                    title: 'Al centro de la Tierra',
                    description: 'Trailer.',
                    platform: 'vimeo',
                    url: 'https://vimeo.com/270913061',
                }}
            />,
        );

        const link = screen.getByRole('link');
        expect(link.getAttribute('href')).toBe('https://vimeo.com/270913061');
        expect(link.getAttribute('target')).toBe('_blank');
        expect(link.textContent).toContain('Abrir no Vimeo');
        expect(document.querySelector('iframe, button')).toBeNull();
    });
});

describe('ChapterIndex', () => {
    const chapters = [
        { id: 'prologo', title: 'Antes da estrela' },
        { id: 'a-noite', title: 'A noite' },
    ];

    it('links every chapter to its anchor', () => {
        render(<ChapterIndex chapters={chapters} />);

        const links = screen.getAllByRole('link', { name: /A noite/ });
        expect(links[0]!.getAttribute('href')).toBe('#a-noite');
    });

    it('opens the phone index as a dialog that closes with Esc', async () => {
        render(<ChapterIndex chapters={chapters} />);

        fireEvent.click(screen.getByRole('button', { name: 'Capítulos' }));
        const dialog = screen.getByRole('dialog');
        expect(within(dialog).getByRole('link', { name: /Antes da estrela/ })).toBeTruthy();

        fireEvent.keyDown(dialog, { key: 'Escape' });
        await waitFor(() => expect(screen.queryByRole('dialog')).toBeNull());
    });
});
