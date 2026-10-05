<?php

namespace Database\Seeders;

use App\Models\CommunityRule;
use Illuminate\Database\Seeder;

class CommunityRuleSeeder extends Seeder
{
    /** The 5 short rules of the plan. Created only when missing. */
    public function run(): void
    {
        $rules = [
            ['Respeito acima de tudo', 'Discorde da teoria, nunca da pessoa. Nada de ataque, preconceito ou deboche com quem relatou.'],
            ['Sem dados de terceiros', 'Não publique nome, telefone, endereço ou placa de carro de ninguém.'],
            ['Foto de gente só com autorização', 'Rosto de alguém só aparece se a pessoa concordou. Na dúvida, corte ou não envie.'],
            ['Sem spam', 'Nada de corrente, propaganda ou link suspeito. O grupo é para o céu.'],
            ['Humor sim, mentira não', 'Pode brincar com o carro amarelo à vontade. Relato inventado apresentado como verdade sai do Livro.'],
        ];

        foreach ($rules as $order => [$title, $body]) {
            CommunityRule::query()->firstOrCreate(['title' => $title], ['body' => $body, 'sort_order' => $order]);
        }
    }
}
