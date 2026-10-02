import type { ReactNode } from 'react';
import { LoginPanel } from '@/Components/Members/LoginPanel';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Section } from '@/Components/Ui/Section';
import { Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

/** Full-page sign-in: where pages that need an account send guests (and the mobile menu). */
export default function SignIn() {
    return (
        <>
            <SeoHead title={t.members.loginTitle} />
            <Section tone="dark" pattern="stars" className="min-h-svh" innerClassName="pt-36! max-w-xl!">
                <Display as="h1" className="text-center text-[clamp(1.7rem,1rem+3vw,3rem)]! text-balance">
                    {t.members.loginTitle}
                </Display>
                <div className="mt-10 rounded-[28px] bg-night-blue/70 p-6 ring-1 ring-moonlight/10 sm:p-10">
                    <LoginPanel />
                </div>
            </Section>
        </>
    );
}

SignIn.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
