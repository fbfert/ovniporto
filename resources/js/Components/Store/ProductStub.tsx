import { RevealItem } from '@/Components/Ui/Reveal';
import { t } from '@/i18n/pt-BR';

/** Grid columns a single stub covers, so free slots read as one "coming soon" space, not repeated boxes. */
const SPAN: Record<number, string> = {
    1: '',
    2: 'lg:col-span-2',
    3: 'sm:col-span-2 lg:col-span-3',
};

/** The dashed stub that fills the free slots of a 3-column product grid. Never a fake product. */
export function ProductStub({ freeSlots, storeEmpty }: { freeSlots: number; storeEmpty: boolean }) {
    if (freeSlots <= 0) return null;

    return (
        <RevealItem as="li" className={SPAN[Math.min(freeSlots, 3)]}>
            <div className="flex h-full min-h-72 flex-col items-center justify-center gap-2 rounded-[18px] border-2 border-dashed border-moonlight/20 p-8 text-center">
                <p className="font-script text-2xl text-beam-glow">
                    {storeEmpty ? t.store.empty : t.storePage.nextStub}
                </p>
                <p className="max-w-[36ch] text-sm text-moonlight/60">{t.storePage.nextStubLead}</p>
            </div>
        </RevealItem>
    );
}
