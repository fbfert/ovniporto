import { Link } from '@inertiajs/react';
import { CheckboxField } from '@/Components/Ui/Fields';
import { t } from '@/i18n/pt-BR';

const copy = t.members;

export type Agreement = 'terms' | 'age';

type Props = {
    /** Which confirmations to ask for, in this order. */
    ask: readonly Agreement[];
    values: Record<Agreement, boolean>;
    errors: Partial<Record<Agreement, string>>;
    onChange: (agreement: Agreement, checked: boolean) => void;
};

/** The yeses a member gives before using the account: the terms and being 16 or older. */
export function AgreementChecks({ ask, values, errors, onChange }: Props) {
    return (
        <div className="space-y-3">
            {ask.includes('terms') && (
                <CheckboxField
                    tone="dark"
                    name="terms"
                    checked={values.terms}
                    onChange={(event) => onChange('terms', event.target.checked)}
                    error={errors.terms}
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
            )}
            {ask.includes('age') && (
                <CheckboxField
                    tone="dark"
                    name="age"
                    checked={values.age}
                    onChange={(event) => onChange('age', event.target.checked)}
                    error={errors.age}
                    label={
                        <>
                            {copy.ageConfirm} <span className="block text-moonlight/60">{copy.ageHint}</span>
                        </>
                    }
                />
            )}
        </div>
    );
}
