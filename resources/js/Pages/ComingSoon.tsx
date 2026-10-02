import type { ReactNode } from 'react';
import { WaitlistForm } from '@/Components/Home/WaitlistForm';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { NightBanner } from '@/Components/Scene/NightBanner';
import { Button } from '@/Components/Ui/Button';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

interface Props {
    slug: string;
    title: string;
    eyebrow: string;
    description: string;
    phase: string;
}

/** Honest placeholder for menu destinations that later OpenSpec changes will build. */
export default function ComingSoon({ slug, title, eyebrow, description, phase }: Props) {
    return (
        <>
            <SeoHead title={title} description={description} />
            <NightBanner>
                <Eyebrow tone="dark">{eyebrow}</Eyebrow>
                <Display as="h1" className="mt-4 text-[clamp(1.7rem,0.35rem+6.4vw,6rem)]! text-balance">
                    {title}
                </Display>
                <div className="mt-6 flex flex-wrap justify-center gap-2">
                    <Badge tone="car">{t.upcoming.badge}</Badge>
                    <Badge tone="neutral">{phase}</Badge>
                </div>
                <p className="mx-auto mt-8 max-w-[52ch] text-lg leading-relaxed text-moonlight/80">{description}</p>
                <div className="mx-auto mt-10 max-w-lg text-left">
                    <p className="mb-3 text-center font-script text-2xl text-beam-glow">{t.upcoming.notify}</p>
                    <WaitlistForm source={slug} />
                </div>
                <div className="mt-10">
                    <Button href="/" variant="secondary" tone="dark">
                        {t.upcoming.back}
                    </Button>
                </div>
            </NightBanner>
        </>
    );
}

ComingSoon.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
