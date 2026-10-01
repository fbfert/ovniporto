import type { ReactNode } from 'react';
import { AbductionHero } from '@/Components/Home/AbductionHero';
import { CommunitySection } from '@/Components/Home/CommunitySection';
import { LegendSection } from '@/Components/Home/LegendSection';
import { LogbookSection } from '@/Components/Home/LogbookSection';
import { PlaceSection } from '@/Components/Home/PlaceSection';
import { RegionSection } from '@/Components/Home/RegionSection';
import { SouvenirsSection } from '@/Components/Home/SouvenirsSection';
import { WelcomeSection } from '@/Components/Home/WelcomeSection';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Marquee } from '@/Components/Ui/Marquee';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { HomeProps } from '@/types';

export default function Home({ counters, content, sightings, products, spaces, partners }: HomeProps) {
    return (
        <>
            <SeoHead />
            {/* 01 */}
            <AbductionHero />
            {/* 02 */}
            <WelcomeSection intro={content.home_intro} sightingsCount={counters.sightings} />
            {/* 03 */}
            <LogbookSection sightings={sightings} />
            {/* 04 */}
            <Marquee items={t.strip} />
            {/* 05 */}
            <PlaceSection lead={content.home_place} spaces={spaces} />
            {/* 06 */}
            <SouvenirsSection lead={content.home_store} products={products} />
            {/* 07 */}
            <LegendSection teaser={content.home_legend} pending={content.legend_body === null} />
            {/* 08 */}
            <RegionSection partners={partners} contactEmail={content.contact_email} />
            {/* 09 */}
            <CommunitySection whatsapp={content.link_whatsapp} instagram={content.link_instagram} />
            {/* 10: footer lives in the layout */}
        </>
    );
}

Home.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
