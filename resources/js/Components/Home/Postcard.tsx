import { usePage } from '@inertiajs/react';
import { PostcardBack } from '@/Components/Postcard/PostcardBack';
import { Button } from '@/Components/Ui/Button';
import { useToast } from '@/Components/Ui/Toast';
import { t } from '@/i18n/pt-BR';
import type { SharedProps } from '@/types';

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
                <PostcardBack />
            </div>
            <div className="mt-8 flex flex-wrap gap-3">
                <Button href={shareUrl} external tone="dark">
                    {t.community.shareWhatsapp}
                </Button>
                <Button variant="secondary" tone="dark" onClick={copy}>
                    {t.community.copy}
                </Button>
                <Button variant="ghost" tone="dark" href="/postal">
                    {t.community.openPostcard}
                </Button>
            </div>
        </div>
    );
}
