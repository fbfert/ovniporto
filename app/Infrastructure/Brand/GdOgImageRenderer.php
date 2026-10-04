<?php

namespace App\Infrastructure\Brand;

use App\Domain\Content\Contracts\OgImageRenderer;
use App\Domain\Content\Sharing\OgCard;
use RuntimeException;

/**
 * Link preview (1200×630) drawn with GD: the content's photo under a night veil,
 * or the drawn night scene when there is none, with the sticker and the text.
 * Cached on disk by card version; rendering a new version drops the old ones.
 */
final class GdOgImageRenderer implements OgImageRenderer
{
    private const WIDTH = 1200;

    private const HEIGHT = 630;

    private const MARGIN = 72;

    private const TITLE_MAX_LINES = 2;

    public function __construct(private readonly ?string $cacheDir = null) {}

    public function render(OgCard $card): string
    {
        $path = $this->pathFor($card, $card->version());
        if (is_file($path)) {
            return $path;
        }

        $this->forgetOtherVersions($card);
        $this->draw($card)->saveJpeg($path, 84);

        return $path;
    }

    private function draw(OgCard $card): NightCanvas
    {
        $canvas = new NightCanvas(self::WIDTH, self::HEIGHT, seed: crc32($card->kind->value.$card->key));
        $this->background($canvas, $card->photo);

        $seal = public_path('brand/seal-512.webp');
        if (is_file($seal)) {
            $canvas->overlay($seal, self::WIDTH - self::MARGIN - 150, 48, 150);
        }

        $this->text($canvas, $card);

        return $canvas;
    }

    private function background(NightCanvas $canvas, ?string $photo): void
    {
        if ($photo !== null) {
            try {
                $canvas->backdrop($photo, veil: 0.25)->fade(0.35, 0.9);

                return;
            } catch (RuntimeException) {
                // Unreadable file: fall through to the drawn scene.
            }
        }

        $canvas->sky()
            ->stars(220)
            ->haze(0.5)
            ->beam(self::WIDTH - 240, 0, 520, 18, 120, 0.14)
            ->ridge(0.8, 16, NightCanvas::NIGHT_BLUE)
            ->ridge(0.9, 9, NightCanvas::NIGHT)
            ->fade(0.45, 0.6);
    }

    private function text(NightCanvas $canvas, OgCard $card): void
    {
        $fonts = resource_path('fonts');
        $maxWidth = self::WIDTH - self::MARGIN * 2;
        [$lines, $size] = $this->fitTitle(mb_strtoupper($card->title), "{$fonts}/Unbounded.ttf", $maxWidth);
        $lineHeight = (int) round($size * 1.25);

        $footerY = self::HEIGHT - 48;
        $subtitleY = $footerY - 52;
        $titleBottom = $card->subtitle === null ? $subtitleY + 10 : $subtitleY - 46;
        $titleTop = $titleBottom - $lineHeight * (count($lines) - 1);

        $canvas->textAt($card->eyebrow, "{$fonts}/Caveat.ttf", 38, self::MARGIN, $titleTop - $size - 22, NightCanvas::BEAM_GLOW);
        foreach ($lines as $i => $line) {
            $canvas->textAt($line, "{$fonts}/Unbounded.ttf", $size, self::MARGIN, $titleTop + $i * $lineHeight, NightCanvas::MOONLIGHT);
        }
        if ($card->subtitle !== null) {
            $canvas->textAt($card->subtitle, "{$fonts}/Figtree.ttf", 24, self::MARGIN, $subtitleY, NightCanvas::MOONLIGHT, 0.85);
        }
        $footer = (string) trans('seo.og.footer', [], 'pt_BR');
        $canvas->textAt($footer, "{$fonts}/Figtree.ttf", 15, self::MARGIN, $footerY, NightCanvas::CAR);
    }

    /**
     * Largest size (52 down to 30) that fits the title in two lines; longer titles are cut with an ellipsis.
     *
     * @return array{list<string>, int}
     */
    private function fitTitle(string $title, string $font, int $maxWidth): array
    {
        for ($size = 52; $size > 30; $size -= 4) {
            $lines = $this->wrap($title, $font, $size, $maxWidth);
            if (count($lines) <= self::TITLE_MAX_LINES) {
                return [$lines, $size];
            }
        }

        $lines = array_slice($this->wrap($title, $font, 30, $maxWidth), 0, self::TITLE_MAX_LINES);
        $lines[self::TITLE_MAX_LINES - 1] = rtrim($lines[self::TITLE_MAX_LINES - 1], ' .,').'…';

        return [$lines, 30];
    }

    /** @return list<string> */
    private function wrap(string $text, string $font, int $size, int $maxWidth): array
    {
        $lines = [];
        $current = '';
        foreach (preg_split('/\s+/u', trim($text)) ?: [] as $word) {
            $candidate = $current === '' ? $word : "{$current} {$word}";
            if ($current !== '' && NightCanvas::textWidth($candidate, $font, $size) > $maxWidth) {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    private function pathFor(OgCard $card, string $version): string
    {
        return $this->directory($card)."/{$card->key}.{$version}.jpg";
    }

    private function forgetOtherVersions(OgCard $card): void
    {
        foreach (glob($this->directory($card)."/{$card->key}.*.jpg") ?: [] as $old) {
            @unlink($old);
        }
    }

    private function directory(OgCard $card): string
    {
        return ($this->cacheDir ?? storage_path('app/og-cache'))."/{$card->kind->value}";
    }
}
