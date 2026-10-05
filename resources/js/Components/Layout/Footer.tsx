import { Link } from '@inertiajs/react';
import { Seal } from '@/Components/Brand/Seal';
import { SaucerShape } from '@/Components/Scene/Art';
import { Section } from '@/Components/Ui/Section';
import { Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { useCommunityChannels } from './community';
import { allLinks } from './nav';

const MAP_URL = 'https://www.openstreetmap.org/?mlat=-27.85495&mlon=-50.21841#map=14/-27.85495/-50.21841';

export function Footer() {
    const channels = useCommunityChannels();

    return (
        <footer className="relative overflow-hidden">
            <Section tone="dark" pattern="stars" wave innerClassName="pb-10!">
                <div aria-hidden className="pointer-events-none absolute top-16 left-0 w-full motion-reduce:hidden">
                    <svg viewBox="-120 -60 240 90" className="w-20 animate-flyby opacity-0">
                        <SaucerShape lightsClassName="animate-blink" />
                    </svg>
                </div>

                <Eyebrow tone="dark" className="rotate-[-3deg]! text-center text-[clamp(2.2rem,1.4rem+3.6vw,4.4rem)]!">
                    {t.brand.signoff}
                </Eyebrow>

                <div className="mt-16 grid gap-12 border-t border-moonlight/12 pt-12 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr]">
                    <div className="flex items-start gap-4 md:col-span-2 lg:col-span-1">
                        <Seal size="md" className="size-20! shrink-0" />
                        <div>
                            <p className="font-display text-xl font-extrabold tracking-[0.04em] uppercase">
                                {t.brand.name}
                            </p>
                            <p className="mt-1 text-moonlight/75">{t.brand.tagline}</p>
                            <p className="mt-1 font-script text-xl text-beam-glow">{t.brand.city}</p>
                        </div>
                    </div>
                    <nav aria-label={t.footer.explore}>
                        <h2 className="text-[0.7rem] font-semibold tracking-[0.12em] text-moonlight/60 uppercase">
                            {t.footer.explore}
                        </h2>
                        <ul className="mt-4 grid grid-cols-2 gap-x-4 gap-y-1 md:grid-cols-1">
                            {allLinks.map((link) => (
                                <li key={link.href}>
                                    <Link
                                        href={link.href}
                                        className="inline-flex min-h-9 items-center text-moonlight/85 hover:text-beam-glow"
                                    >
                                        {link.label}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </nav>
                    <div>
                        <h2 className="text-[0.7rem] font-semibold tracking-[0.12em] text-moonlight/60 uppercase">
                            {t.footer.community}
                        </h2>
                        <ul className="mt-4 space-y-1">
                            {channels.map((channel) => (
                                <li key={channel.key}>
                                    {channel.href ? (
                                        <a
                                            href={channel.href}
                                            className="inline-flex min-h-9 items-center gap-2 [overflow-wrap:anywhere] hover:text-beam-glow sm:[overflow-wrap:normal]"
                                        >
                                            {channel.icon}
                                            {channel.label}
                                        </a>
                                    ) : (
                                        <span className="inline-flex min-h-9 items-center gap-2 text-moonlight/55">
                                            {channel.icon}
                                            {channel.label} · {t.footer.soon}
                                        </span>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>

                <div className="mt-14 flex flex-col gap-4 border-t border-moonlight/12 pt-6 text-sm text-moonlight/70 md:flex-row md:items-center md:justify-between">
                    <p>
                        {t.brand.location} ·{' '}
                        <a
                            href={MAP_URL}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="whitespace-nowrap underline decoration-moonlight/40 underline-offset-4 hover:text-beam-glow"
                        >
                            {t.footer.map}
                        </a>
                    </p>
                    <p className="flex flex-wrap gap-x-5 gap-y-2">
                        <Link href="/privacidade" className="hover:text-beam-glow">
                            {t.footer.privacy}
                        </Link>
                        <Link href="/termos" className="hover:text-beam-glow">
                            {t.footer.terms}
                        </Link>
                        <span>
                            {t.footer.madeBy}{' '}
                            <a
                                href="https://xiax.com.br"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="font-semibold text-moonlight hover:text-beam-glow"
                            >
                                Xiax
                            </a>
                        </span>
                    </p>
                </div>
            </Section>
        </footer>
    );
}
