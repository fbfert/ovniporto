<?php

namespace App\Domain\Members;

use Closure;
use Illuminate\Support\Str;

/**
 * The only public name of a member. 3 to 20 characters: lowercase letters,
 * digits, "_" and "."; unique ignoring case; a few words are reserved so
 * nobody can pass as the control tower.
 */
final class Nickname
{
    public const MIN = 3;

    public const MAX = 20;

    public const RESERVED = ['admin', 'administrador', 'torre', 'torre_de_controle', 'moderador', 'moderacao', 'ovniporto', 'suporte', 'oficial', 'felipe'];

    public static function normalize(string $nickname): string
    {
        return Str::lower(trim($nickname));
    }

    /** @return 'format'|'reserved'|null the reason it can't be used, or null when it can */
    public static function problem(string $nickname): ?string
    {
        $normalized = self::normalize($nickname);
        if (! preg_match('/^[a-z0-9_.]{'.self::MIN.','.self::MAX.'}$/', $normalized)) {
            return 'format';
        }

        return in_array($normalized, self::RESERVED, true) ? 'reserved' : null;
    }

    /**
     * First free nickname derived from the first name: "Ana Clara" → "ana", "ana2", "ana3"…
     *
     * @param  Closure(string): bool  $isTaken
     */
    public static function suggest(string $fullName, Closure $isTaken): string
    {
        $first = Str::of(Str::ascii(Str::before(trim($fullName), ' ')))->lower()->replaceMatches('/[^a-z0-9]/', '')->limit(self::MAX - 3, '')->toString();
        $base = strlen($first) >= self::MIN ? $first : 'vigia';
        if (self::problem($base) === 'reserved') {
            $base = "{$base}_vigia";
        }

        $candidate = $base;
        for ($n = 2; $isTaken($candidate); $n++) {
            $candidate = $base.$n;
        }

        return $candidate;
    }
}
