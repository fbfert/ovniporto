import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { Button } from '@/Components/Ui/Button';
import { SelectField, TextareaField } from '@/Components/Ui/Fields';
import { Modal } from '@/Components/Ui/Modal';
import { Badge, Display } from '@/Components/Ui/Typography';
import { longDate, shortDate, t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.members;

interface MemberDetail {
    id: number;
    nickname: string | null;
    name: string;
    email: string;
    avatarUrl: string | null;
    city: string | null;
    role: string;
    joinedAt: string;
    termsAcceptedAt: string | null;
    blockedAt: string | null;
    blockedReason: string | null;
    approved: number;
    sightings: { id: number; type: string; status: string; observedDate: string }[];
}

interface HistoryEntry {
    action: string;
    actor: string | null;
    context: Record<string, unknown>;
    at: string;
}

interface Props {
    member: MemberDetail;
    history: HistoryEntry[];
    canManage: boolean;
    isSelf: boolean;
}

const ROLE_OPTIONS = Object.entries(copy.roles).map(([value, label]) => ({ value, label }));

const dateTime = (iso: string) =>
    new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });

function Card({ title, children }: { title: string; children: ReactNode }) {
    return (
        <section className="rounded-[22px] bg-night/5 p-5 ring-1 ring-night/10">
            <h2 className="font-display text-sm font-bold tracking-[0.06em] uppercase">{title}</h2>
            <div className="mt-3">{children}</div>
        </section>
    );
}

function RoleForm({ member }: { member: MemberDetail }) {
    const form = useForm({ role: member.role });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(`/painel/membros/${member.id}/papel`, { preserveScroll: true });
    };
    return (
        <form onSubmit={submit} className="flex flex-wrap items-end gap-3">
            <SelectField
                label={copy.roleTitle}
                hideLabel
                value={form.data.role}
                onChange={(e) => form.setData('role', e.target.value)}
                options={ROLE_OPTIONS}
                error={form.errors.role}
                className="min-w-48 flex-1"
            />
            <Button
                type="submit"
                variant="secondary"
                loading={form.processing}
                disabled={form.data.role === member.role}
            >
                {copy.roleSave}
            </Button>
        </form>
    );
}

function BlockForm({ member }: { member: MemberDetail }) {
    const form = useForm({ reason: '' });
    if (member.blockedAt) {
        return (
            <div>
                <p className="text-sm text-night/65">
                    {copy.blockedSince} {dateTime(member.blockedAt)}
                </p>
                <p className="mt-2 rounded-xl bg-car/25 px-4 py-3">{member.blockedReason}</p>
                <Button
                    variant="secondary"
                    className="mt-4"
                    onClick={() => router.delete(`/painel/membros/${member.id}/bloqueio`, { preserveScroll: true })}
                >
                    {copy.unblockCta}
                </Button>
            </div>
        );
    }
    if (member.role !== 'member') {
        return <p className="text-night/70">{copy.blockPanelRole}</p>;
    }
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(`/painel/membros/${member.id}/bloqueio`, { preserveScroll: true, onSuccess: () => form.reset() });
    };
    return (
        <form onSubmit={submit} className="space-y-4">
            <p className="text-sm text-night/70">{copy.blockLead}</p>
            <TextareaField
                label={copy.blockReason}
                value={form.data.reason}
                onChange={(e) => form.setData('reason', e.target.value)}
                error={form.errors.reason}
                maxLength={500}
                rows={3}
            />
            <Button type="submit" variant="car" loading={form.processing}>
                {copy.blockCta}
            </Button>
        </form>
    );
}

function DeleteAccount({ member }: { member: MemberDetail }) {
    const [open, setOpen] = useState(false);
    const [busy, setBusy] = useState(false);
    const label = member.nickname ? `@${member.nickname}` : member.name;
    return (
        <>
            <p className="text-sm text-night/70">{copy.deleteLead}</p>
            <Button variant="secondary" className="mt-4" onClick={() => setOpen(true)}>
                {copy.deleteCta}
            </Button>
            <Modal open={open} onClose={() => setOpen(false)} title={copy.deleteConfirm(label)}>
                <p className="text-moonlight/80">{copy.deleteLead}</p>
                <div className="mt-6 flex flex-wrap gap-3">
                    <Button
                        variant="car"
                        loading={busy}
                        onClick={() => {
                            setBusy(true);
                            router.delete(`/painel/membros/${member.id}`, { onFinish: () => setBusy(false) });
                        }}
                    >
                        {copy.deleteConfirmCta}
                    </Button>
                    <Button variant="ghost" tone="dark" onClick={() => setOpen(false)}>
                        {copy.cancel}
                    </Button>
                </div>
            </Modal>
        </>
    );
}

export default function Show({ member, history, canManage, isSelf }: Props) {
    const label = member.nickname ? `@${member.nickname}` : copy.noNickname;
    return (
        <>
            <Head title={`${label} · ${t.panel.title}`} />
            <Link href="/painel/membros" className="min-h-11 font-semibold underline underline-offset-4">
                ← {copy.back}
            </Link>

            <div className="mt-6 flex flex-wrap items-center gap-4">
                <span className="flex size-16 items-center justify-center overflow-hidden rounded-full bg-night font-display text-xl font-bold text-beam-glow uppercase">
                    {member.avatarUrl ? (
                        <img
                            src={member.avatarUrl}
                            alt=""
                            referrerPolicy="no-referrer"
                            className="size-full object-cover"
                        />
                    ) : (
                        (member.nickname ?? member.name).slice(0, 1)
                    )}
                </span>
                <div>
                    <Display as="h1" className="text-[clamp(1.6rem,1.1rem+2vw,2.4rem)]! normal-case!">
                        {label}
                    </Display>
                    <p className="mt-1 flex flex-wrap items-center gap-2 text-night/70">
                        {member.name} · {member.email}
                        <Badge tone="horizon">{copy.roles[member.role]}</Badge>
                        {member.blockedAt && <Badge tone="car">{copy.blocked}</Badge>}
                    </p>
                </div>
            </div>

            <div className="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                <div className="space-y-8">
                    <Card title={copy.profile}>
                        <dl className="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2">
                            <dt className="text-night/60">{copy.joinedLabel}</dt>
                            <dd>{longDate(member.joinedAt)}</dd>
                            <dt className="text-night/60">{copy.city}</dt>
                            <dd>{member.city ?? '—'}</dd>
                            <dt className="text-night/60">{copy.terms}</dt>
                            <dd>{member.termsAcceptedAt ? dateTime(member.termsAcceptedAt) : '—'}</dd>
                        </dl>
                        <p className="mt-3 text-sm text-night/65">{copy.approved(member.approved)}</p>
                    </Card>

                    <section>
                        <h2 className="font-display text-sm font-bold tracking-[0.06em] uppercase">{copy.sightings}</h2>
                        {member.sightings.length === 0 ? (
                            <p className="mt-2 text-night/60">{copy.noSightings}</p>
                        ) : (
                            <ul className="mt-3 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                                {member.sightings.map((sighting) => (
                                    <li key={sighting.id}>
                                        <Link
                                            href={`/painel/relatos/${sighting.id}`}
                                            className="flex min-h-11 flex-wrap items-center gap-3 py-3 hover:underline"
                                        >
                                            <span className="font-semibold">#{sighting.id}</span>
                                            {t.logbook.types[sighting.type as keyof typeof t.logbook.types]}
                                            <span className="text-sm text-night/60">
                                                {shortDate(sighting.observedDate)}
                                            </span>
                                            <Badge>{t.members.sightingStatus[sighting.status]}</Badge>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                </div>

                <div className="space-y-8">
                    {isSelf ? (
                        <p className="rounded-[22px] border-2 border-dashed border-night/20 p-6 font-script text-2xl text-horizon">
                            {copy.self}
                        </p>
                    ) : (
                        <>
                            <Card title={copy.roleTitle}>
                                {canManage ? (
                                    <RoleForm member={member} />
                                ) : (
                                    <p className="text-night/70">{copy.roleOnlyAdmin}</p>
                                )}
                            </Card>
                            <Card title={copy.blockTitle}>
                                <BlockForm member={member} />
                            </Card>
                            {canManage && (
                                <Card title={copy.deleteTitle}>
                                    <DeleteAccount member={member} />
                                </Card>
                            )}
                        </>
                    )}

                    <section>
                        <h2 className="font-display text-sm font-bold tracking-[0.06em] uppercase">{copy.history}</h2>
                        {history.length === 0 ? (
                            <p className="mt-2 text-night/60">{t.panel.review.historyEmpty}</p>
                        ) : (
                            <ol className="mt-3 space-y-3 border-l-2 border-dashed border-horizon/40 pl-4">
                                {history.map((entry, i) => (
                                    <li key={i} className="text-sm">
                                        <strong className="font-semibold">{entry.actor ?? t.panel.audit.system}</strong>{' '}
                                        {t.panel.actions[entry.action] ?? entry.action}
                                        <span className="text-night/55"> · {dateTime(entry.at)}</span>
                                        {typeof entry.context.reason === 'string' && (
                                            <p className="mt-1 text-night/75">{entry.context.reason}</p>
                                        )}
                                        {typeof entry.context.role === 'string' && (
                                            <p className="mt-1 text-night/75">{copy.roles[entry.context.role]}</p>
                                        )}
                                    </li>
                                ))}
                            </ol>
                        )}
                    </section>
                </div>
            </div>
        </>
    );
}

Show.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;
