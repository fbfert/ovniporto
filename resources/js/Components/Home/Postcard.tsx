import { usePage } from '@inertiajs/react';
import { useId } from 'react';
import { SealArt } from '@/Components/Brand/Seal';
import { SaucerShape } from '@/Components/Scene/Art';
import { Button } from '@/Components/Ui/Button';
import { useToast } from '@/Components/Ui/Toast';
import { t } from '@/i18n/pt-BR';
import type { SharedProps } from '@/types';

function Postmark() {
    const id = `postmark-${useId().replace(/:/g, '')}`;
    return (
        <svg
            aria-hidden
            viewBox="0 0 120 120"
            className="absolute -top-4 right-16 w-28 -rotate-12 text-horizon opacity-80 mix-blend-multiply sm:right-24"
        >
            <defs>
                <path id={id} d="M 60 60 m -40 0 a 40 40 0 1 1 80 0 a 40 40 0 1 1 -80 0" />
            </defs>
            <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" strokeWidth="2.5" />
            <circle cx="60" cy="60" r="28" fill="none" stroke="currentColor" strokeWidth="1.5" />
            <text fill="currentColor" fontFamily="var(--font-display)" fontWeight="800" fontSize="11" letterSpacing="3">
                <textPath href={`#${id}`}>LAGES · SC · LAGES · SC ·</textPath>
            </text>
            <text
                x="60"
                y="64"
                textAnchor="middle"
                fill="currentColor"
                fontFamily="var(--font-script)"
                fontSize="16"
                fontWeight="600"
            >
                2028
            </text>
            {[0, 1, 2, 3].map((i) => (
                <path
                    key={i}
                    d={`M118 ${44 + i * 10} C 140 ${40 + i * 10} 160 ${48 + i * 10} 190 ${44 + i * 10}`}
                    stroke="currentColor"
                    strokeWidth="2"
                    fill="none"
                />
            ))}
        </svg>
    );
}

/** "Mande um postal": a postcard from the serra, shareable on WhatsApp or by link. */
export function Postcard() {
    const { appUrl } = usePage<SharedProps>().props;
    const toast = useToast();
    const shareUrl = `https://wa.me/?text=${encodeURIComponent(t.community.shareText(appUrl))}`;

    const copy = async () => {
        try {
            await navigator.clipboard.writeText(appUrl);
            toast(t.community.copied);
        } catch {
            toast(appUrl);
        }
    };

    return (
        <div>
            <div className="relative rotate-[1.5deg] rounded-[6px] bg-moonlight p-5 text-night shadow-polaroid sm:p-7">
                <div className="grid gap-6 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                    <div className="flex items-center gap-4 sm:flex-col sm:items-start">
                        <div className="w-24 shrink-0 -rotate-6 sm:w-32">
                            <SealArt title="Selo OVNIPORTO no postal" sizes="8rem" />
                        </div>
                        <p className="font-script text-[1.45rem] leading-snug text-night/85">
                            {t.community.postcardMessage}
                        </p>
                    </div>
                    <div className="relative border-night/15 sm:border-l-2 sm:border-dashed sm:pl-6">
                        <div className="ml-auto flex size-24 items-center justify-center bg-car stamp-edge">
                            <div className="flex h-full w-full items-center justify-center bg-night-blue">
                                <svg viewBox="-120 -60 240 100" className="w-16" aria-hidden>
                                    <SaucerShape />
                                </svg>
                            </div>
                        </div>
                        <Postmark />
                        <div className="mt-6 space-y-4 font-script text-xl text-night/70">
                            <p className="border-b border-night/25 pb-1">{t.community.postcardTo}</p>
                            <p className="border-b border-night/25 pb-1">Serra Catarinense</p>
                            <p className="border-b border-night/25 pb-1">{t.brand.city}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div className="mt-8 flex flex-wrap gap-3">
                <Button href={shareUrl} external tone="dark">
                    {t.community.shareWhatsapp}
                </Button>
                <Button variant="secondary" tone="dark" onClick={copy}>
                    {t.community.copy}
                </Button>
            </div>
        </div>
    );
}
