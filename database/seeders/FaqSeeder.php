<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /** The 10 questions of the plan. Created only when missing, so panel edits survive a re-seed. */
    public function run(): void
    {
        $faqs = [
            ['O OVNIPORTO já existe?', 'Ainda não. Hoje existem a comunidade, o Livro de avistamentos e a loja. A pista de pouso na Localidade Pedras Brancas está em planejamento, com meta para 2028.'],
            ['É de graça?', 'A vigília vai ser grátis: olhar o céu não tem preço. Entrar na comunidade e relatar avistamentos também é de graça. Só a loja cobra, pelas lembranças.'],
            ['Como relato um avistamento?', 'Com a conta Google, num formulário de 4 passos: o que você viu, fotos (opcionais), quando e onde. A torre de controle revisa antes de publicar. O formulário ainda está em construção; deixe o e-mail no Avise-me para saber quando abrir.'],
            ['Posso ir lá à noite?', 'Ainda não há o que visitar: o terreno não está aberto ao público. Quando a primeira fase abrir, a vigília vai ser à noite, com luz baixa e vermelha para não apagar o céu.'],
            ['Como eu chego?', 'O OVNIPORTO vai ficar na Localidade Pedras Brancas, em Lages, na Serra Catarinense. O ponto está no mapa, no rodapé do site.'],
            ['Vocês vendem camiseta?', 'Por enquanto, só o adesivo, que já está pronto pra colar. Camiseta, caneca e o Kit Abdução entram na loja depois, impressos sob pedido.'],
            ['Quanto demora a entrega?', 'O adesivo é pronta entrega: o prazo é só o do frete. Nos produtos sob pedido, some os dias de produção, informados em cada produto, ao prazo do frete calculado pelo CEP.'],
            ['Meus dados ficam públicos?', 'Só o seu apelido e o que você autorizar em cada relato: o texto, as fotos e o ponto no mapa que você mesmo escolheu. As fotos são publicadas sem os dados de local e hora do arquivo. Nome e e-mail nunca aparecem.'],
            ['Posso apagar meu relato?', 'Pode, a qualquer momento, em Minha conta, que chega junto com o login. Lá também vai dar para baixar todos os seus dados ou excluir a conta inteira.'],
            ['Como posso apoiar a pista?', 'A arrecadação só abre quando o orçamento da obra estiver pronto. Até lá, deixe o e-mail no Avise-me e, se quiser, leve um adesivo.'],
        ];

        foreach ($faqs as $order => [$question, $answer]) {
            Faq::query()->firstOrCreate(['question' => $question], ['answer' => $answer, 'sort_order' => $order]);
        }
    }
}
