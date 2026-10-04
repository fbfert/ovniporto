import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField, TextField } from '@/Components/Ui/Fields';
import { t } from '@/i18n/pt-BR';
import { track } from '@/lib/analytics';

/** "Avise-me da campanha": e-mail + explicit consent, double opt-in on the server. */
export function WaitlistForm({ source = 'home', tone = 'dark' }: { source?: string; tone?: 'light' | 'dark' }) {
    const form = useForm({ email: '', consent: false as boolean, source });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/avise-me', {
            preserveScroll: true,
            onSuccess: () => {
                track('avise_me', { origem: source });
                form.reset('email', 'consent');
            },
        });
    };

    return (
        <form onSubmit={submit} noValidate className="flex flex-col gap-3">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start">
                <TextField
                    className="flex-1"
                    tone={tone}
                    label={t.community.emailLabel}
                    hideLabel
                    type="email"
                    name="email"
                    autoComplete="email"
                    inputMode="email"
                    placeholder={t.community.emailPlaceholder}
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                    error={form.errors.email}
                    required
                />
                <Button type="submit" size="md" className="h-12 shrink-0" loading={form.processing}>
                    {form.processing ? t.community.sending : t.community.submit}
                </Button>
            </div>
            <CheckboxField
                tone={tone}
                name="consent"
                checked={form.data.consent}
                onChange={(e) => form.setData('consent', e.target.checked)}
                label={t.community.consent}
                error={form.errors.consent}
            />
        </form>
    );
}
