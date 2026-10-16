<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
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
            'home_legend' => 'O OVNIPORTO nasceu de uma viagem ao Ovnipuerto de Cachi, na Argentina, uma pista de pouso para discos voadores desenhada com pedras. A pergunta veio junto: e por que não na serra de Lages?',
            'legend_body' => (string) file_get_contents(database_path('data/relato-carro-amarelo.md')),
            'link_whatsapp' => '',
            'link_instagram' => '',
            'contact_email' => 'contato@ovniporto.tars.art.br',
            // Legal model texts for the encarregado: the draft notice stays until "{kind}_final" is filled.
            'privacy_body' => (string) file_get_contents(database_path('data/politica-privacidade-modelo.md')),
            'privacy_final' => '',
            'privacy_updated_at' => '2026-10-16',
            'terms_body' => (string) file_get_contents(database_path('data/termos-uso-modelo.md')),
            'terms_final' => '',
            'terms_updated_at' => '2026-10-16',
        ];

        foreach ($blocks as $key => $value) {
            ContentBlock::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
