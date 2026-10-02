import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { WaitlistForm } from '@/Components/Home/WaitlistForm';
import { useCommunityChannels } from '@/Components/Layout/community';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.communityPage;

interface Rule {
    title: string;
    body: string;
}

export default function Community({ rules }: { rules: Rule[] }) {
    const social = useCommunityChannels().filter((channel) => channel.key !== 'email');

    return (
        <>
            <SeoHead title={copy.title} description={copy.description} />
            <PageCover eyebrow={copy.eyebrow} title={copy.title} />

            <Section tone="light" labelledBy="regras">
                <Eyebrow>{copy.rulesEyebrow}</Eyebrow>
                <Display as="h2" id="regras" className="mt-3">
                    {copy.rulesTitle}
                </Display>
                <Reveal stagger as="ol" className="mt-12 max-w-4xl border-t-2 border-dashed border-night/15">
                    {rules.map((rule, index) => (
                        <RevealItem
                            as="li"
                            key={rule.title}
                            className="grid grid-cols-[3.5rem_minmax(0,1fr)] gap-4 border-b-2 border-dashed border-night/15 py-7 sm:grid-cols-[5.5rem_minmax(0,1fr)]"
                        >
                            <Display as="span" outlined className="text-4xl leading-none text-horizon sm:text-5xl">
                                {String(index + 1).padStart(2, '0')}
                            </Display>
                            <div>
                                <h3 className="font-display text-lg leading-tight font-bold tracking-[0.03em] uppercase">
                                    {rule.title}
                                </h3>
                                <p className="mt-2 max-w-[56ch] text-lg leading-relaxed text-night/75">{rule.body}</p>
                            </div>
                        </RevealItem>
                    ))}
                </Reveal>
            </Section>

            <Section tone="dark" pattern="stars" wave labelledBy="canais">
                <div className="grid gap-16 lg:grid-cols-2 lg:gap-20">
                    <div>
                        <Display as="h2" id="canais" className="text-[clamp(1.6rem,1rem+2.4vw,2.6rem)]!">
                            {copy.channelsTitle}
                        </Display>
                        <ul className="mt-8 flex flex-col gap-4">
                            {social.map((channel) => (
                                <li key={channel.key}>
                                    {channel.href ? (
                                        <Button
                                            href={channel.href}
                                            external
                                            size="lg"
                                            variant="secondary"
                                            tone="dark"
                                            iconLeft={channel.icon}
                                        >
                                            {channel.label}
                                        </Button>
                                    ) : (
                                        <span className="inline-flex h-14 items-center gap-3 rounded-full border-[1.5px] border-dashed border-moonlight/30 px-8 font-semibold text-moonlight/60">
                                            {channel.icon}
                                            {channel.label}
                                            <Badge tone="neutral">{copy.channelSoon}</Badge>
                                        </span>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </div>
                    <div className="rounded-[26px] bg-night-blue/70 p-6 ring-1 ring-moonlight/10 sm:p-8">
                        <h2 className="font-display text-xl font-bold tracking-[0.03em] uppercase">
                            {copy.waitlistTitle}
                        </h2>
                        <p className="mt-2 mb-6 max-w-[46ch] text-moonlight/75">{copy.waitlistLead}</p>
                        <WaitlistForm source="comunidade" />
                    </div>
                </div>
            </Section>
        </>
    );
}

Community.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
