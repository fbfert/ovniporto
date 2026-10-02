import { Link } from '@inertiajs/react';
import { SealArt } from '@/Components/Brand/Seal';
import { t } from '@/i18n/pt-BR';

const copy = t.members;

/** Google's mark in its own colors: the one place a third-party brand appears, on the button that goes to it. */
function GoogleMark() {
    return (
        <svg viewBox="0 0 24 24" className="size-5" aria-hidden>
            <path
                fill="#4285F4"
                d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5a5.6 5.6 0 0 1-2.4 3.6v3h3.9c2.3-2.1 3.5-5.2 3.5-8.8z"
            />
            <path
                fill="#34A853"
                d="M12 24c3.2 0 6-1.1 8-2.9l-3.9-3c-1.1.7-2.5 1.2-4.1 1.2-3.1 0-5.8-2.1-6.7-5H1.3v3.1A12 12 0 0 0 12 24z"
            />
            <path fill="#FBBC05" d="M5.3 14.3a7.2 7.2 0 0 1 0-4.6V6.6h-4a12 12 0 0 0 0 10.8l4-3.1z" />
            <path
                fill="#EA4335"
                d="M12 4.8c1.8 0 3.3.6 4.6 1.8l3.4-3.4A12 12 0 0 0 1.3 6.6l4 3.1c.9-2.9 3.6-4.9 6.7-4.9z"
            />
        </svg>
    );
}

/**
 * Content shared by the sign-in modal and the /entrar page. The Google link is
 * a plain anchor: it leaves the site, so it must be a full navigation.
 */
export function LoginPanel() {
    return (
        <div className="text-center">
            <div className="mx-auto size-24">
                <SealArt sizes="6rem" />
            </div>
            <p className="mx-auto mt-5 max-w-[32ch] text-lg leading-snug">{copy.loginLead}</p>
            <a
                href="/auth/google"
                className="mt-7 inline-flex h-14 w-full press items-center justify-center gap-3 rounded-full bg-moonlight px-6 font-semibold text-night shadow-lift transition-transform duration-200 ease-snap active:scale-[0.97] sm:w-auto"
            >
                <GoogleMark />
                {copy.loginGoogle}
            </a>
            <p className="mt-6 font-script text-xl text-beam-glow">{copy.loginFootnote}</p>
            <p className="mt-3 text-sm opacity-70">
                <Link href="/termos" className="underline underline-offset-4">
                    {copy.terms}
                </Link>
                {' · '}
                <Link href="/privacidade" className="underline underline-offset-4">
                    {copy.privacy}
                </Link>
            </p>
        </div>
    );
}
