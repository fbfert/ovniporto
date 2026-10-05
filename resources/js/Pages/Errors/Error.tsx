import { Head } from '@inertiajs/react';
import { YellowCarShape } from '@/Components/Scene/Art';
import { NightBanner } from '@/Components/Scene/NightBanner';
import { Button } from '@/Components/Ui/Button';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';

/**
 * Themed error page. Rendered outside the web middleware stack (404s never reach
 * it), so it does not rely on shared props or the public layout.
 */
export default function Error({ status }: { status: number }) {
    const copy = t.errors[status] ?? t.errors[500]!;
    return (
        <>
            <Head title={`${status}`} />
            <main>
                <NightBanner size="tall">
                    <svg
                        aria-hidden
                        viewBox="-80 -80 210 90"
                        className="mx-auto w-48 -rotate-[24deg] animate-hover-bob motion-reduce:animate-none"
                    >
                        <YellowCarShape headlights />
                    </svg>
                    <Eyebrow tone="dark" className="mt-8">
                        Erro {status}
                    </Eyebrow>
                    <Display as="h1" className="mx-auto mt-4 max-w-[18ch] text-[clamp(1.9rem,1rem+3.6vw,4rem)]!">
                        {copy.title}
                    </Display>
                    <p className="mx-auto mt-6 max-w-[44ch] text-lg text-moonlight/80">{copy.lead}</p>
                    <div className="mt-10">
                        <Button href="/" size="lg">
                            {t.errors.back}
                        </Button>
                    </div>
                </NightBanner>
            </main>
        </>
    );
}
