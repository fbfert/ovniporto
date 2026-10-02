import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.content;

/** The six content screens as numbered stops, the same 01/02/03 the public site uses. */
export default function Hub() {
    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.title}
            </Display>
            <p className="mt-3 max-w-[56ch] text-night/70">{copy.lead}</p>
            <ol className="mt-10 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                {copy.sections.map((section, i) => (
                    <li key={section.href}>
                        <Link
                            href={section.href}
                            className="group grid grid-cols-[3.5rem_minmax(0,1fr)] items-baseline gap-4 rounded-2xl px-2 py-5 outline-none hover:bg-night/4 focus-visible:ring-2 focus-visible:ring-beam sm:grid-cols-[5rem_minmax(0,1fr)]"
                        >
                            <span className="font-display text-3xl font-extrabold text-horizon/40 group-hover:text-horizon sm:text-4xl">
                                {String(i + 1).padStart(2, '0')}
                            </span>
                            <span>
                                <span className="block font-display text-lg font-bold tracking-[0.03em] uppercase">
                                    {section.title}
                                </span>
                                <span className="mt-1 block text-night/70">{section.body}</span>
                            </span>
                        </Link>
                    </li>
                ))}
            </ol>
        </>
    );
}

Hub.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
