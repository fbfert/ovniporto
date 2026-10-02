<?php

namespace App\Domain\Place;

use InvalidArgumentException;

/**
 * The 3D map is an iframe from a viewer the architect uses. Only hosts on the
 * configured list (config/ovniporto.php "embed_hosts") are accepted, over
 * https; the admin may paste the URL or the whole <iframe> snippet.
 */
final class EmbedPolicy
{
    /**
     * @param  list<string>  $allowedHosts
     * @return string the clean https URL to put in the iframe's src
     */
    public static function src(string $input, array $allowedHosts): string
    {
        $input = trim($input);
        if (preg_match('/<iframe[^>]*\ssrc=["\']([^"\']+)["\']/i', $input, $match) === 1) {
            $input = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5);
        }

        $parts = parse_url($input);
        $host = is_array($parts) ? strtolower((string) ($parts['host'] ?? '')) : '';
        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || $host === '') {
            throw new InvalidArgumentException('Cole o link https do visualizador 3D (ou o código do iframe).');
        }

        foreach ($allowedHosts as $allowed) {
            $allowed = strtolower($allowed);
            if ($host === $allowed || str_ends_with($host, ".{$allowed}")) {
                return $input;
            }
        }

        throw new InvalidArgumentException("O domínio {$host} não está na lista permitida: ".implode(', ', $allowedHosts).'.');
    }
}
