import { router, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { AgreementChecks, type Agreement } from '@/Components/Members/AgreementChecks';
import { Button } from '@/Components/Ui/Button';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { longDate, t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.members.termsUpdate;

type Props = {
    version: string;
    /** What the member still has to confirm: new terms, the age declaration, or both. */
    pending: Agreement[];
};

/** The terms changed or the age was never declared: one screen with only the pending boxes. */
export default function AcceptTerms({ version, pending }: Props) {
    const form = useForm({ terms: false as boolean, age: false as boolean });
    const termsPending = pending.includes('terms');
    const dated = /^\d{4}-\d{2}-\d{2}$/.test(version);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/termos/aceitar');
    };

    return (
        <>
            <SeoHead />
            <Section tone="dark" pattern="stars" className="min-h-svh" innerClassName="pt-32! sm:pt-36! max-w-2xl!">
                <form onSubmit={submit} noValidate>
                    <Eyebrow tone="dark">{copy.eyebrow}</Eyebrow>
                    <Display as="h1" className="mt-3 text-[clamp(1.9rem,1rem+3.6vw,3.4rem)]! text-balance">
                        {termsPending ? copy.title : copy.ageOnlyTitle}
                    </Display>
                    <p className="mt-5 max-w-[46ch] text-lg leading-relaxed text-moonlight/80">
                        {!termsPending ? copy.ageOnlyLead : dated ? copy.lead(longDate(version)) : copy.leadInitial}
                    </p>

                    <div className="mt-8">
                        <AgreementChecks
                            ask={pending}
                            values={form.data}
                            errors={form.errors}
                            onChange={(agreement, checked) => form.setData(agreement, checked)}
                        />
                    </div>

                    <div className="mt-8 flex flex-wrap items-center gap-4">
                        <Button type="submit" size="lg" loading={form.processing}>
                            {termsPending ? copy.accept : copy.confirm}
                        </Button>
                        <Button variant="ghost" tone="dark" onClick={() => router.post('/sair')}>
                            {copy.notNow}
                        </Button>
                    </div>
                </form>
            </Section>
        </>
    );
}

AcceptTerms.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
