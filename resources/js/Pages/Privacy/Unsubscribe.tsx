import { useForm, usePage } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { NightBanner } from '@/Components/Scene/NightBanner';
import { Button } from '@/Components/Ui/Button';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.unsubscribe;

type Props = {
    list: string;
    /** Masked e-mail of the sign-up; null when there is nothing left to cancel. */
    email: string | null;
    done: boolean;
};

/** From the link in the e-mail: show what will be canceled, cancel only on confirmation. */
export default function Unsubscribe({ list, email, done }: Props) {
    const { url } = usePage();
    const form = useForm({});

    const submit = (event: FormEvent) => {
        event.preventDefault();
        // The signed URL, query included, is what authorizes the POST.
        form.post(url);
    };

    const [title, lead] = done
        ? [copy.doneTitle, copy.doneLead]
        : email === null
          ? [copy.nothingTitle, copy.nothingLead]
          : [copy.confirmTitle, copy.confirmLead(copy.lists[list] ?? list, email)];

    return (
        <>
            <SeoHead />
            <NightBanner>
                <Eyebrow tone="dark">{copy.eyebrow}</Eyebrow>
                <Display as="h1" className="mt-3 text-balance">
                    {title}
                </Display>
                <p className="mx-auto mt-6 max-w-[42ch] text-lg text-moonlight/80">{lead}</p>
                {!done && email !== null ? (
                    <form onSubmit={submit} className="mt-10 flex flex-wrap items-center justify-center gap-4">
                        <Button type="submit" size="lg" loading={form.processing}>
                            {copy.confirm}
                        </Button>
                        <Button href="/" variant="ghost" tone="dark">
                            {copy.keep}
                        </Button>
                    </form>
                ) : (
                    <div className="mt-10">
                        <Button href="/">{copy.back}</Button>
                    </div>
                )}
            </NightBanner>
        </>
    );
}

Unsubscribe.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
