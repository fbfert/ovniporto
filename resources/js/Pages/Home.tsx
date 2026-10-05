import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { AbductionHero } from '@/Components/Home/AbductionHero';
import { CommunitySection } from '@/Components/Home/CommunitySection';
import { OriginSection } from '@/Components/Home/OriginSection';
import { LogbookSection } from '@/Components/Home/LogbookSection';
import { PlaceSection } from '@/Components/Home/PlaceSection';
import { RelatoSection } from '@/Components/Home/RelatoSection';
import { RegionSection } from '@/Components/Home/RegionSection';
import { SouvenirsSection } from '@/Components/Home/SouvenirsSection';
import { WelcomeSection } from '@/Components/Home/WelcomeSection';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Marquee } from '@/Components/Ui/Marquee';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { HomeProps, SharedProps } from '@/types';

export default function Home({
    counters,
    content,
    sightings,
    products,
    spaces,
    partners,
    relatoOpening,
    cachiCover,
}: HomeProps) {
    const { community } = usePage<SharedProps>().props;
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
            <OriginSection teaser={content.home_legend} cover={cachiCover} />
            {/* 08 */}
            <RelatoSection opening={relatoOpening} />
            {/* 09 */}
            <RegionSection partners={partners} contactEmail={community.email} />
            {/* 10 */}
            <CommunitySection />
            {/* 11: footer lives in the layout */}
        </>
    );
}

Home.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
