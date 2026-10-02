import { useState } from 'react';
import { WhatsAppIcon } from '@/Components/Icons';
import { Button } from '@/Components/Ui/Button';
import { t } from '@/i18n/pt-BR';

const copy = t.sightingPage;

export function whatsappShareUrl(url: string): string {
    return `https://wa.me/?text=${encodeURIComponent(copy.shareText(url))}`;
}

/** "Mande um postal": WhatsApp with the text ready, or the bare link to the clipboard. */
export function SharePostcard({ url }: { url: string }) {
    const [copied, setCopied] = useState(false);

    const copyLink = async () => {
        try {
            await navigator.clipboard.writeText(url);
            setCopied(true);
            window.setTimeout(() => setCopied(false), 2500);
        } catch {
            setCopied(false);
        }
    };

    return (
        <div className="rounded-[22px] bg-moonlight p-6 text-night shadow-polaroid">
            <p className="font-display text-lg font-bold tracking-[0.03em] uppercase">{copy.postcardTitle}</p>
            <p className="mt-1 font-script text-xl text-horizon">{copy.postcardLead}</p>
            <div className="mt-5 flex flex-wrap gap-3">
                <Button href={whatsappShareUrl(url)} external iconLeft={<WhatsAppIcon size="1.1rem" />}>
                    {copy.whatsapp}
                </Button>
                <Button variant="secondary" onClick={copyLink}>
                    {copied ? copy.copied : copy.copy}
                </Button>
            </div>
            <span className="sr-only" aria-live="polite">
                {copied ? copy.copied : ''}
            </span>
        </div>
    );
}
