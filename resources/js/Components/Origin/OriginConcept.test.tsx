import { cleanup, render, screen } from '@testing-library/react';
import { afterEach, describe, expect, it } from 'vitest';
import { hasConcept } from '@/Components/Ui/Picture';
import { ORIGIN_CONCEPTS, OriginConcept } from './OriginConcept';

afterEach(cleanup);

describe('OriginConcept', () => {
    it.each(ORIGIN_CONCEPTS.filter((slug) => !hasConcept(slug)))(
        'shows "conceito em produção" while %s has not been generated',
        (slug) => {
            render(<OriginConcept slug={slug} />);

            expect(screen.getByRole('img', { name: 'Conceito em produção' })).toBeTruthy();
            expect(screen.getByText('conceito')).toBeTruthy();
            expect(document.querySelector('picture')).toBeNull();
        },
    );
});
