<?php

use App\Application\Content\UseCases\GetOgImage;
use App\Domain\Content\Contracts\OgCardRepository;
use App\Domain\Content\Contracts\OgImageRenderer;
use App\Domain\Content\Sharing\OgCard;
use App\Domain\Content\Sharing\OgKind;

function ogUseCase(?OgCard $card): GetOgImage
{
    $cards = new class($card) implements OgCardRepository
    {
        public function __construct(private ?OgCard $card) {}

        public function find(OgKind $kind, string $key): ?OgCard
        {
            return $this->card;
        }
    };
    $renderer = new class implements OgImageRenderer
    {
        public function render(OgCard $card): string
        {
            return "/cache/{$card->key}.{$card->version()}.jpg";
        }
    };

    return new GetOgImage($cards, $renderer);
}

it('renders the requested version when it is the current one', function () {
    $card = new OgCard(OgKind::Product, 'adesivo', 'Lembranças', 'Adesivo');

    expect(ogUseCase($card)->execute(OgKind::Product, 'adesivo', $card->version()))
        ->toBe(['path' => "/cache/adesivo.{$card->version()}.jpg", 'version' => $card->version()]);
});

it('answers a stale version with the current one and no image', function () {
    $card = new OgCard(OgKind::Product, 'adesivo', 'Lembranças', 'Adesivo');

    expect(ogUseCase($card)->execute(OgKind::Product, 'adesivo', '0000000000'))
        ->toBe(['path' => null, 'version' => $card->version()]);
});

it('has nothing for content that is not public', function () {
    expect(ogUseCase(null)->execute(OgKind::Sighting, '7', '0000000000'))->toBeNull();
});

it('changes the version whenever anything drawn changes', function () {
    $base = new OgCard(OgKind::Sighting, '7', 'Alguém jurou ter visto', 'Luz', 'Lages', '/a.webp');

    expect($base->version())->toHaveLength(10)
        ->not->toBe((new OgCard(OgKind::Sighting, '7', 'Alguém jurou ter visto', 'Luz', 'Lages', '/b.webp'))->version())
        ->not->toBe((new OgCard(OgKind::Sighting, '7', 'Alguém jurou ter visto', 'Rastro', 'Lages', '/a.webp'))->version());
});
