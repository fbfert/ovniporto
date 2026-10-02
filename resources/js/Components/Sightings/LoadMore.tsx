import { useState } from 'react';
import { Button } from '@/Components/Ui/Button';
import { t } from '@/i18n/pt-BR';

/** "Carregar mais": asks for the next page; disappears when there is nothing left. */
export function LoadMore({
    page,
    hasMore,
    onLoad,
}: {
    page: number;
    hasMore: boolean;
    onLoad: (nextPage: number, done: () => void) => void;
}) {
    const [loading, setLoading] = useState(false);
    if (!hasMore) return null;

    return (
        <div className="mt-12 flex justify-center">
            <Button
                variant="secondary"
                tone="dark"
                size="lg"
                loading={loading}
                onClick={() => {
                    setLoading(true);
                    onLoad(page + 1, () => setLoading(false));
                }}
            >
                {t.logbookPage.loadMore}
            </Button>
        </div>
    );
}
