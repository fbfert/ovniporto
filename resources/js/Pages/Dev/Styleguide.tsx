import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Seal } from '@/Components/Brand/Seal';
import { Button } from '@/Components/Ui/Button';
import { CheckboxField, TextField } from '@/Components/Ui/Fields';
import { InfoCard } from '@/Components/Ui/InfoCard';
import { Marquee } from '@/Components/Ui/Marquee';
import { NightSkyArt, Polaroid } from '@/Components/Ui/Polaroid';
import { Section } from '@/Components/Ui/Section';
import { TicketCard } from '@/Components/Ui/TicketCard';
import { ToastProvider, useToast } from '@/Components/Ui/Toast';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { SealArt } from '@/Components/Brand/Seal';

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

function Kit({ tone }: { tone: 'light' | 'dark' }) {
    return (
        <>
            <Row title="Botões">
                <Button>Relatar avistamento</Button>
                <Button variant="secondary" tone={tone}>
                    Ver o mapa
                </Button>
                <Button variant="ghost" tone={tone}>
                    Ler a lenda
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
                <TextField tone={tone} label="Com erro" defaultValue="abc" error="Esse e-mail não parece certo." className="w-72" />
                <CheckboxField tone={tone} label="Autorizo publicar este relato." />
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
                            <div className="h-16 rounded-xl ring-1 ring-night/10" style={{ background: `var(--color-${name})` }} />
                            <p className="mt-2 text-sm font-semibold">{name}</p>
                        </div>
                    ))}
                </Row>
                <Kit tone="light" />
                <Row title="Informação">
                    <dl className="grid grid-cols-2 gap-6">
                        <InfoCard label="Onde" value="Vila das Pedras, Lages · SC" />
                        <InfoCard label="Pista" value="Meta 2028" extra={<Badge tone="car">em planejamento</Badge>} />
                    </dl>
                </Row>
                <Row title="Polaroids">
                    <div className="w-56">
                        <Polaroid rotate={-4} art={<NightSkyArt label="Luz" seed={2} />} caption="Luz · Lages · 12 set" />
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
        </ToastProvider>
    );
}
