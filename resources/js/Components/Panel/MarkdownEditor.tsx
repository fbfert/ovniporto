import { useState } from 'react';
import { Prose } from '@/Components/Content/Prose';
import { TextareaField } from '@/Components/Ui/Fields';
import { t } from '@/i18n/pt-BR';
import { postJson } from '@/lib/http';

const copy = t.panel.content;

export type PreviewRenderer = (markdown: string) => Promise<string>;

/** The panel's preview endpoint uses the same renderer as the public pages. */
export const serverPreview: PreviewRenderer = async (markdown) =>
    (await postJson<{ html: string }>('/painel/previa', { markdown })).html;

/**
 * Markdown with an "Editar / Prévia" switch. The preview is rendered on the
 * server, so what the admin sees is exactly what the site will show.
 */
export function MarkdownEditor({
    label,
    value,
    onChange,
    error,
    rows = 8,
    render = serverPreview,
}: {
    label: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
    rows?: number;
    render?: PreviewRenderer;
}) {
    const [preview, setPreview] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);

    const showPreview = async () => {
        setLoading(true);
        try {
            setPreview(await render(value));
        } finally {
            setLoading(false);
        }
    };

    const tab =
        'min-h-11 rounded-full px-4 text-sm font-semibold aria-pressed:bg-night aria-pressed:text-moonlight ring-1 ring-night/20';

    return (
        <div>
            <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
                <span className="text-sm font-semibold">{label}</span>
                <span className="flex gap-1.5">
                    <button
                        type="button"
                        className={tab}
                        aria-pressed={preview === null}
                        onClick={() => setPreview(null)}
                    >
                        {copy.edit}
                    </button>
                    <button
                        type="button"
                        className={tab}
                        aria-pressed={preview !== null}
                        onClick={showPreview}
                        disabled={loading}
                    >
                        {copy.preview}
                    </button>
                </span>
            </div>
            {preview === null ? (
                <TextareaField
                    label={label}
                    hideLabel
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    error={error}
                    rows={rows}
                />
            ) : (
                <div
                    aria-live="polite"
                    className="min-h-28 rounded-[22px] bg-moonlight p-5 ring-1 ring-night/15"
                    data-testid="markdown-preview"
                >
                    {preview.trim() === '' ? (
                        <p className="font-script text-xl text-horizon">{copy.previewEmpty}</p>
                    ) : (
                        <Prose html={preview} />
                    )}
                </div>
            )}
            <p className="mt-1.5 text-xs text-night/60">{copy.markdownHint}</p>
        </div>
    );
}
