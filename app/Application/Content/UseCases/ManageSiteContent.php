<?php

namespace App\Application\Content\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Content\Contracts\ContentAdminRepository;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Content\Contracts\MarkdownRenderer;
use App\Domain\Content\SiteSettings;
use DateTimeImmutable;
use InvalidArgumentException;

/** /painel/configuracoes, admin only: links, goals, markdown blocks, FAQ and rules. */
final readonly class ManageSiteContent
{
    public function __construct(
        private ContentBlockRepository $blocks,
        private ContentAdminRepository $admin,
        private MarkdownRenderer $markdown,
        private Auditor $auditor,
    ) {}

    /** @return array{values: array<string, string|null>, faq: list<array<string, mixed>>, regras: list<array<string, mixed>>} */
    public function page(): array
    {
        return [
            'values' => $this->blocks->values(SiteSettings::allKeys()),
            'faq' => $this->admin->items('faq'),
            'regras' => $this->admin->items('regras'),
        ];
    }

    /** The same renderer as the public pages: what the preview shows is what the site shows. */
    public function preview(string $markdown): string
    {
        return $this->markdown->render($markdown);
    }

    /** @param array<string, string|null> $values links or goals, keyed as in SiteSettings */
    public function saveSettings(int $actorId, array $values): void
    {
        $allowed = [...SiteSettings::LINKS, ...SiteSettings::GOALS];
        if (array_diff(array_keys($values), $allowed) !== []) {
            throw new InvalidArgumentException('Unknown setting.');
        }
        $this->put($actorId, 'settings.updated', $values);
    }

    /** Saving a legal text dates it today; "final" removes the draft notice from the public page. */
    public function saveBlock(int $actorId, string $key, string $markdown, ?bool $final = null): void
    {
        if (! SiteSettings::isBlock($key)) {
            throw new InvalidArgumentException("Unknown block: {$key}");
        }
        $values = [$key => $markdown];
        $kind = SiteSettings::LEGAL[$key] ?? null;
        if ($kind !== null) {
            $values["{$kind}_updated_at"] = (new DateTimeImmutable)->format('Y-m-d');
            $values["{$kind}_final"] = $final ? '1' : '';
        }
        $this->put($actorId, 'content.block_updated', $values);
    }

    /** @param 'faq'|'regras' $list */
    public function saveItem(int $actorId, string $list, ?int $id, string $title, string $body): void
    {
        $this->record($actorId, "content.{$list}_saved", ['id' => $id, 'title' => $title], fn () => $this->admin->saveItem($list, $id, $title, $body));
    }

    /** @param 'faq'|'regras' $list */
    public function deleteItem(int $actorId, string $list, int $id): void
    {
        $this->record($actorId, "content.{$list}_deleted", ['id' => $id], fn () => $this->admin->deleteItem($list, $id));
    }

    /** @param 'faq'|'regras' $list */
    public function moveItem(int $actorId, string $list, int $id, int $direction): void
    {
        $this->record($actorId, "content.{$list}_moved", ['id' => $id, 'direction' => $direction], fn () => $this->admin->moveItem($list, $id, $direction));
    }

    /** @param array<string, string|null> $values */
    private function put(int $actorId, string $action, array $values): void
    {
        $this->auditor->audited(
            new AuditEntry($actorId, $action, 'content', 0, ['keys' => array_keys($values)]),
            fn () => $this->blocks->values(array_keys($values)),
            fn () => $this->admin->put($values),
        );
    }

    /** @param array<string, mixed> $context */
    private function record(int $actorId, string $action, array $context, callable $change): void
    {
        $this->auditor->audited(new AuditEntry($actorId, $action, 'content', 0, $context), fn () => null, $change);
    }
}
