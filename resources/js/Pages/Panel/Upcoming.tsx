import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Badge, Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel;

/** An area the role may open whose tools arrive with a later change. */
export default function Upcoming({ area }: { area: string }) {
    const title = copy.areas[area] ?? area;
    return (
        <>
            <Head title={`${title} · ${copy.title}`} />
            <Badge tone="horizon">{t.upcoming.badge}</Badge>
            <Display as="h1" className="mt-4 text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {title}
            </Display>
            <p className="mt-4 max-w-[56ch] text-lg text-night/75">{copy.upcoming.lead}</p>
            <p className="mt-6 max-w-[56ch] rounded-[22px] border-2 border-dashed border-night/20 p-6 font-script text-2xl text-horizon">
                {copy.upcoming[area]}
            </p>
        </>
    );
}

Upcoming.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
