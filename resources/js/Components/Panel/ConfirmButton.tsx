import { useState, type ReactNode } from 'react';
import { Button, type ButtonProps } from '@/Components/Ui/Button';
import { Modal } from '@/Components/Ui/Modal';
import { t } from '@/i18n/pt-BR';

/** A destructive action that asks once, in our own dialog (never the browser's). */
export function ConfirmButton({
    title,
    confirmLabel,
    onConfirm,
    children,
    variant = 'ghost',
    size = 'sm',
    lead,
}: {
    title: string;
    confirmLabel: string;
    onConfirm: (done: () => void) => void;
    children: ReactNode;
    variant?: ButtonProps['variant'];
    size?: ButtonProps['size'];
    lead?: ReactNode;
}) {
    const [open, setOpen] = useState(false);
    const [busy, setBusy] = useState(false);
    return (
        <>
            <Button variant={variant} size={size} onClick={() => setOpen(true)}>
                {children}
            </Button>
            <Modal open={open} onClose={() => setOpen(false)} title={title}>
                {lead && <p className="text-moonlight/80">{lead}</p>}
                <div className="mt-6 flex flex-wrap gap-3">
                    <Button
                        variant="car"
                        loading={busy}
                        onClick={() => {
                            setBusy(true);
                            onConfirm(() => {
                                setBusy(false);
                                setOpen(false);
                            });
                        }}
                    >
                        {confirmLabel}
                    </Button>
                    <Button variant="ghost" tone="dark" onClick={() => setOpen(false)}>
                        {t.panel.content.cancel}
                    </Button>
                </div>
            </Modal>
        </>
    );
}
