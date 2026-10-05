import { useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField, ChipGroup, TextareaField, TextField } from '@/Components/Ui/Fields';
import { Modal } from '@/Components/Ui/Modal';
import { t } from '@/i18n/pt-BR';

const copy = t.collaborator;

/** Errors of "areas.0" etc. are shown under the chips together with "areas". */
const areasError = (errors: Record<string, string | undefined>) =>
    errors.areas ?? Object.entries(errors).find(([key]) => key.startsWith('areas.'))?.[1];

/**
 * "Seja colaborador" under the limits of the Atlas: a quiet invitation that opens the form in a dialog.
 * The hidden "website" field is a honeypot; the server answers bots like people and stores nothing.
 */
export function CollaboratorForm() {
    const [open, setOpen] = useState(false);
    const form = useForm({
        name: '',
        email: '',
        location: '',
        areas: [] as string[],
        message: '',
        consent: false as boolean,
        website: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/colaborar', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setOpen(false);
            },
        });
    };

    return (
        <div className="mt-10 border-t-2 border-dashed border-night/15 pt-8">
            <p className="font-script text-2xl text-horizon">{copy.eyebrow}</p>
            <p className="mt-2 max-w-[48ch] leading-relaxed text-night/75">{copy.lead}</p>
            <button
                type="button"
                onClick={() => setOpen(true)}
                aria-haspopup="dialog"
                className="mt-3 inline-flex min-h-11 items-center font-semibold text-night underline decoration-beam decoration-2 underline-offset-[6px] transition-colors duration-150 hover:text-horizon"
            >
                {copy.open}
            </button>

            <Modal open={open} onClose={() => setOpen(false)} title={copy.title}>
                <p className="-mt-2 mb-6 leading-relaxed text-moonlight/75">{copy.intro}</p>
                <form onSubmit={submit} noValidate className="flex flex-col gap-5">
                    <TextField
                        tone="dark"
                        label={copy.name}
                        name="name"
                        autoComplete="name"
                        maxLength={120}
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        error={form.errors.name}
                        required
                    />
                    <TextField
                        tone="dark"
                        label={copy.email}
                        type="email"
                        name="email"
                        autoComplete="email"
                        inputMode="email"
                        maxLength={190}
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                        error={form.errors.email}
                        required
                    />
                    <TextField
                        tone="dark"
                        label={copy.location}
                        name="location"
                        autoComplete="address-level2"
                        placeholder={copy.locationPlaceholder}
                        maxLength={120}
                        value={form.data.location}
                        onChange={(e) => form.setData('location', e.target.value)}
                        error={form.errors.location}
                        required
                    />
                    <ChipGroup
                        multiple
                        tone="dark"
                        label={copy.areas}
                        options={copy.areaOptions}
                        value={form.data.areas}
                        onChange={(areas) => form.setData('areas', areas)}
                        error={areasError(form.errors as Record<string, string | undefined>)}
                    />
                    <TextareaField
                        tone="dark"
                        label={copy.message}
                        name="message"
                        placeholder={copy.messagePlaceholder}
                        rows={4}
                        maxLength={2000}
                        showCount
                        value={form.data.message}
                        onChange={(e) => form.setData('message', e.target.value)}
                        error={form.errors.message}
                        required
                    />
                    <div aria-hidden className="absolute -left-[9999px] h-px w-px overflow-hidden">
                        <label>
                            {copy.honeypot}
                            <input
                                type="text"
                                name="website"
                                tabIndex={-1}
                                autoComplete="off"
                                value={form.data.website}
                                onChange={(e) => form.setData('website', e.target.value)}
                            />
                        </label>
                    </div>
                    <CheckboxField
                        tone="dark"
                        name="consent"
                        checked={form.data.consent}
                        onChange={(e) => form.setData('consent', e.target.checked)}
                        label={copy.consent}
                        error={form.errors.consent}
                    />
                    <Button type="submit" size="md" tone="dark" className="self-start" loading={form.processing}>
                        {form.processing ? copy.sending : copy.submit}
                    </Button>
                </form>
            </Modal>
        </div>
    );
}
