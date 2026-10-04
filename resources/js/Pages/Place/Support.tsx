import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { WaitlistForm } from '@/Components/Home/WaitlistForm';
import { StarIcon } from '@/Components/Icons';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.supportPage;

interface Sponsor {
    name: string;
    tier: string | null;
    url: string | null;
    logo: string | null;
}

/** Built on the server by CampaignPublicView: `open` only exists while the campaign is open. */
interface Campaign {
    status: 'planning' | 'open' | 'closed';
    storeSharePercent: number | null;
    open?: {
        goalCents: number | null;
        raisedCents: number;
        crowdfundingUrl: string | null;
        platform: string | null;
        supporters: string[];
        sponsors: Sponsor[];
    };
}

function Scoreboard({ open }: { open: NonNullable<Campaign['open']> }) {
    const ratio = open.goalCents ? Math.min(1, open.raisedCents / open.goalCents) : 0;
    return (
        <div className="rounded-[26px] bg-night-blue p-6 text-moonlight ring-1 ring-moonlight/10 sm:p-8">
            <p className="flex flex-wrap items-baseline gap-x-3">
                <span className="font-display text-[clamp(2rem,1.4rem+2.4vw,3rem)] font-extrabold text-car">
                    {money(open.raisedCents)}
                </span>
                <span className="text-moonlight/70">
                    {copy.raised}
                    {open.goalCents ? ` ${copy.goal(money(open.goalCents))}` : ''}
                </span>
            </p>
            <div
                role="progressbar"
                aria-label={copy.progressLabel}
                aria-valuemin={0}
                aria-valuemax={100}
                aria-valuenow={Math.round(ratio * 100)}
                className="mt-5 h-3 overflow-hidden rounded-full bg-moonlight/10"
            >
                <div
                    className="h-full origin-left rounded-full bg-beam transition-transform duration-1000 ease-snap"
                    style={{ transform: `scaleX(${ratio})` }}
                />
            </div>
            {open.crowdfundingUrl && (
                <div className="mt-8">
                    <Button href={open.crowdfundingUrl} external variant="car" size="lg">
                        {open.platform ? copy.supportOn(open.platform) : copy.supportCta}
                    </Button>
                </div>
            )}
        </div>
    );
}

function SupportersWall({ names, sponsors }: { names: string[]; sponsors: Sponsor[] }) {
    return (
        <Section tone="dark" pattern="stars" wave labelledBy="muro">
            <Display as="h2" id="muro">
                {copy.wallTitle}
            </Display>
            {names.length === 0 ? (
                <p className="mt-6 font-script text-2xl text-beam-glow">{copy.wallEmpty}</p>
            ) : (
                <ul className="mt-10 flex flex-wrap gap-3">
                    {names.map((name, i) => (
                        <li
                            key={`${name}-${i}`}
                            className="rounded-full bg-moonlight/8 px-4 py-2 font-semibold ring-1 ring-moonlight/15"
                        >
                            {name}
                        </li>
                    ))}
                </ul>
            )}
            {sponsors.length > 0 && (
                <>
                    <h3 className="mt-16 text-[0.7rem] font-semibold tracking-[0.12em] text-moonlight/60 uppercase">
                        {copy.sponsorsTitle}
                    </h3>
                    <ul className="mt-5 flex flex-wrap items-center gap-8">
                        {sponsors.map((sponsor) => {
                            const label = sponsor.logo ? (
                                <img src={sponsor.logo} alt={sponsor.name} className="h-12 w-auto object-contain" />
                            ) : (
                                <span className="font-display font-bold tracking-[0.03em] uppercase">
                                    {sponsor.name}
                                </span>
                            );
                            return (
                                <li key={sponsor.name}>
                                    {sponsor.url ? (
                                        <a href={sponsor.url} rel="noopener" className="block">
                                            {label}
                                        </a>
                                    ) : (
                                        label
                                    )}
                                </li>
                            );
                        })}
                    </ul>
                </>
            )}
        </Section>
    );
}

function HowItWorks() {
    return (
        <>
            <Eyebrow>{copy.howEyebrow}</Eyebrow>
            <Display as="h2" id="como" className="mt-3">
                {copy.howTitle}
            </Display>
            <Reveal stagger as="ol" className="mt-10 grid gap-8 md:grid-cols-3">
                {copy.how.map((item, i) => (
                    <RevealItem as="li" key={item.title} className="border-t-2 border-dashed border-night/15 pt-6">
                        <Display as="span" outlined className="text-4xl leading-none text-horizon">
                            {String(i + 1).padStart(2, '0')}
                        </Display>
                        <h3 className="mt-4 font-display text-lg font-bold tracking-[0.03em] uppercase">
                            {item.title}
                        </h3>
                        <p className="mt-2 max-w-[34ch] leading-relaxed text-night/75">{item.body}</p>
                    </RevealItem>
                ))}
            </Reveal>
        </>
    );
}

/** What supporters get, as admission stubs: punched edges and a dashed tear line. */
function Rewards() {
    return (
        <>
            <Eyebrow tone="dark">{copy.rewardsEyebrow}</Eyebrow>
            <Display as="h2" id="recompensas" className="mt-3">
                {copy.rewardsTitle}
            </Display>
            <Reveal stagger as="ul" className="mt-10 grid gap-6 md:grid-cols-3">
                {copy.rewards.map((reward, i) => (
                    <RevealItem
                        as="li"
                        key={reward.title}
                        className="flex h-full flex-col rounded-[18px] bg-night-blue ticket-punch [--punch-y:30%]"
                    >
                        <div className="flex items-center justify-between px-6 pt-6 pb-5">
                            <span className="text-[0.7rem] font-semibold tracking-[0.14em] text-moonlight/60 uppercase">
                                {copy.rewardsLabel} · {String(i + 1).padStart(2, '0')}
                            </span>
                            <StarIcon size="1.1rem" className="text-car" />
                        </div>
                        <div className="mx-5 border-t-2 border-dashed border-moonlight/15" />
                        <div className="flex-1 px-6 pt-5 pb-7">
                            <h3 className="font-display text-lg leading-tight font-bold tracking-[0.03em] uppercase">
                                {reward.title}
                            </h3>
                            <p className="mt-2 leading-relaxed text-moonlight/75">{reward.body}</p>
                        </div>
                    </RevealItem>
                ))}
            </Reveal>
        </>
    );
}

export default function Support({ campaign }: { campaign: Campaign }) {
    const share = campaign.storeSharePercent;

    return (
        <>
            <SeoHead />
            <PageCover eyebrow={copy.eyebrow} title={copy.title}>
                <div className="mt-6 flex justify-center">
                    {campaign.status === 'planning' && <Badge tone="car">{copy.planningBadge}</Badge>}
                    {campaign.status === 'closed' && <Badge tone="neutral">{copy.closedBadge}</Badge>}
                </div>
            </PageCover>

            <Section tone="light" labelledBy="como">
                {campaign.open ? (
                    <div className="mb-20">
                        <Scoreboard open={campaign.open} />
                    </div>
                ) : (
                    <div className="mb-16 max-w-3xl space-y-4 text-[clamp(1.2rem,1rem+0.8vw,1.6rem)] leading-snug font-medium">
                        {(campaign.status === 'closed' ? [copy.closedLead] : copy.honest).map((line) => (
                            <p key={line}>{line}</p>
                        ))}
                    </div>
                )}
                {share !== null && (
                    <p className="mb-14 inline-flex rounded-full bg-car px-5 py-2 font-semibold text-night">
                        {copy.storeShare(share.toLocaleString('pt-BR'))}
                    </p>
                )}
                <HowItWorks />
            </Section>

            <Section tone="dark" wave labelledBy="recompensas">
                <Rewards />
            </Section>

            {campaign.open && <SupportersWall names={campaign.open.supporters} sponsors={campaign.open.sponsors} />}

            <Section tone="light" wave labelledBy="avise-me">
                <div className="mx-auto max-w-2xl text-center">
                    <h2
                        id="avise-me"
                        className="font-display text-[clamp(1.5rem,1.1rem+1.6vw,2.2rem)] font-extrabold tracking-[0.03em] uppercase"
                    >
                        {copy.notifyTitle}
                    </h2>
                    <p className="mx-auto mt-3 max-w-[44ch] text-lg text-night/75">{copy.notifyLead}</p>
                    <div className="mt-8 rounded-[26px] bg-night p-6 text-left sm:p-8" data-tone="dark">
                        <WaitlistForm source="apoie" />
                    </div>
                    <div className="mt-8">
                        <Button href="/loja" variant="secondary">
                            {copy.sticker}
                        </Button>
                    </div>
                </div>
            </Section>
        </>
    );
}

Support.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
