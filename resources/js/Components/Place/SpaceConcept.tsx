import { ConceptImage } from '@/Components/Ui/ConceptImage';
import { hasConcept } from '@/Components/Ui/Picture';
import { Badge } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import type { PlaceSpace } from '@/types';

/**
 * A planned space's art: a bundled illustration, one uploaded in the panel, or
 * the honest "pending" frame. Either image always carries the "conceito" badge.
 */
export function SpaceConcept({
    space,
    sizes,
    className,
    pendingClassName,
}: {
    space: PlaceSpace;
    sizes: string;
    className: string;
    pendingClassName: string;
}) {
    if (hasConcept(space.concept)) {
        return <ConceptImage slug={space.concept} sizes={sizes} className={className} />;
    }
    if (space.conceptUrl) {
        return (
            <div className={`relative overflow-hidden ${className}`}>
                <img
                    src={space.conceptUrl}
                    alt={`${space.name}: ${t.concept.badge}`}
                    loading="lazy"
                    className="h-full w-full object-cover"
                />
                <Badge tone="horizon" className="absolute top-3 right-3">
                    {t.concept.badge}
                </Badge>
            </div>
        );
    }
    return (
        <div
            className={`flex items-center justify-center border-b-2 border-dashed border-night/15 bg-night/[0.03] ${pendingClassName}`}
        >
            <p className="font-script text-xl text-horizon">{t.place.conceptPending}</p>
        </div>
    );
}
