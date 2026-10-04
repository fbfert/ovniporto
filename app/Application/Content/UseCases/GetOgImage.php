<?php

namespace App\Application\Content\UseCases;

use App\Domain\Content\Contracts\OgCardRepository;
use App\Domain\Content\Contracts\OgImageRenderer;
use App\Domain\Content\Sharing\OgCard;
use App\Domain\Content\Sharing\OgKind;

final readonly class GetOgImage
{
    public function __construct(
        private OgCardRepository $cards,
        private OgImageRenderer $renderer,
    ) {}

    /** The current card of a public item (null when it isn't public). */
    public function card(OgKind $kind, string $key): ?OgCard
    {
        return $this->cards->find($kind, $key);
    }

    /**
     * The image for the requested version. A stale version gets no path, only the
     * current version to redirect to; content that isn't public gets null.
     *
     * @return array{path: ?string, version: string}|null
     */
    public function execute(OgKind $kind, string $key, string $version): ?array
    {
        $card = $this->card($kind, $key);
        if ($card === null) {
            return null;
        }

        $current = $card->version();

        return [
            'path' => $current === $version ? $this->renderer->render($card) : null,
            'version' => $current,
        ];
    }
}
