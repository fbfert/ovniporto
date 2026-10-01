import type { ReactNode } from 'react';
import { Seal } from '@/Components/Brand/Seal';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { NightBanner } from '@/Components/Scene/NightBanner';
import { Button } from '@/Components/Ui/Button';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

export default function Confirmed() {
    return (
        <>
            <SeoHead title="Inscrição confirmada" />
            <NightBanner>
                <div className="flex justify-center">
                    <div className="animate-seal-in">
                        <Seal size="md" glow />
                    </div>
                </div>
                <Eyebrow tone="dark" className="mt-10">
                    {t.waitlist.confirmedEyebrow}
                </Eyebrow>
                <Display as="h1" className="mt-3">
                    {t.waitlist.confirmedTitle}
                </Display>
                <p className="mx-auto mt-6 max-w-[42ch] text-lg text-moonlight/80">{t.waitlist.confirmedLead}</p>
                <div className="mt-10">
                    <Button href="/">{t.upcoming.back}</Button>
                </div>
            </NightBanner>
        </>
    );
}

Confirmed.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
