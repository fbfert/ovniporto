import { Head, usePage } from '@inertiajs/react';
import type { SharedProps } from '@/types';

const DEFAULT_DESCRIPTION =
    'Astroturismo na Serra Catarinense: comunidade, Livro de avistamentos e lembranças do OVNIPORTO, a futura pista de pouso de Lages, SC.';

/** Title, description, canonical, Open Graph and Twitter Card. Rendered on the server. */
export function SeoHead({
    title,
    description = DEFAULT_DESCRIPTION,
    image = '/og/default.jpg',
}: {
    title?: string;
    description?: string;
    image?: string;
}) {
    const { appUrl, currentUrl } = usePage<SharedProps>().props;
    const fullTitle = title ? `${title} · OVNIPORTO Lages` : 'OVNIPORTO Lages · A pista de pouso do planalto';
    const imageUrl = image.startsWith('http') ? image : `${appUrl}${image}`;

    return (
        <Head title={title}>
            <meta head-key="description" name="description" content={description} />
            <link head-key="canonical" rel="canonical" href={currentUrl} />
            <meta head-key="og:type" property="og:type" content="website" />
            <meta head-key="og:site_name" property="og:site_name" content="OVNIPORTO Lages" />
            <meta head-key="og:locale" property="og:locale" content="pt_BR" />
            <meta head-key="og:title" property="og:title" content={fullTitle} />
            <meta head-key="og:description" property="og:description" content={description} />
            <meta head-key="og:url" property="og:url" content={currentUrl} />
            <meta head-key="og:image" property="og:image" content={imageUrl} />
            <meta head-key="og:image:width" property="og:image:width" content="1200" />
            <meta head-key="og:image:height" property="og:image:height" content="630" />
            <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
            <meta head-key="twitter:title" name="twitter:title" content={fullTitle} />
            <meta head-key="twitter:description" name="twitter:description" content={description} />
            <meta head-key="twitter:image" name="twitter:image" content={imageUrl} />
        </Head>
    );
}
