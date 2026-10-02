import { useState } from 'react';
import { Button, type ButtonProps } from '@/Components/Ui/Button';
import { Modal } from '@/Components/Ui/Modal';
import { t } from '@/i18n/pt-BR';
import { LoginPanel } from './LoginPanel';

type Size = NonNullable<ButtonProps['size']>;

/** "Entrar na comunidade": opens the Google sign-in modal right where the visitor is. */
export function JoinButton({
    size = 'md',
    label = t.nav.join,
    className = '',
    onOpen,
}: {
    size?: Size;
    label?: string;
    className?: string;
    onOpen?: () => void;
}) {
    const [open, setOpen] = useState(false);
    return (
        <>
            <Button
                size={size}
                className={className}
                onClick={() => {
                    onOpen?.();
                    setOpen(true);
                }}
            >
                {label}
            </Button>
            <Modal open={open} onClose={() => setOpen(false)} title={t.members.loginTitle}>
                <LoginPanel />
            </Modal>
        </>
    );
}
