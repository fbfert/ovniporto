import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { ConfirmButton } from '@/Components/Panel/ConfirmButton';
import { MarkdownEditor } from '@/Components/Panel/MarkdownEditor';
import { MoveButtons, PanelSection } from '@/Components/Panel/PanelSection';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField, TextField } from '@/Components/Ui/Fields';
import { Display } from '@/Components/Ui/Typography';
import { longDate, t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.settings;
const common = t.panel.content;

type Values = Record<string, string | null>;
interface Item {
    id: number;
    title: string;
    body: string;
}
type ListKey = 'faq' | 'regras';

const LEGAL: Record<string, string> = { privacy_body: 'privacy', terms_body: 'terms' };

function LinksForm({ values }: { values: Values }) {
    const form = useForm({
        link_whatsapp: values.link_whatsapp ?? '',
        link_instagram: values.link_instagram ?? '',
        contact_email: values.contact_email ?? '',
    });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put('/painel/configuracoes/links', { preserveScroll: true });
    };
    return (
        <form onSubmit={submit} className="grid gap-4">
            <TextField
                type="url"
                label={copy.whatsapp}
                value={form.data.link_whatsapp}
                onChange={(e) => form.setData('link_whatsapp', e.target.value)}
                error={form.errors.link_whatsapp}
            />
            <TextField
                type="url"
                label={copy.instagram}
                value={form.data.link_instagram}
                onChange={(e) => form.setData('link_instagram', e.target.value)}
                error={form.errors.link_instagram}
            />
            <TextField
                type="email"
                label={copy.email}
                value={form.data.contact_email}
                onChange={(e) => form.setData('contact_email', e.target.value)}
                error={form.errors.contact_email}
                required
            />
            <p className="text-sm text-night/60">{copy.linksHint}</p>
            <div>
                <Button type="submit" loading={form.processing}>
                    {common.save}
                </Button>
            </div>
        </form>
    );
}

function GoalsForm({ values }: { values: Values }) {
    const form = useForm({
        launch_date: values.launch_date ?? '',
        goal_members: values.goal_members ?? '',
        goal_sightings: values.goal_sightings ?? '',
        goal_orders: values.goal_orders ?? '',
    });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put('/painel/configuracoes/metas', { preserveScroll: true });
    };
    const number = (key: 'goal_members' | 'goal_sightings' | 'goal_orders', label: string) => (
        <TextField
            type="number"
            min={0}
            inputMode="numeric"
            label={label}
            value={form.data[key]}
            onChange={(e) => form.setData(key, e.target.value)}
            error={form.errors[key]}
        />
    );
    return (
        <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
            <TextField
                type="date"
                label={copy.launchDate}
                value={form.data.launch_date}
                onChange={(e) => form.setData('launch_date', e.target.value)}
                error={form.errors.launch_date}
            />
            {number('goal_members', copy.goalMembers)}
            {number('goal_sightings', copy.goalSightings)}
            {number('goal_orders', copy.goalOrders)}
            <div className="sm:col-span-2">
                <Button type="submit" loading={form.processing}>
                    {common.save}
                </Button>
            </div>
        </form>
    );
}

function BlockEditor({ blockKey, values }: { blockKey: string; values: Values }) {
    const [open, setOpen] = useState(false);
    const legal = LEGAL[blockKey];
    const form = useForm({ value: values[blockKey] ?? '', final: legal ? Boolean(values[`${legal}_final`]) : false });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => (legal ? data : { value: data.value }));
        form.put(`/painel/configuracoes/textos/${blockKey}`, { preserveScroll: true });
    };
    const updated = legal ? values[`${legal}_updated_at`] : null;

    return (
        <li className="py-4">
            <button
                type="button"
                onClick={() => setOpen(!open)}
                aria-expanded={open}
                className="flex min-h-11 w-full flex-wrap items-center justify-between gap-2 text-left"
            >
                <span className="font-semibold">{copy.blocks[blockKey]}</span>
                <span className="text-sm text-night/60">
                    {(values[blockKey] ?? '').trim() === ''
                        ? copy.emptyBlock
                        : updated
                          ? copy.legalUpdated(longDate(updated))
                          : ''}
                </span>
            </button>
            {open && (
                <form onSubmit={submit} className="mt-3 space-y-4">
                    <MarkdownEditor
                        label={copy.blocks[blockKey] ?? blockKey}
                        value={form.data.value}
                        onChange={(value) => form.setData('value', value)}
                        error={form.errors.value}
                        rows={legal ? 16 : 6}
                    />
                    {legal && (
                        <CheckboxField
                            label={copy.legalFinal}
                            checked={form.data.final}
                            onChange={(e) => form.setData('final', e.target.checked)}
                        />
                    )}
                    <Button type="submit" loading={form.processing}>
                        {common.save}
                    </Button>
                </form>
            )}
        </li>
    );
}

function ItemForm({ list, item, onDone }: { list: ListKey; item?: Item; onDone?: () => void }) {
    const form = useForm({ title: item?.title ?? '', body: item?.body ?? '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        const url = item ? `/painel/configuracoes/${list}/${item.id}` : `/painel/configuracoes/${list}`;
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                if (!item) form.reset();
                onDone?.();
            },
        };
        if (item) form.put(url, options);
        else form.post(url, options);
    };
    return (
        <form onSubmit={submit} className="space-y-4">
            <TextField
                label={copy.itemTitle[list] ?? ''}
                value={form.data.title}
                onChange={(e) => form.setData('title', e.target.value)}
                error={form.errors.title}
                maxLength={255}
            />
            <MarkdownEditor
                label={copy.itemBody[list] ?? ''}
                value={form.data.body}
                onChange={(body) => form.setData('body', body)}
                error={form.errors.body}
                rows={4}
            />
            <div className="flex flex-wrap gap-3">
                <Button type="submit" loading={form.processing}>
                    {item ? common.save : copy.addItem[list]}
                </Button>
                {item && onDone && (
                    <Button variant="ghost" onClick={onDone}>
                        {common.cancel}
                    </Button>
                )}
            </div>
        </form>
    );
}

function EditableList({ list, items }: { list: ListKey; items: Item[] }) {
    const [editing, setEditing] = useState<number | null>(null);
    const move = (id: number, direction: -1 | 1) =>
        router.post(`/painel/configuracoes/${list}/${id}/mover`, { direction }, { preserveScroll: true });

    return (
        <>
            {items.length === 0 ? (
                <p className="text-night/60">{copy.emptyList}</p>
            ) : (
                <ol className="divide-y-2 divide-dashed divide-night/12">
                    {items.map((item, i) => (
                        <li key={item.id} className="py-4">
                            {editing === item.id ? (
                                <ItemForm list={list} item={item} onDone={() => setEditing(null)} />
                            ) : (
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <p className="min-w-0 flex-1 font-semibold">{item.title}</p>
                                    <span className="flex flex-wrap gap-2">
                                        <MoveButtons
                                            onMove={(direction) => move(item.id, direction)}
                                            first={i === 0}
                                            last={i === items.length - 1}
                                            labels={{ up: common.moveUp, down: common.moveDown }}
                                        />
                                        <Button variant="secondary" size="sm" onClick={() => setEditing(item.id)}>
                                            {common.edit}
                                        </Button>
                                        <ConfirmButton
                                            title={common.confirmRemove}
                                            confirmLabel={common.remove}
                                            lead={item.title}
                                            onConfirm={(done) =>
                                                router.delete(`/painel/configuracoes/${list}/${item.id}`, {
                                                    preserveScroll: true,
                                                    onFinish: done,
                                                })
                                            }
                                        >
                                            {common.remove}
                                        </ConfirmButton>
                                    </span>
                                </div>
                            )}
                        </li>
                    ))}
                </ol>
            )}
            <div className="mt-6 border-t-2 border-dashed border-night/12 pt-6">
                <ItemForm list={list} />
            </div>
        </>
    );
}

export default function Settings({ values, faq, regras }: { values: Values; faq: Item[]; regras: Item[] }) {
    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Link href="/painel/conteudo" className="min-h-11 font-semibold underline underline-offset-4">
                ← {common.back}
            </Link>
            <Display as="h1" className="mt-6 text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.title}
            </Display>

            <div className="mt-10 grid gap-8 lg:grid-cols-2">
                <PanelSection title={copy.linksTitle}>
                    <LinksForm values={values} />
                </PanelSection>
                <PanelSection title={copy.goalsTitle}>
                    <GoalsForm values={values} />
                </PanelSection>
            </div>

            <PanelSection title={copy.blocksTitle} className="mt-8">
                <ul className="divide-y-2 divide-dashed divide-night/12">
                    {Object.keys(copy.blocks).map((key) => (
                        <BlockEditor key={key} blockKey={key} values={values} />
                    ))}
                </ul>
            </PanelSection>

            <div className="mt-8 grid gap-8 lg:grid-cols-2">
                <PanelSection title={copy.faqTitle}>
                    <EditableList list="faq" items={faq} />
                </PanelSection>
                <PanelSection title={copy.rulesTitle}>
                    <EditableList list="regras" items={regras} />
                </PanelSection>
            </div>
        </>
    );
}

Settings.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
