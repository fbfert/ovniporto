import { motion } from 'motion/react';
import { SaucerShape } from '@/Components/Scene/Art';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Starfield } from '@/Components/Scene/Starfield';
import { Button } from '@/Components/Ui/Button';
import { Display } from '@/Components/Ui/Typography';
import { usePrefersReducedMotion } from '@/hooks/usePrefersReducedMotion';
import { t } from '@/i18n/pt-BR';
import { ease } from '@/lib/motion';

const copy = t.report.sent;

/** The saucer takes the report up: one slow rise, then it hovers. */
export default function Submitted() {
    const reduced = usePrefersReducedMotion();

    return (
        <div
            data-tone="dark"
            className="relative flex min-h-svh items-center justify-center overflow-hidden bg-night px-5 text-moonlight"
        >
            <SeoHead />
            <Starfield className="absolute inset-0" density="medium" />
            <div className="relative z-10 max-w-lg text-center">
                <motion.svg
                    viewBox="-70 -50 140 100"
                    className="mx-auto w-40"
                    aria-hidden
                    initial={reduced ? false : { transform: 'translateY(120px)', opacity: 0 }}
                    animate={{ transform: 'translateY(0px)', opacity: 1 }}
                    transition={{ duration: 1.4, ease: ease.glide }}
                >
                    <g className="animate-hover-bob">
                        <SaucerShape lightsClassName="animate-blink" />
                    </g>
                </motion.svg>
                <Display as="h1" className="mt-8 text-[clamp(1.6rem,1rem+3vw,2.8rem)]! text-balance">
                    {copy.title}
                </Display>
                <p className="mt-5 text-lg leading-relaxed text-moonlight/85">{copy.lead}</p>
                <p className="mt-2 text-moonlight/70">{copy.email}</p>
                <div className="mt-10 flex flex-wrap justify-center gap-3">
                    <Button href="/conta?aba=relatos">{copy.mine}</Button>
                    <Button href="/" variant="secondary" tone="dark">
                        {copy.home}
                    </Button>
                </div>
            </div>
        </div>
    );
}
