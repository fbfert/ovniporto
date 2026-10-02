<?php

namespace App\Application\Content\UseCases;

use App\Domain\Content\Contracts\ContentBlockRepository;

/**
 * Public community channels, shared with every page. Links that were never
 * filled in come back as null so the UI shows "em breve" instead of a dead link.
 */
final readonly class GetCommunityLinks
{
    public const FALLBACK_EMAIL = 'contato@ovniporto.tars.art.br';

    public function __construct(private ContentBlockRepository $blocks) {}

    /** @return array{whatsapp: ?string, instagram: ?string, email: string} */
    public function execute(): array
    {
        $values = $this->blocks->values(['link_whatsapp', 'link_instagram', 'contact_email']);

        return [
            'whatsapp' => $this->filled($values['link_whatsapp'] ?? null),
            'instagram' => $this->filled($values['link_instagram'] ?? null),
            'email' => $this->filled($values['contact_email'] ?? null) ?? self::FALLBACK_EMAIL,
        ];
    }

    private function filled(?string $value): ?string
    {
        return blank($value) ? null : trim((string) $value);
    }
}
