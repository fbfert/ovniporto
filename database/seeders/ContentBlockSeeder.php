<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
    private const PRIVACY_DRAFT = <<<'MD'
        ## Dados coletados
        _Tópico a ser redigido pelo encarregado._ Hoje o site pede só nome, e-mail e foto da conta Google, o apelido público e, em cada relato, o que a pessoa decidir enviar.

        ## Finalidades
        _Tópico a ser redigido pelo encarregado._

        ## Bases legais
        _Tópico a ser redigido pelo encarregado._

        ## Compartilhamento
        _Tópico a ser redigido pelo encarregado._ Serviços previstos: PayPal (pagamento), Melhor Envio (frete) e Google (login).

        ## Retenção
        _Tópico a ser redigido pelo encarregado._

        ## Direitos do titular
        _Tópico a ser redigido pelo encarregado._ Em Minha conta vai dar para baixar os próprios dados e excluir a conta.

        ## Contato do encarregado
        _Tópico a ser redigido pelo encarregado._

        ## Cookies
        _Tópico a ser redigido pelo encarregado._ O site não usa cookies de terceiros; usa só os de sessão e de segurança do formulário.

        ## Alterações
        _Tópico a ser redigido pelo encarregado._
        MD;

    private const TERMS_DRAFT = <<<'MD'
        ## Uso do site
        _Tópico a ser redigido pelo encarregado._

        ## Comunidade e relatos
        _Tópico a ser redigido pelo encarregado._

        ## Loja e pedidos
        _Tópico a ser redigido pelo encarregado._

        ## Propriedade intelectual
        _Tópico a ser redigido pelo encarregado._

        ## Responsabilidades
        _Tópico a ser redigido pelo encarregado._

        ## Alterações
        _Tópico a ser redigido pelo encarregado._

        ## Contato
        _Tópico a ser redigido pelo encarregado._
        MD;

    /**
     * Initial editable texts. Empty values mean "aguardando conteúdo" (never invented).
     * Blocks are only created when missing: re-seeding never overwrites a panel edit.
     */
    public function run(): void
    {
        $blocks = [
            'home_intro' => 'No alto da Serra Catarinense, na Localidade Pedras Brancas, uma comunidade mantém o céu sob vigilância. O Livro de avistamentos está aberto, a loja já vende lembranças e a pista de pouso tem meta: 2028. Quase toda noite alguém jura ter visto algo.',
            'home_place' => 'Uma pista de pouso de pedra, construída em fases, na Localidade Pedras Brancas. Hoje é terreno, céu escuro e muita vontade.',
            'home_store' => 'Adesivo na mão, pista no céu.',
            'home_legend' => 'Em Cachi, na Argentina, um campo de pedras virou pista de pouso para discos voadores. Em Lages, a história do carro amarelo está sendo escrita.',
            'legend_body' => '',
            'link_whatsapp' => '',
            'link_instagram' => '',
            'contact_email' => 'contato@ovniporto.tars.art.br',
            // Legal drafts: the notice stays until "{kind}_final" is filled by the person in charge.
            'privacy_body' => self::PRIVACY_DRAFT,
            'privacy_final' => '',
            'privacy_updated_at' => '2026-10-01',
            'terms_body' => self::TERMS_DRAFT,
            'terms_final' => '',
            'terms_updated_at' => '2026-10-01',
        ];

        foreach ($blocks as $key => $value) {
            ContentBlock::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
