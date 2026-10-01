<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
    /** Initial editable texts. Empty values mean "aguardando conteúdo" (never invented). */
    public function run(): void
    {
        $blocks = [
            'home_intro' => 'No alto da Serra Catarinense, ao lado da Hospedaria Vila das Pedras, uma comunidade mantém o céu sob vigilância. O Livro de avistamentos está aberto, a loja já vende lembranças e a pista de pouso tem meta: 2028. Quase toda noite alguém jura ter visto algo.',
            'home_place' => 'Uma pista de pouso de pedra, construída em fases, ao lado da Hospedaria Vila das Pedras. Hoje é terreno, céu escuro e muita vontade.',
            'home_store' => 'Adesivo na mão, pista no céu.',
            'home_legend' => 'Em Cachi, na Argentina, um campo de pedras virou pista de pouso para discos voadores. Em Lages, a história do carro amarelo está sendo escrita.',
            'legend_body' => '',
            'link_whatsapp' => '',
            'link_instagram' => '',
            'contact_email' => 'contato@ovniporto.tars.art.br',
        ];

        foreach ($blocks as $key => $value) {
            ContentBlock::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
