import { Button } from '@/Components/Ui/Button';
import { Reveal } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { Postcard } from './Postcard';
import { WaitlistForm } from './WaitlistForm';

function SocialButton({ href, label }: { href: string; label: string }) {
    if (href) {
        return (
            <Button href={href} variant="secondary" tone="dark" external>
                {label}
            </Button>
        );
    }
    return (
        <span className="inline-flex h-12 items-center gap-2 rounded-full border-[1.5px] border-dashed border-moonlight/30 px-5 text-[0.95rem] font-semibold text-moonlight/60">
            {label}
            <Badge tone="neutral">{t.community.linkSoon}</Badge>
        </span>
    );
}

/** Section 09. Join the vigil, ask to be told when the campaign opens, send a postcard. */
export function CommunitySection({ whatsapp, instagram }: { whatsapp: string; instagram: string }) {
    return (
        <Section tone="dark" pattern="stars" wave labelledBy="comunidade" className="pb-10">
            <div className="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Eyebrow tone="dark">{t.community.eyebrow}</Eyebrow>
                    <Display as="h2" id="comunidade" className="mt-3">
                        {t.community.title}
                    </Display>
                </div>
                <div className="flex flex-wrap gap-3">
                    <Button href="/comunidade">{t.community.google}</Button>
                    <SocialButton href={whatsapp} label={t.community.whatsapp} />
                    <SocialButton href={instagram} label={t.community.instagram} />
                </div>
            </div>

            <div className="mt-14 grid gap-16 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-20">
                <Reveal>
                    <div className="rounded-[26px] bg-night-blue/70 p-6 ring-1 ring-moonlight/10 sm:p-8">
                        <h3 className="font-display text-xl font-bold tracking-[0.03em] uppercase">
                            {t.community.waitlistTitle}
                        </h3>
                        <p className="mt-2 mb-6 max-w-[46ch] text-moonlight/75">{t.community.waitlistLead}</p>
                        <WaitlistForm />
                    </div>
                </Reveal>

                <Reveal>
                    <h3 className="font-display text-xl font-bold tracking-[0.03em] uppercase">
                        {t.community.postcardTitle}
                    </h3>
                    <p className="mt-2 mb-10 text-moonlight/75">{t.community.postcardLead}</p>
                    <Postcard />
                </Reveal>
            </div>
        </Section>
    );
}
