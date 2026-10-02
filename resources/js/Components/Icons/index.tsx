import type { ReactNode } from 'react';

export interface IconProps {
    /** CSS size (any unit). Defaults to 1.25rem. */
    size?: string;
    /** Accessible name. Without it the icon is decorative (aria-hidden). */
    title?: string;
    className?: string;
}

/**
 * Minimal OVNIPORTO icon set: 24px grid, 1.75 stroke in currentColor, round
 * caps. Same weight everywhere so icons sit quietly beside Figtree text.
 */
function Icon({ size = '1.25rem', title, className = '', children }: IconProps & { children: ReactNode }) {
    return (
        <svg
            viewBox="0 0 24 24"
            width={size}
            height={size}
            fill="none"
            stroke="currentColor"
            strokeWidth={1.75}
            strokeLinecap="round"
            strokeLinejoin="round"
            role={title ? 'img' : undefined}
            aria-label={title}
            aria-hidden={title ? undefined : true}
            className={`shrink-0 ${className}`}
        >
            {children}
        </svg>
    );
}

export function UfoIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M8.5 10.2C8.8 7.8 10.2 6.5 12 6.5s3.2 1.3 3.5 3.7" />
            <ellipse cx="12" cy="12" rx="9" ry="2.8" />
            <path d="M9 17.5l-1 2.5M12 17.8V20.5M15 17.5l1 2.5" opacity="0.6" />
        </Icon>
    );
}

export function StarIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M12 3.5l1.9 5.6 5.6 1.9-5.6 1.9L12 18.5l-1.9-5.6L4.5 11l5.6-1.9z" />
        </Icon>
    );
}

export function BeamIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M7 6.5h10" />
            <path d="M9.5 6.5L5 20.5h14L14.5 6.5" />
            <path d="M12 10v6" opacity="0.6" />
        </Icon>
    );
}

export function CarIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M3.5 15.5v-3l2-4.5h9l3.5 4.5 2.5.8v2.2" />
            <path d="M5.5 12h14" />
            <circle cx="7.5" cy="16.5" r="1.8" />
            <circle cx="16.5" cy="16.5" r="1.8" />
        </Icon>
    );
}

export function PinIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11z" />
            <circle cx="12" cy="10" r="2.3" />
        </Icon>
    );
}

export function CameraIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <rect x="3" y="7" width="18" height="13" rx="3" />
            <circle cx="12" cy="13.5" r="3.5" />
            <path d="M8.5 7l1.5-2.5h4L15.5 7" />
        </Icon>
    );
}

export function CompassIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <circle cx="12" cy="12" r="9" />
            <path d="M15.5 8.5l-2 5-5 2 2-5z" />
        </Icon>
    );
}

export function StampIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M9.5 3.5h5v4.2c0 1 .6 1.9 1.5 2.3h0a3 3 0 0 1 1.8 2.8V14H6.2v-1.2A3 3 0 0 1 8 10h0c.9-.4 1.5-1.3 1.5-2.3z" />
            <path d="M5 17.5h14M7 20.5h10" />
        </Icon>
    );
}

export function WhatsAppIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M4 20l1.2-3.6A8.5 8.5 0 1 1 8 19.1z" />
            <path d="M9 8.5c0 3.3 3.2 6.5 6.5 6.5l1-1.6-2.1-1-1 1a4.3 4.3 0 0 1-2.8-2.8l1-1-1-2.1z" />
        </Icon>
    );
}

export function InstagramIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <rect x="3.5" y="3.5" width="17" height="17" rx="5" />
            <circle cx="12" cy="12" r="4" />
            <circle cx="17.2" cy="6.8" r="0.6" fill="currentColor" />
        </Icon>
    );
}

export function MailIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <rect x="3" y="5.5" width="18" height="13" rx="3" />
            <path d="M4 7.5l8 6 8-6" />
        </Icon>
    );
}

export function ChevronDownIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M6 9l6 6 6-6" />
        </Icon>
    );
}

export function CloseIcon(props: IconProps) {
    return (
        <Icon {...props}>
            <path d="M6 6l12 12M18 6L6 18" />
        </Icon>
    );
}
