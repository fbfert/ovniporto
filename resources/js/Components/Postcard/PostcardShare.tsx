import { useState, useSyncExternalStore } from 'react';
import { WhatsAppIcon } from '@/Components/Icons';
import { Button } from '@/Components/Ui/Button';
import { t } from '@/i18n/pt-BR';

const copy = t.postcardPage;

export function postcardWhatsappUrl(url: string): string {
    return `https://wa.me/?text=${encodeURIComponent(copy.shareText(url))}`;
}

const noSubscription = () => () => {};

/** SSR-safe: the server and the hydration pass show the fallback, then the client switches if it can share. */
function useNativeShare(): boolean {
    return useSyncExternalStore(
        noSubscription,
        () => typeof navigator.share === 'function',
        () => false,
    );
}

async function postcardFile(imageUrl: string): Promise<File | null> {
    try {
        const blob = await (await fetch(imageUrl)).blob();
        const file = new File([blob], copy.fileName, { type: blob.type || 'image/jpeg' });
        return navigator.canShare?.({ files: [file] }) ? file : null;
    } catch {
        return null;
    }
}

/**
 * The device's own share sheet (with the image when it can carry files);
 * WhatsApp and "copy link" where there is none. Downloading always works.
 */
export function PostcardShare({ url, imageUrl }: { url: string; imageUrl: string }) {
    const native = useNativeShare();
    const [copied, setCopied] = useState(false);

    const share = async () => {
        const file = await postcardFile(imageUrl);
        const data: ShareData = {
            title: copy.shareTitle,
            text: copy.shareText(url),
            ...(file ? { files: [file] } : { url }),
        };
        try {
            await navigator.share(data);
        } catch {
            // Closing the share sheet rejects too: nothing to report.
        }
    };

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
        <div className="flex flex-wrap gap-3">
            {native ? (
                <Button onClick={share}>{copy.share}</Button>
            ) : (
                <>
                    <Button href={postcardWhatsappUrl(url)} external iconLeft={<WhatsAppIcon size="1.1rem" />}>
                        {copy.whatsapp}
                    </Button>
                    <Button variant="secondary" onClick={copyLink}>
                        {copied ? copy.copied : copy.copy}
                    </Button>
                </>
            )}
            <a
                href={imageUrl}
                download={copy.fileName}
                className="inline-flex min-h-11 items-center px-2 font-semibold text-night underline decoration-beam decoration-2 underline-offset-4 hover:decoration-night"
            >
                {copy.download}
            </a>
            <span className="sr-only" aria-live="polite">
                {copied ? copy.copied : ''}
            </span>
        </div>
    );
}
