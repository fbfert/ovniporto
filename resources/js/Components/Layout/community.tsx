import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { InstagramIcon, MailIcon, WhatsAppIcon } from '@/Components/Icons';
import type { CommunityLinks, SharedProps } from '@/types';

export interface CommunityChannel {
    key: string;
    label: string;
    /** null while the link isn't registered: show "em breve", never a dead link. */
    href: string | null;
    icon: ReactNode;
}

/** WhatsApp, Instagram and e-mail, from the props shared with every page. */
export function useCommunityChannels(): CommunityChannel[] {
    const { community } = usePage<SharedProps>().props as { community?: CommunityLinks };
    return [
        { key: 'whatsapp', label: 'WhatsApp', href: community?.whatsapp ?? null, icon: <WhatsAppIcon /> },
        { key: 'instagram', label: 'Instagram', href: community?.instagram ?? null, icon: <InstagramIcon /> },
        {
            key: 'email',
            label: community?.email ?? '',
            href: community?.email ? `mailto:${community.email}` : null,
            icon: <MailIcon />,
        },
    ];
}
