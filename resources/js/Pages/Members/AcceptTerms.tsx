import { Link, router, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField } from '@/Components/Ui/Fields';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { longDate, t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.members;

/** The terms were updated since the member last agreed: same checkbox as the welcome screen. */
export default function AcceptTerms({ version }: { version: string }) {
    const form = useForm({ terms: false as boolean });
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
                    <Eyebrow tone="dark">{copy.termsUpdate.eyebrow}</Eyebrow>
                    <Display as="h1" className="mt-3 text-[clamp(1.9rem,1rem+3.6vw,3.4rem)]! text-balance">
                        {copy.termsUpdate.title}
                    </Display>
                    <p className="mt-5 max-w-[46ch] text-lg leading-relaxed text-moonlight/80">
                        {dated ? copy.termsUpdate.lead(longDate(version)) : copy.termsUpdate.leadInitial}
                    </p>

                    <div className="mt-8">
                        <CheckboxField
                            tone="dark"
                            checked={form.data.terms}
                            onChange={(event) => form.setData('terms', event.target.checked)}
                            error={form.errors.terms}
                            label={
                                <>
                                    {copy.termsLabel}{' '}
                                    <Link href="/termos" className="underline underline-offset-4">
                                        {copy.terms}
                                    </Link>
                                    {' · '}
                                    <Link href="/privacidade" className="underline underline-offset-4">
                                        {copy.privacy}
                                    </Link>
                                </>
                            }
                        />
                    </div>

                    <div className="mt-8 flex flex-wrap items-center gap-4">
                        <Button type="submit" size="lg" loading={form.processing}>
                            {copy.termsUpdate.accept}
                        </Button>
                        <Button variant="ghost" tone="dark" onClick={() => router.post('/sair')}>
                            {copy.termsUpdate.notNow}
                        </Button>
                    </div>
                </form>
            </Section>
        </>
    );
}

AcceptTerms.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
