<?php

namespace App\Console\Commands;

use App\Infrastructure\Brand\ConceptImageBuilder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('concept:import {source : Folder with the original illustrations} {--output= : Derivatives folder} {--manifest= : Manifest JSON path}')]
#[Description('Build AVIF/WebP/JPEG derivatives and the front-end manifest from the concept illustrations')]
class ImportConceptIllustrations extends Command
{
    /** Original file name (as delivered, see ovniporto-ilustracoes.md) => slug used by the site. */
    public const FILES = [
        'Disco Voador sobre o Planalto Estrelado.png' => 'cover',
        'Disco Voador sobre o Campo Estrelado.png' => 'cover-alt',
        'Observatório Sob a Via Láctea.png' => 'vigil',
        'Instalação UFO no Vale Nebuloso.png' => 'yellow-car',
        'Mirante Serrano_ Disco e Feixe Verde.png' => 'yellow-car-sculpture',
        'Aduana Interplanetária nas Montanhas.png' => 'customs-shop',
        'Café Rústico Sob o Céu Estrelado.png' => 'snack-bar',
        'Torre de Controle.png' => 'tower',
        'Observatório Rural sob a Via Láctea.png' => 'overview',
        'Trilha Estelar sob a Via Láctea.png' => 'museum-path',
        'Trilha Astronômica sob a Via Láctea.png' => 'museum-path-alt',
        // The relato of the yellow car (Dropbox OVNIPORTO root, not the Ilustrações folder).
        'nivaamarelo.png' => 'niva-roadside',
        'nivaamarelo2.png' => 'niva-roadside-tall',
        // Origin scenes, the two spaces still missing and the Atlas postcards (docs/ovniporto-imagens-pendentes.md).
        'Estrella de la Esperanza.png' => 'stone-star-night',
        'Atlas dos Ovnipuertos.png' => 'atlas-globe',
        'Casa-cueva.png' => 'cachi-casa-cueva',
        'Pedras e cordas.png' => 'werner-stones',
        'Hangar.png' => 'hangar',
        'Museu coberto.png' => 'museum-indoor',
        'Atlas St Paul.png' => 'atlas-ill-st-paul',
        'Atlas Angelholm.png' => 'atlas-ill-angelholm',
        'Atlas Ares.png' => 'atlas-ill-ares',
        'Atlas Wycliffe Well.png' => 'atlas-ill-wycliffe-well',
        'Atlas Green River.png' => 'atlas-ill-green-river',
        'Atlas Barra do Garcas.png' => 'atlas-ill-barra-do-garcas',
        'Atlas Lajas.png' => 'atlas-ill-lajas',
        'Atlas El Enladrillado.png' => 'atlas-ill-el-enladrillado',
        'Atlas Emilcin.png' => 'atlas-ill-emilcin',
        'Atlas Carbondale.png' => 'atlas-ill-carbondale',
        // Historical cases of the Livro de avistamentos (Dropbox livro-de-avistamentos/avistamentos/imagens).
        '01-florianopolis-1981.png' => 'caso-florianopolis-1981',
        '02-picarras-2017.png' => 'caso-picarras-2017',
        '03-itapoa-2018.png' => 'caso-itapoa-2018',
        '04-voo-sc-2022.png' => 'caso-voo-santa-catarina-2022',
        '05-barra-1952.png' => 'caso-barra-da-tijuca-1952',
        '06-gravatai-1954.png' => 'caso-gravatai-1954',
        '07-colares-1977.png' => 'caso-colares-1977',
        '08-noite-1986.png' => 'caso-noite-oficial-1986',
        '09-valensole-1965.png' => 'caso-valensole-1965',
        '10-rendlesham-1980.png' => 'caso-rendlesham-1980',
        '11-trans-1981.png' => 'caso-trans-en-provence-1981',
        '12-gofast-2015.png' => 'caso-gofast-2015',
    ];

    public function handle(): int
    {
        $source = rtrim((string) $this->argument('source'), '/\\');
        $output = (string) ($this->option('output') ?: public_path('concept'));
        $manifestPath = (string) ($this->option('manifest') ?: resource_path('js/data/concept.json'));

        File::ensureDirectoryExists($output);
        $builder = new ConceptImageBuilder($output);
        // The illustrations arrive in more than one folder: entries built from another folder are kept.
        $manifest = is_file($manifestPath) ? (array) json_decode((string) File::get($manifestPath), true) : [];
        $built = 0;

        foreach (self::FILES as $file => $slug) {
            $path = "{$source}/{$file}";
            if (! is_file($path)) {
                $this->warn("Ausente, pulando: {$file}");

                continue;
            }
            $manifest[$slug] = $builder->build($path, $slug);
            $built++;
            $this->line("{$slug} ← {$file}");
        }

        if ($built === 0) {
            $this->error('Nenhuma ilustração encontrada em '.$source);

            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($manifestPath));
        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        $this->info("{$built} ilustrações processadas.");

        return self::SUCCESS;
    }
}
