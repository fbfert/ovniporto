/** Every interface string lives here. Editable page copy comes from content_blocks instead. */
export const t = {
    brand: {
        name: 'OVNIPORTO',
        tagline: 'A pista de pouso do planalto',
        city: 'Lages · SC',
        signoff: 'Guardei um lugar pra você.',
        location: 'Ao lado da Hospedaria Vila das Pedras · Lages, SC',
    },
    nav: {
        region: 'Conheça a região',
        place: 'O lugar',
        logbook: 'Livro de avistamentos',
        store: 'Loja',
        join: 'Entrar na comunidade',
        home: 'OVNIPORTO, voltar ao início',
        openMenu: 'Abrir menu',
        closeMenu: 'Fechar menu',
        skip: 'Ir para o conteúdo',
        primary: 'Navegação principal',
    },
    footer: {
        explore: 'Navegue',
        community: 'Comunidade',
        map: 'Ver no mapa',
        privacy: 'Privacidade',
        terms: 'Termos',
        madeBy: 'Feito na serra por',
        soon: 'em breve',
    },
    hero: {
        freeVigil: 'Vigília grátis',
        scroll: 'Role para explorar',
        punchline: 'Mais um pro Livro de avistamentos.',
        sceneLabel:
            'Ilustração conceitual da pista à noite na serra de Lages. Ao rolar, um disco voador desce e abduz um carro amarelo.',
    },
    welcome: {
        eyebrow: 'Bem-vindo ao',
        where: 'Onde',
        whereValue: 'Vila das Pedras, Lages · SC',
        city: 'Cidade',
        cityValue: 'Lages, Santa Catarina',
        sightings: 'Relatos',
        sightingsValue: (n: number) => (n === 1 ? 'no Livro' : 'no Livro'),
        runway: 'Pista',
        runwayValue: 'Meta 2028',
        planning: 'em planejamento',
    },
    logbook: {
        eyebrow: 'Alguém jurou ter visto',
        title: 'Livro de avistamentos',
        lead: 'Os últimos relatos aprovados pela torre de controle.',
        yourReport: 'Seu relato aqui',
        report: 'Relatar avistamento',
        map: 'Ver o mapa',
        types: { light: 'Luz', object: 'Objeto', trail: 'Rastro', other: 'Outro' },
        noPlace: 'Serra catarinense',
    },
    strip: ['A pista de pouso do planalto', 'Lages · SC', 'Meta 2028', 'Vigília grátis'],
    place: {
        eyebrow: 'Em planejamento',
        title: 'O lugar',
        today: 'O terreno hoje',
        todayEmpty: 'Fotos do terreno em breve',
        future: 'Como vai ficar',
        concept: 'conceito',
        phase: (n: number) => `fase ${n}`,
        status: { planning: 'em planejamento', building: 'em obra', open: 'aberto' },
        more: 'Conhecer o projeto',
        spacesLabel: 'Os espaços, fase a fase',
        conceptPending: 'Ilustração em produção',
    },
    concept: {
        badge: 'conceito',
        alt: {
            cover: 'Ilustração conceitual: a pista circular de pedras à noite, com balizas vermelhas e um disco voador lançando um feixe verde sobre ela.',
            'cover-alt': 'Ilustração conceitual: disco voador sobre a pista circular no campo estrelado da serra.',
            vigil: 'Ilustração conceitual da área de vigília: pessoas de costas em espreguiçadeiras, enroladas em mantas, olhando a Via Láctea ao redor de um fogo baixo.',
            'yellow-car':
                'Ilustração conceitual: o carro amarelo suspenso num feixe verde ao entardecer, com um visitante tirando foto.',
            'yellow-car-sculpture':
                'Ilustração conceitual: escultura de disco voador segurando o carro amarelo no feixe verde, sobre o mirante da serra.',
            'customs-shop':
                'Ilustração conceitual da Aduana interplanetária: casa de madeira e pedra com a porta aberta, balcão de carimbos e prateleiras de lembranças.',
            'snack-bar':
                'Ilustração conceitual da lanchonete: balcão coberto com o quadro Cardápio de bordo e mesas compridas com gente de manta numa noite fria.',
            tower: 'Ilustração conceitual da torre de controle: mirante de madeira com telescópio, e a pista com balizas vermelhas lá embaixo.',
            overview:
                'Ilustração conceitual da vista geral: a pista, a vigília, o carro no feixe, a aduana e a torre ligados por caminhos de pedra, com a hospedaria ao fundo.',
            'museum-path':
                'Ilustração conceitual do museu ao ar livre: trilha de pedra com placas baixas iluminadas, uma com a estrela de Cachi.',
            'museum-path-alt': 'Ilustração conceitual: trilha astronômica com placas iluminadas sob a Via Láctea.',
        },
    },
    store: {
        eyebrow: 'Lembranças de',
        title: 'OVNIPORTO',
        cta: 'Ver a loja',
        ready: 'pronta entrega',
        madeToOrder: (days: number) => `feito sob pedido · ${days} dias`,
        empty: 'A loja está separando as primeiras lembranças.',
    },
    legend: {
        eyebrow: 'De Cachi a Lages',
        title: 'A lenda do carro amarelo',
        cta: 'Ler a lenda',
        pending: 'aguardando conteúdo',
        polaroid: 'Essa história ainda está sendo escrita.',
    },
    region: {
        eyebrow: 'Fique mais um dia',
        title: 'Conheça a região',
        empty: 'Pousadas, trilhas e produtores da serra em breve.',
        join: 'Quero aparecer aqui',
        all: 'Ver tudo',
        example: 'exemplo',
        types: {
            inn: 'Pousada',
            attraction: 'Passeio',
            producer: 'Produtor',
            restaurant: 'Comida',
            other: 'Outro',
        } as Record<string, string>,
    },
    form: {
        charactersUsed: 'caracteres usados',
        close: 'Fechar',
    },
    community: {
        eyebrow: 'Entre na vigília',
        title: 'Comunidade',
        google: 'Entrar com Google',
        whatsapp: 'WhatsApp',
        instagram: 'Instagram',
        linkSoon: 'link em breve',
        waitlistTitle: 'Avise-me da campanha',
        waitlistLead:
            'A arrecadação para a pista só abre quando o orçamento estiver pronto. Deixe o e-mail e a torre avisa.',
        emailLabel: 'Seu e-mail',
        emailPlaceholder: 'voce@exemplo.com',
        consent: 'Quero receber um e-mail quando a campanha abrir. Posso sair quando quiser.',
        submit: 'Me avise',
        sending: 'Enviando…',
        postcardTitle: 'Mande um postal',
        postcardLead: 'Conhece alguém que precisa ver o céu de Lages?',
        postcardTo: 'Para: quem precisa de um céu escuro',
        postcardMessage: 'Achei o lugar onde o céu ainda é escuro e alguém sempre jura ter visto alguma coisa.',
        shareWhatsapp: 'Enviar no WhatsApp',
        copy: 'Copiar link',
        copied: 'Link copiado',
        shareText: (url: string) => `Olha o que estão fazendo no céu de Lages: ${url}`,
    },
    legendPage: {
        eyebrow: 'Como tudo começou',
        title: 'A lenda',
        lead: 'Uma pista de pouso de verdade, com uma lenda inventada por cima. A gente conta qual é qual.',
        originEyebrow: 'A origem real',
        originTitle: 'De Cachi a Lages',
        origin: [
            'O OVNIPORTO nasceu de uma viagem. O Felipe visitou o Ovnipuerto de Cachi, na Argentina: uma pista de pouso para discos voadores desenhada com pedras no meio do campo.',
            'Voltou com uma pergunta na cabeça: e por que não na serra de Lages, onde o céu é tão escuro quanto? A pista vai ser de verdade. O carro amarelo é lenda, criada aqui, e a gente não finge o contrário.',
        ],
        photoPending: ['Cachi, Salta · foto em breve', 'Estrelas de pedra · foto em breve'],
        timelineToggle: 'Saiba mais sobre Cachi',
        timelineHide: 'Recolher a linha do tempo',
        timelineTitle: 'Cachi, na linha do tempo',
        carEyebrow: 'A lenda criada',
        carTitle: 'O carro amarelo',
        pendingCaption: 'Essa história ainda está sendo escrita.',
        pendingLead:
            'O texto da lenda ainda não foi escrito, e ninguém aqui vai inventar às pressas. Deixe o e-mail e a torre avisa quando sair.',
        notify: 'Me avise quando sair',
        closing: 'Viu alguma coisa no céu da serra?',
        report: 'Relatar avistamento',
    },
    faqPage: {
        eyebrow: 'Antes que você pergunte',
        title: 'Perguntas frequentes',
        description: 'O OVNIPORTO já existe? É de graça? Meus dados ficam públicos? As respostas diretas.',
        missing: 'Não achou a sua?',
        write: 'Escreva pra gente',
        empty: 'As perguntas estão sendo escritas.',
    },
    communityPage: {
        eyebrow: 'Entre na vigília',
        title: 'Comunidade',
        description: 'As regras de convivência da vigília, o grupo do WhatsApp, o Instagram e o Avise-me da campanha.',
        rulesEyebrow: 'O combinado',
        rulesTitle: 'Cinco regras curtas',
        channelsTitle: 'Onde a vigília conversa',
        channelSoon: 'link em breve',
        waitlistTitle: 'Avise-me da campanha',
        waitlistLead:
            'A arrecadação para a pista só abre quando o orçamento estiver pronto. Deixe o e-mail e a torre avisa.',
    },
    legalPage: {
        privacy: {
            eyebrow: 'Seus dados, suas regras',
            title: 'Privacidade',
            description: 'Como o OVNIPORTO trata os seus dados. Texto em rascunho, a ser redigido pelo encarregado.',
        },
        terms: {
            eyebrow: 'O combinado',
            title: 'Termos de uso',
            description:
                'As regras de uso do site, da comunidade e da loja. Texto em rascunho, a ser redigido pelo encarregado.',
        },
        draft: 'RASCUNHO — texto final será redigido pelo encarregado (Felipe)',
        draftLead: 'Nada aqui é a versão final. A estrutura de tópicos está pronta para ele escrever.',
        updated: (date: string) => `Atualizado em ${date}`,
        toc: 'Nesta página',
    },
    upcoming: {
        badge: 'em construção pela torre',
        back: 'Voltar ao início',
        notify: 'Enquanto isso, deixe o e-mail:',
    },
    errors: {
        404: { title: 'Esse ponto do céu ainda não foi mapeado.', lead: 'O endereço não existe ou mudou de órbita.' },
        403: { title: 'Área restrita da torre.', lead: 'Você não tem acesso a esta página.' },
        419: { title: 'A página ficou tempo demais no ar.', lead: 'Recarregue e tente de novo.' },
        429: { title: 'Calma, piloto.', lead: 'Muitas tentativas seguidas. Espere um minuto e tente de novo.' },
        500: { title: 'Perdemos o sinal da torre.', lead: 'Algo deu errado do nosso lado. Já estamos olhando.' },
        503: { title: 'Pista em manutenção.', lead: 'Voltamos em instantes.' },
        back: 'Voltar ao início',
    } as Record<number, { title: string; lead: string }> & { back: string },
    waitlist: {
        confirmedEyebrow: 'Torre de controle confirma',
        confirmedTitle: 'Pronto.',
        confirmedLead: 'Guardei um lugar pra você. Quando a campanha abrir, você fica sabendo antes.',
    },
    toast: {
        close: 'Fechar aviso',
    },
} as const;

export const money = (cents: number): string =>
    (cents / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

export const shortDate = (iso: string): string => {
    const [y, m, d] = iso.split('-').map(Number);
    const date = new Date(Date.UTC(y ?? 1970, (m ?? 1) - 1, d ?? 1));
    return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', timeZone: 'UTC' }).replace('.', '');
};

/** "2026-10-01" → "1 de outubro de 2026". */
export const longDate = (iso: string): string => {
    const [y, m, d] = iso.split('-').map(Number);
    const date = new Date(Date.UTC(y ?? 1970, (m ?? 1) - 1, d ?? 1));
    return date.toLocaleDateString('pt-BR', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
};
