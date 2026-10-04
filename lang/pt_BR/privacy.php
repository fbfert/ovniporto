<?php

/*
 * "O que fazemos na prática" on /privacidade. Each line is a guarantee the code
 * enforces and a test checks; the numbers come from the code (CodePrivacyPractices).
 */

return [
    'title' => 'O que fazemos na prática',
    'practices' => [
        'Fotos de relato perdem todos os metadados (local do GPS, data, modelo da câmera) antes de serem gravadas no servidor. O ponto no mapa é você quem escolhe.',
        'Enquanto um relato está em análise, as fotos só abrem por um link que vale :minutes minutos, e só para você e para a moderação.',
        'Em público aparece só o seu apelido. Nome e e-mail nunca são mostrados.',
        'Do Google guardamos apenas nome, e-mail, foto de perfil e o identificador da conta, e pedimos só as permissões :scopes.',
        'Dados de cartão vão do seu navegador direto para o PayPal e nunca passam pelo nosso servidor.',
        'Cada consentimento (termos, publicação de relato, Avise-me e listagem de parceiros) fica registrado com versão, data, IP e navegador. Se os termos mudarem, pedimos seu aceite de novo.',
        'Em Minha conta você baixa todos os seus dados (perfil, relatos, pedidos, inscrições e consentimentos) e pode excluir a conta quando quiser.',
        'Ao excluir a conta, relatos e fotos são apagados na hora. Pedidos pagos ficam guardados por :years anos após o pagamento, sem seu nome nem contato, porque a lei fiscal exige; depois são apagados.',
        'Não usamos cookies de terceiros nem anúncios. O site só cria o cookie da sessão e o de proteção de formulários; a contagem de visitas é feita sem cookie, em servidor próprio.',
        'Os registros de acesso do servidor são guardados por no máximo :months meses.',
    ],
];
