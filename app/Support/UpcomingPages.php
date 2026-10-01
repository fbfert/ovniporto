<?php

namespace App\Support;

/**
 * Public routes that exist in the menu but are built by later OpenSpec changes.
 * Each one renders an honest "em construção" page instead of a 404.
 */
final class UpcomingPages
{
    /** @var array<string, array{title: string, eyebrow: string, description: string, phase: string, change: string}> */
    public const PAGES = [
        'mapa' => [
            'title' => 'Livro de avistamentos',
            'eyebrow' => 'Céu sob vigilância',
            'description' => 'O mapa com todos os relatos aprovados pela torre, filtros por período e tipo, e a ficha de cada avistamento.',
            'phase' => 'Fase 3 · Membros e relatos',
            'change' => 'add-sightings-map',
        ],
        'relatar' => [
            'title' => 'Relatar avistamento',
            'eyebrow' => 'Viu alguma coisa?',
            'description' => 'Um relato em 4 passos, feito para o celular, à noite, com uma mão. As fotos sobem sem os dados de local e hora.',
            'phase' => 'Fase 3 · Membros e relatos',
            'change' => 'add-sighting-submission',
        ],
        'loja' => [
            'title' => 'Loja',
            'eyebrow' => 'Lembranças de',
            'description' => 'O adesivo já está pronto pra colar. Camiseta, caneca e o Kit Abdução entram depois, impressos sob pedido.',
            'phase' => 'Fase 4 · Loja',
            'change' => 'add-store-catalog',
        ],
        'o-lugar' => [
            'title' => 'O lugar',
            'eyebrow' => 'Ao lado da Hospedaria Vila das Pedras',
            'description' => 'A pista de pouso ainda não existe. Aqui vão ficar o mapa, as fotos do terreno, os espaços fase a fase e as regras do céu escuro.',
            'phase' => 'Fase 2 · Páginas públicas',
            'change' => 'add-place-and-campaign',
        ],
        'apoie' => [
            'title' => 'Apoie a pista',
            'eyebrow' => 'Orçamento em planejamento',
            'description' => 'Nenhuma arrecadação abre antes de existir orçamento. Deixe o e-mail e a torre avisa quando a campanha começar.',
            'phase' => 'Fase 2 · Páginas públicas',
            'change' => 'add-place-and-campaign',
        ],
        'obra' => [
            'title' => 'Diário da obra',
            'eyebrow' => 'Pedra por pedra',
            'description' => 'A obra ainda não começou. O primeiro post será o dia em que a primeira pedra for colocada.',
            'phase' => 'Fase 2 · Páginas públicas',
            'change' => 'add-place-and-campaign',
        ],
        'regiao' => [
            'title' => 'Conheça a região',
            'eyebrow' => 'Fique mais um dia',
            'description' => 'Pousadas, trilhas, vinhos e gente da serra em volta do OVNIPORTO, só com quem autorizou aparecer.',
            'phase' => 'Fase 2 · Páginas públicas',
            'change' => 'add-region-partners',
        ],
        'lenda' => [
            'title' => 'A lenda',
            'eyebrow' => 'De Cachi a Lages',
            'description' => 'A história do carro amarelo ainda está sendo escrita. Enquanto isso, a origem real: uma visita ao Ovnipuerto de Cachi, na Argentina.',
            'phase' => 'Fase 2 · Páginas públicas',
            'change' => 'add-content-pages',
        ],
        'comunidade' => [
            'title' => 'Comunidade',
            'eyebrow' => 'Entre na vigília',
            'description' => 'Regras de convivência curtas: respeito, nada de dados de terceiros, humor sim e mentira não.',
            'phase' => 'Fase 2 · Páginas públicas',
            'change' => 'add-content-pages',
        ],
        'faq' => [
            'title' => 'Perguntas frequentes',
            'eyebrow' => 'Antes que você pergunte',
            'description' => 'O lugar existe? É de graça? Meus dados ficam públicos? As respostas chegam junto com as páginas de conteúdo.',
            'phase' => 'Fase 2 · Páginas públicas',
            'change' => 'add-content-pages',
        ],
        'privacidade' => [
            'title' => 'Privacidade',
            'eyebrow' => 'Seus dados, suas regras',
            'description' => 'O texto final será redigido pelo encarregado. Já valem as regras: sem cookies de terceiros e fotos publicadas sem metadados.',
            'phase' => 'Fase 2 · Páginas públicas',
            'change' => 'add-content-pages',
        ],
        'termos' => [
            'title' => 'Termos de uso',
            'eyebrow' => 'O combinado',
            'description' => 'Os termos ainda são rascunho e serão redigidos pelo encarregado antes do lançamento.',
            'phase' => 'Fase 2 · Páginas públicas',
            'change' => 'add-content-pages',
        ],
    ];
}
