import type { ReactNode } from 'react';
import { ARAUCARIAS, AraucariaShape, SERRA } from '@/Components/Scene/Art';
import { Starfield } from '@/Components/Scene/Starfield';

/** Short night cover used by inner pages: sky, stars and the serra skyline at the bottom. */
export function NightBanner({ children, tall = false }: { children: ReactNode; tall?: boolean }) {
    return (
        <section
            data-tone="dark"
            className={`relative overflow-hidden bg-night text-moonlight ${tall ? 'min-h-svh' : 'min-h-[78svh]'} flex items-center`}
        >
            <div className="absolute inset-0 bg-[radial-gradient(120%_80%_at_50%_0%,var(--color-night-blue)_0%,var(--color-night)_62%)]" />
            <Starfield className="absolute inset-0" density="medium" />
            <div className="absolute inset-x-0 bottom-0 h-1/2 bg-[linear-gradient(to_bottom,transparent,rgb(73_67_131/0.38))]" />
            <svg aria-hidden viewBox="0 600 1600 400" preserveAspectRatio="xMidYMax slice" className="absolute inset-x-0 bottom-0 h-[38%] w-full">
                <path d={SERRA.far} fill="var(--color-night-blue)" />
                <path d={SERRA.near} fill="var(--color-night)" />
                {ARAUCARIAS.near.map((tree) => (
                    <g key={tree.x} transform={`translate(${tree.x} ${tree.y})`}>
                        <AraucariaShape height={tree.h * 0.8} seed={tree.seed} />
                    </g>
                ))}
            </svg>
            <div className="relative z-[2] mx-auto w-full max-w-4xl px-5 pt-32 pb-40 text-center sm:px-8">{children}</div>
        </section>
    );
}
