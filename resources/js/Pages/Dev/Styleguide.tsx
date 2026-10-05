import { Head } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import { Seal, SealArt } from '@/Components/Brand/Seal';
import * as Icons from '@/Components/Icons';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField, ChipGroup, SelectField, TextareaField, TextField } from '@/Components/Ui/Fields';
import { InfoCard } from '@/Components/Ui/InfoCard';
import { Marquee } from '@/Components/Ui/Marquee';
import { Modal } from '@/Components/Ui/Modal';
import { NightSkyArt, Polaroid } from '@/Components/Ui/Polaroid';
import { Section } from '@/Components/Ui/Section';
import { TicketCard } from '@/Components/Ui/TicketCard';
import { ToastProvider, useToast } from '@/Components/Ui/Toast';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';

const swatches = ['moonlight', 'night', 'night-blue', 'horizon', 'beam', 'beam-glow', 'car'] as const;

function Row({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div className="border-t border-current/10 py-10">
            <p className="mb-6 text-[0.7rem] font-semibold tracking-[0.12em] uppercase opacity-60">{title}</p>
            <div className="flex flex-wrap items-center gap-4">{children}</div>
        </div>
    );
}

function ToastDemo() {
    const toast = useToast();
    return (
        <Button variant="secondary" tone="dark" onClick={() => toast('Quase lá: confirme no seu e-mail.')}>
            Mostrar toast
        </Button>
    );
}

const SIGHTING_TYPES = [
    { value: 'light', label: 'Luz', icon: <Icons.StarIcon size="1rem" /> },
    { value: 'object', label: 'Objeto', icon: <Icons.UfoIcon size="1rem" /> },
    { value: 'trail', label: 'Rastro', icon: <Icons.BeamIcon size="1rem" /> },
    { value: 'other', label: 'Outro' },
];
const DIRECTIONS = ['N', 'NE', 'L', 'SE', 'S', 'SO', 'O', 'NO'].map((d) => ({ value: d, label: d }));
const TIME_RANGES = [
    { value: 'dusk', label: 'Anoitecer' },
    { value: 'night', label: 'Noite' },
    { value: 'dawn', label: 'Madrugada' },
];

function ModalDemo({ tone }: { tone: 'light' | 'dark' }) {
    const [open, setOpen] = useState(false);
    return (
        <>
            <Button variant="secondary" tone={tone} onClick={() => setOpen(true)}>
                Abrir modal
            </Button>
            <Modal open={open} onClose={() => setOpen(false)} title="Entrar na comunidade" tone={tone}>
                <p className="mb-6 opacity-80">Entre com sua conta Google. Só pedimos nome, e-mail e foto.</p>
                <div className="flex flex-wrap gap-3">
                    <Button onClick={() => setOpen(false)}>Continuar</Button>
                    <Button variant="ghost" tone={tone} onClick={() => setOpen(false)}>
                        Agora não
                    </Button>
                </div>
            </Modal>
        </>
    );
}

function FormDemo({ tone }: { tone: 'light' | 'dark' }) {
    const [description, setDescription] = useState('Uma luz verde parada sobre a serra, depois sumiu de uma vez.');
    const [type, setType] = useState<string | null>('light');
    const [directions, setDirections] = useState<string[]>(['NE']);
    return (
        <div className="grid w-full max-w-3xl gap-6 md:grid-cols-2">
            <TextareaField
                tone={tone}
                label="O que você viu?"
                value={description}
                onChange={(event) => setDescription(event.target.value)}
                maxLength={80}
                showCount
                className="md:col-span-2"
            />
            <SelectField
                tone={tone}
                label="Faixa de horário"
                placeholder="Escolha"
                defaultValue=""
                options={TIME_RANGES}
            />
            <SelectField
                tone={tone}
                label="Com erro"
                defaultValue=""
                placeholder="Escolha"
                options={TIME_RANGES}
                error="Escolha uma faixa de horário."
            />
            <ChipGroup
                tone={tone}
                label="Tipo (seleção única)"
                options={SIGHTING_TYPES}
                value={type}
                onChange={setType}
            />
            <ChipGroup
                tone={tone}
                multiple
                label="Direção do olhar (múltipla)"
                options={DIRECTIONS}
                value={directions}
                onChange={setDirections}
            />
        </div>
    );
}

function Kit({ tone }: { tone: 'light' | 'dark' }) {
    return (
        <>
            <Row title="Botões">
                <Button>Relatar avistamento</Button>
                <Button variant="secondary" tone={tone}>
                    Ver o mapa
                </Button>
                <Button variant="ghost" tone={tone}>
                    Conhecer a origem
                </Button>
                <Button variant="car">Ver a loja</Button>
                <Button loading>Enviando</Button>
                <Button size="sm">Pequeno</Button>
                <Button size="lg" disabled>
                    Desabilitado
                </Button>
            </Row>
            <Row title="Tipografia">
                <div>
                    <Eyebrow tone={tone}>Bem-vindo ao</Eyebrow>
                    <Display as="h2">OVNIPORTO</Display>
                    <Display as="span" outlined className="text-6xl">
                        01
                    </Display>
                </div>
            </Row>
            <Row title="Etiquetas">
                <Badge tone="beam">fase 1</Badge>
                <Badge tone="car">em planejamento</Badge>
                <Badge tone="horizon">conceito</Badge>
                <Badge tone="neutral">aguardando conteúdo</Badge>
            </Row>
            <Row title="Campos">
                <TextField tone={tone} label="E-mail" placeholder="voce@exemplo.com" className="w-72" />
                <TextField
                    tone={tone}
                    label="Com erro"
                    defaultValue="abc"
                    error="Esse e-mail não parece certo."
                    className="w-72"
                />
                <CheckboxField tone={tone} label="Autorizo publicar este relato." />
            </Row>
            <Row title="Texto longo, select e pílulas">
                <FormDemo tone={tone} />
            </Row>
            <Row title="Modal">
                <ModalDemo tone={tone} />
            </Row>
            <Row title="Ícones">
                {Object.entries(Icons).map(([name, Icon]) => (
                    <span
                        key={name}
                        className="inline-flex w-28 flex-col items-center gap-2 text-center text-xs opacity-80"
                    >
                        <Icon size="1.75rem" />
                        {name.replace('Icon', '')}
                    </span>
                ))}
            </Row>
        </>
    );
}

export default function Styleguide() {
    return (
        <ToastProvider>
            <Head title="Styleguide" />
            <Section tone="light">
                <Display as="h1">Styleguide</Display>
                <Row title="Cores">
                    {swatches.map((name) => (
                        <div key={name} className="w-28">
                            <div
                                className="h-16 rounded-xl ring-1 ring-night/10"
                                style={{ background: `var(--color-${name})` }}
                            />
                            <p className="mt-2 text-sm font-semibold">{name}</p>
                        </div>
                    ))}
                </Row>
                <Kit tone="light" />
                <Row title="Informação">
                    <dl className="grid grid-cols-2 gap-6">
                        <InfoCard
                            icon={<Icons.PinIcon size="0.95rem" />}
                            label="Onde"
                            value="Pedras Brancas, Lages · SC"
                        />
                        <InfoCard
                            icon={<Icons.BeamIcon size="0.95rem" />}
                            label="Pista"
                            value="Meta 2028"
                            extra={<Badge tone="car">em planejamento</Badge>}
                        />
                    </dl>
                </Row>
                <Row title="Polaroids">
                    <div className="w-56">
                        <Polaroid
                            rotate={-4}
                            art={<NightSkyArt label="Luz" seed={2} />}
                            caption="Luz · Lages · 12 set"
                        />
                    </div>
                    <div className="w-56">
                        <Polaroid rotate={3} tape="corner" art={<NightSkyArt seed={5} />} caption="Seu relato aqui" />
                    </div>
                </Row>
            </Section>
            <Marquee items={['A pista de pouso do planalto', 'Lages · SC', 'Meta 2028']} />
            <Section tone="dark" pattern="stars" wave>
                <Kit tone="dark" />
                <Row title="Selo">
                    <Seal size="sm" />
                    <Seal size="md" glow />
                </Row>
                <Row title="Ingresso">
                    <div className="w-72">
                        <TicketCard
                            label="PARA LEVAR"
                            title="Adesivo OVNIPORTO"
                            meta="pronta entrega"
                            price="R$ 8,00"
                            art={
                                <div className="flex h-full items-center justify-center">
                                    <div className="w-1/2">
                                        <SealArt />
                                    </div>
                                </div>
                            }
                        />
                    </div>
                </Row>
                <Row title="Toast">
                    <ToastDemo />
                </Row>
            </Section>
            <Section tone="dark" pattern="grid">
                <Row title="Seção com grade">
                    <p className="max-w-[48ch] opacity-80">
                        Fundo quadriculado discreto, para mapas e fichas da torre de controle.
                    </p>
                </Row>
            </Section>
        </ToastProvider>
    );
}
