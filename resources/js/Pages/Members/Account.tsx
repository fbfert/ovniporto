import { Link, router, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { useAuthMember } from '@/Components/Members/auth';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import { TextField } from '@/Components/Ui/Fields';
import { Modal } from '@/Components/Ui/Modal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display } from '@/Components/Ui/Typography';
import { longDate, money, t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.members;
const TABS = ['relatos', 'pedidos', 'dados', 'privacidade'] as const;
type Tab = (typeof TABS)[number];

interface Profile {
    name: string;
    email: string;
    avatarUrl: string | null;
    nickname: string;
    city: string | null;
    role: string;
}

interface OwnSighting {
    id: number;
    type: string;
    status: string;
    moderationNote: string | null;
    observedDate: string;
    placeLabel: string | null;
}

const statusTone = (status: string) =>
    status === 'approved' ? 'beam' : status === 'pending' ? 'neutral' : status === 'rejected' ? 'horizon' : 'car';

function SightingsTab({ sightings }: { sightings: OwnSighting[] }) {
    const [deleting, setDeleting] = useState<OwnSighting | null>(null);

    if (sightings.length === 0) {
        return (
            <div className="rounded-[22px] border-2 border-dashed border-night/20 p-10 text-center">
                <p className="font-script text-2xl text-horizon">{copy.noSightings}</p>
                <div className="mt-5">
                    <Button href="/relatar">{copy.report}</Button>
                </div>
            </div>
        );
    }

    return (
        <>
            <ul className="divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                {sightings.map((sighting) => (
                    <li
                        key={sighting.id}
                        className="flex flex-col gap-4 py-6 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <p className="flex flex-wrap items-center gap-2">
                                <span className="font-display text-lg font-bold tracking-[0.03em] uppercase">
                                    {copy.sightingTypes[sighting.type] ?? sighting.type}
                                </span>
                                <Badge tone={statusTone(sighting.status)}>
                                    {copy.sightingStatus[sighting.status] ?? sighting.status}
                                </Badge>
                            </p>
                            <p className="mt-1 text-sm text-night/65">
                                {longDate(sighting.observedDate)}
                                {sighting.placeLabel && ` · ${sighting.placeLabel}`}
                            </p>
                            {sighting.moderationNote && (
                                <p className="mt-3 max-w-[56ch] rounded-2xl bg-car/15 px-4 py-3 text-sm">
                                    <strong className="font-semibold">{copy.moderationNote}:</strong>{' '}
                                    {sighting.moderationNote}
                                </p>
                            )}
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {sighting.status === 'changes_requested' && (
                                <Button href={`/relatar/${sighting.id}/editar`} size="sm">
                                    {copy.editSighting}
                                </Button>
                            )}
                            <Button variant="secondary" size="sm" onClick={() => setDeleting(sighting)}>
                                {copy.deleteSighting}
                            </Button>
                        </div>
                    </li>
                ))}
            </ul>
            <Modal open={deleting !== null} onClose={() => setDeleting(null)} title={copy.deleteSightingTitle}>
                <p className="opacity-80">{copy.deleteSightingLead}</p>
                <div className="mt-6 flex flex-wrap gap-3">
                    <Button
                        variant="car"
                        onClick={() =>
                            deleting &&
                            router.delete(`/conta/relatos/${deleting.id}`, {
                                preserveScroll: true,
                                onFinish: () => setDeleting(null),
                            })
                        }
                    >
                        {copy.confirmDelete}
                    </Button>
                    <Button variant="ghost" tone="dark" onClick={() => setDeleting(null)}>
                        {copy.cancel}
                    </Button>
                </div>
            </Modal>
        </>
    );
}

interface OwnOrder {
    number: string;
    status: string;
    totalCents: number;
    createdAt: string;
    items: number;
}

function OrdersTab({ orders }: { orders: OwnOrder[] }) {
    if (orders.length > 0) {
        return (
            <ul className="divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                {orders.map((order) => (
                    <li key={order.number}>
                        <Link
                            href={`/pedido/${order.number}`}
                            className="flex min-h-11 flex-wrap items-center justify-between gap-3 py-5 hover:underline"
                        >
                            <span>
                                <span className="font-display font-bold tracking-[0.03em]">{order.number}</span>
                                <span className="ml-3 text-sm text-night/60">
                                    {longDate(order.createdAt.slice(0, 10))} · {t.cart.count(order.items)}
                                </span>
                            </span>
                            <span className="flex items-center gap-3">
                                <Badge tone={order.status === 'pending_payment' ? 'car' : 'neutral'}>
                                    {t.order.status[order.status] ?? order.status}
                                </Badge>
                                <span className="font-semibold tabular-nums">{money(order.totalCents)}</span>
                            </span>
                        </Link>
                    </li>
                ))}
            </ul>
        );
    }
    return (
        <div className="rounded-[22px] border-2 border-dashed border-night/20 p-10 text-center">
            <p className="font-script text-2xl text-horizon">{copy.noOrders}</p>
            <div className="mt-5">
                <Button href="/loja" variant="car">
                    {copy.toStore}
                </Button>
            </div>
        </div>
    );
}

function ProfileTab({ profile }: { profile: Profile }) {
    const form = useForm({ nickname: profile.nickname, city: profile.city ?? '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put('/conta/dados', { preserveScroll: true });
    };

    return (
        <div className="grid gap-12 lg:grid-cols-2">
            <form onSubmit={submit} noValidate className="flex max-w-md flex-col gap-5">
                <TextField
                    label={copy.nicknameLabel}
                    value={form.data.nickname}
                    maxLength={20}
                    autoCapitalize="none"
                    onChange={(event) => form.setData('nickname', event.target.value)}
                    error={form.errors.nickname}
                />
                <TextField
                    label={copy.cityLabel}
                    value={form.data.city}
                    onChange={(event) => form.setData('city', event.target.value)}
                    error={form.errors.city}
                />
                <div>
                    <Button type="submit" loading={form.processing}>
                        {copy.save}
                    </Button>
                </div>
            </form>
            <dl className="self-start rounded-[22px] bg-night/[0.035] p-6 ring-1 ring-night/10">
                <p className="text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase">
                    {copy.googleData}
                </p>
                <dt className="mt-4 text-sm text-night/60">{copy.name}</dt>
                <dd className="font-semibold">{profile.name}</dd>
                <dt className="mt-3 text-sm text-night/60">{copy.email}</dt>
                <dd className="font-semibold break-all">{profile.email}</dd>
            </dl>
        </div>
    );
}

function PrivacyTab({ nickname }: { nickname: string }) {
    const [confirming, setConfirming] = useState(false);
    const form = useForm({ confirmation: '' });
    const destroy = (event: FormEvent) => {
        event.preventDefault();
        form.delete('/conta');
    };

    return (
        <div className="grid gap-8 lg:grid-cols-2">
            <section className="rounded-[22px] bg-night/[0.035] p-6 ring-1 ring-night/10 sm:p-8">
                <h2 className="font-display text-lg font-bold tracking-[0.03em] uppercase">{copy.exportTitle}</h2>
                <p className="mt-2 max-w-[44ch] text-night/75">{copy.exportLead}</p>
                <div className="mt-6">
                    <Button
                        variant="secondary"
                        onClick={() => router.post('/conta/exportar', {}, { preserveScroll: true })}
                    >
                        {copy.exportCta}
                    </Button>
                </div>
            </section>
            <section className="rounded-[22px] border-2 border-dashed border-car p-6 sm:p-8">
                <h2 className="font-display text-lg font-bold tracking-[0.03em] uppercase">{copy.deleteTitle}</h2>
                <p className="mt-2 max-w-[44ch] text-night/75">{copy.deleteLead}</p>
                <div className="mt-6">
                    <Button variant="car" onClick={() => setConfirming(true)}>
                        {copy.deleteCta}
                    </Button>
                </div>
            </section>
            <Modal open={confirming} onClose={() => setConfirming(false)} title={copy.deleteConfirmTitle}>
                <form onSubmit={destroy} noValidate>
                    <p className="opacity-80">{copy.deleteConfirmLead(nickname)}</p>
                    <TextField
                        tone="dark"
                        label={copy.deleteConfirmLabel}
                        hideLabel
                        autoComplete="off"
                        autoCapitalize="none"
                        placeholder={nickname}
                        value={form.data.confirmation}
                        onChange={(event) => form.setData('confirmation', event.target.value)}
                        error={form.errors.confirmation}
                        className="mt-5"
                    />
                    <div className="mt-6 flex flex-wrap gap-3">
                        <Button type="submit" variant="car" loading={form.processing}>
                            {copy.deleteConfirmCta}
                        </Button>
                        <Button type="button" variant="ghost" tone="dark" onClick={() => setConfirming(false)}>
                            {copy.cancel}
                        </Button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}

export default function Account({
    profile,
    sightings,
    orders,
    tab,
}: {
    profile: Profile;
    sightings: OwnSighting[];
    orders: OwnOrder[];
    tab: Tab;
}) {
    const member = useAuthMember();
    return (
        <>
            <SeoHead />
            <Section tone="light" innerClassName="pt-32! sm:pt-36!">
                <div className="flex flex-wrap items-center gap-5">
                    {profile.avatarUrl && (
                        <img
                            src={profile.avatarUrl}
                            alt=""
                            referrerPolicy="no-referrer"
                            className="size-16 rounded-full object-cover ring-4 ring-beam/40"
                        />
                    )}
                    <div>
                        <p className="font-script text-2xl text-horizon">@{profile.nickname}</p>
                        <Display as="h1" className="text-[clamp(1.8rem,1rem+3vw,3.2rem)]!">
                            {copy.accountTitle}
                        </Display>
                    </div>
                </div>

                {member?.blocked && (
                    <p
                        role="status"
                        className="mt-8 max-w-[60ch] rounded-2xl bg-car px-5 py-3 font-semibold text-night"
                    >
                        {copy.blockedNotice}
                    </p>
                )}

                <nav aria-label={copy.accountTitle} className="mt-10">
                    <ul className="flex flex-wrap gap-2 border-b-2 border-dashed border-night/15 pb-3">
                        {TABS.map((key) => (
                            <li key={key}>
                                <Link
                                    href={`/conta?aba=${key}`}
                                    preserveScroll
                                    aria-current={tab === key ? 'page' : undefined}
                                    className="inline-flex min-h-11 items-center rounded-full px-4 text-[0.95rem] font-semibold whitespace-nowrap text-night/70 ring-1 ring-night/15 transition-colors hover:text-night aria-[current=page]:bg-night aria-[current=page]:text-moonlight aria-[current=page]:ring-night"
                                >
                                    {copy.tabs[key]}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>

                <div className="mt-10">
                    {tab === 'relatos' && <SightingsTab sightings={sightings} />}
                    {tab === 'pedidos' && <OrdersTab orders={orders} />}
                    {tab === 'dados' && <ProfileTab profile={profile} />}
                    {tab === 'privacidade' && <PrivacyTab nickname={profile.nickname} />}
                </div>
            </Section>
        </>
    );
}

Account.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;
