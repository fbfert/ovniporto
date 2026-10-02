import { useId, useState, type ReactNode } from 'react';
import { ChevronDownIcon } from '@/Components/Icons';

export interface AccordionItem {
    title: ReactNode;
    content: ReactNode;
}

/**
 * Question list with at most one answer open. Height animates through
 * grid-template-rows (0fr → 1fr), so nothing is measured in JS; a closed panel
 * is `inert`, out of the tab order and the accessibility tree.
 */
export function Accordion({
    items,
    numbered = false,
    headingLevel = 3,
    className = '',
}: {
    items: AccordionItem[];
    numbered?: boolean;
    headingLevel?: 2 | 3;
    className?: string;
}) {
    const [open, setOpen] = useState<number | null>(null);
    const baseId = useId();
    const Heading = `h${headingLevel}` as const;

    return (
        <div
            className={`divide-y-2 divide-dashed divide-current/15 border-y-2 border-dashed border-current/15 ${className}`}
        >
            {items.map((item, index) => {
                const expanded = open === index;
                const buttonId = `${baseId}-q${index}`;
                const panelId = `${baseId}-a${index}`;
                return (
                    <div key={index}>
                        <Heading>
                            <button
                                id={buttonId}
                                type="button"
                                aria-expanded={expanded}
                                aria-controls={panelId}
                                onClick={() => setOpen(expanded ? null : index)}
                                className="group flex min-h-16 w-full items-center gap-4 py-4 text-left font-semibold"
                            >
                                {numbered && (
                                    <span
                                        aria-hidden
                                        className="w-14 shrink-0 font-display text-[1.6rem] leading-none font-extrabold whitespace-nowrap text-horizon text-outline"
                                    >
                                        {String(index + 1).padStart(2, '0')}
                                    </span>
                                )}
                                <span className="flex-1 text-[1.05rem] leading-snug">{item.title}</span>
                                <span className="inline-flex size-9 shrink-0 items-center justify-center rounded-full ring-1 ring-current/20 transition-[transform,background-color] duration-200 ease-snap group-aria-expanded:rotate-180 group-aria-expanded:bg-beam group-aria-expanded:text-night group-aria-expanded:ring-beam">
                                    <ChevronDownIcon size="1.05rem" />
                                </span>
                            </button>
                        </Heading>
                        <div
                            id={panelId}
                            role="region"
                            aria-labelledby={buttonId}
                            inert={!expanded}
                            className={`grid transition-[grid-template-rows,opacity] duration-[220ms] ease-snap ${
                                expanded ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'
                            }`}
                        >
                            <div className="overflow-hidden">
                                <div className={`pb-6 ${numbered ? 'pl-[4.5rem]' : ''}`}>{item.content}</div>
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}
