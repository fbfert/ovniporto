import type { ReactNode } from 'react';
import { NightBanner } from '@/Components/Scene/NightBanner';
import { Display, Eyebrow } from '@/Components/Ui/Typography';

/** Short night cover shared by the content pages: cursive eyebrow, display title, one line. */
export function PageCover({
    eyebrow,
    title,
    lead,
    children,
}: {
    eyebrow: string;
    title: string;
    lead?: string;
    children?: ReactNode;
}) {
    return (
        <NightBanner size="short">
            <Eyebrow tone="dark">{eyebrow}</Eyebrow>
            <Display as="h1" className="mt-4 text-[clamp(1.7rem,0.35rem+6.4vw,5.5rem)]! text-balance">
                {title}
            </Display>
            {lead && <p className="mx-auto mt-6 max-w-[48ch] text-lg leading-relaxed text-moonlight/80">{lead}</p>}
            {children}
        </NightBanner>
    );
}
