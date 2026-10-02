import { useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/Components/Ui/Button';
import { ChipGroup, TextareaField } from '@/Components/Ui/Fields';
import { Modal } from '@/Components/Ui/Modal';
import { t } from '@/i18n/pt-BR';
import type { SharedProps } from '@/types';

const copy = t.panel.review;

export type Dialog = 'changes' | 'reject' | 'unpublish' | null;

interface Props {
    sightingId: number;
    open: Dialog;
    onClose: () => void;
}

function ChangesForm({ sightingId, onClose }: Omit<Props, 'open'>) {
    const form = useForm({ message: '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(`/painel/relatos/${sightingId}/ajuste`, { onSuccess: onClose });
    };
    return (
        <form onSubmit={submit} className="space-y-5">
            <TextareaField
                tone="dark"
                label={copy.changesLabel}
                value={form.data.message}
                onChange={(e) => form.setData('message', e.target.value)}
                error={form.errors.message}
                maxLength={1000}
                showCount
                autoFocus
            />
            <p className="text-sm text-moonlight/70">{copy.changesHint}</p>
            <div className="flex flex-wrap gap-3">
                <Button type="submit" variant="car" loading={form.processing}>
                    {copy.changesSend}
                </Button>
                <Button variant="ghost" tone="dark" onClick={onClose}>
                    {copy.cancel}
                </Button>
            </div>
        </form>
    );
}

function RejectForm({ sightingId, onClose }: Omit<Props, 'open'>) {
    const form = useForm({ reason: '', detail: '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(`/painel/relatos/${sightingId}/rejeitar`, { onSuccess: onClose });
    };
    return (
        <form onSubmit={submit} className="space-y-5">
            <ChipGroup
                tone="dark"
                label={copy.rejectReason}
                options={copy.rejectReasons}
                value={form.data.reason || null}
                onChange={(reason) => form.setData('reason', reason)}
                error={form.errors.reason}
            />
            {form.data.reason === 'other' && (
                <TextareaField
                    tone="dark"
                    label={copy.rejectDetail}
                    value={form.data.detail}
                    onChange={(e) => form.setData('detail', e.target.value)}
                    error={form.errors.detail}
                    maxLength={500}
                    rows={3}
                />
            )}
            <div className="flex flex-wrap gap-3">
                <Button type="submit" variant="car" loading={form.processing}>
                    {copy.rejectSend}
                </Button>
                <Button variant="ghost" tone="dark" onClick={onClose}>
                    {copy.cancel}
                </Button>
            </div>
        </form>
    );
}

function UnpublishForm({ sightingId, onClose }: Omit<Props, 'open'>) {
    const form = useForm({ note: '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(`/painel/relatos/${sightingId}/despublicar`, { onSuccess: onClose });
    };
    return (
        <form onSubmit={submit} className="space-y-5">
            <p className="text-moonlight/80">{copy.unpublishLead}</p>
            <TextareaField
                tone="dark"
                label={copy.unpublishLabel}
                value={form.data.note}
                onChange={(e) => form.setData('note', e.target.value)}
                error={form.errors.note}
                maxLength={500}
                rows={3}
                autoFocus
            />
            <div className="flex flex-wrap gap-3">
                <Button type="submit" variant="car" loading={form.processing}>
                    {copy.unpublishSend}
                </Button>
                <Button variant="ghost" tone="dark" onClick={onClose}>
                    {copy.cancel}
                </Button>
            </div>
        </form>
    );
}

const TITLES: Record<Exclude<Dialog, null>, string> = {
    changes: copy.changesTitle,
    reject: copy.rejectTitle,
    unpublish: copy.unpublishTitle,
};

/** The three decisions that need words: each one a small form in a dialog. */
export function DecisionDialogs({ sightingId, open, onClose }: Props) {
    // The state machine refusing the step (someone else decided first) comes back as "status".
    const { errors } = usePage<SharedProps>().props;
    return (
        <Modal open={open !== null} onClose={onClose} title={open ? TITLES[open] : ''}>
            {errors.status && (
                <p role="alert" className="mb-5 rounded-xl bg-car px-4 py-2 font-semibold text-night">
                    {errors.status}
                </p>
            )}
            {open === 'changes' && <ChangesForm sightingId={sightingId} onClose={onClose} />}
            {open === 'reject' && <RejectForm sightingId={sightingId} onClose={onClose} />}
            {open === 'unpublish' && <UnpublishForm sightingId={sightingId} onClose={onClose} />}
        </Modal>
    );
}
