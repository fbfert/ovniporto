<?php

namespace App\Application\Place\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Content\Contracts\ImageLibrary;
use App\Domain\Place\Contracts\DiaryAdminRepository;
use App\Domain\Place\Data\DiaryPostDraft;

/** /painel/obra, admin only: write, schedule and remove diary posts. */
final readonly class ManageConstructionDiary
{
    public function __construct(
        private DiaryAdminRepository $posts,
        private ImageLibrary $images,
        private Auditor $auditor,
    ) {}

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->posts->all();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        return $this->posts->find($id);
    }

    public function create(int $actorId, DiaryPostDraft $post, ?string $cover): int
    {
        $post->assertCoverDescribed($cover !== null);
        $path = $cover === null ? null : $this->images->store($cover, 'diary');

        return $this->auditor->audited(
            new AuditEntry($actorId, 'diary.created', 'diary', 0, ['title' => $post->title]),
            fn () => null,
            fn () => $this->posts->create($post, $path),
        );
    }

    /** A new cover replaces (and erases) the previous one. */
    public function update(int $actorId, int $id, DiaryPostDraft $post, ?string $cover): void
    {
        $previous = $this->posts->coverOf($id);
        $post->assertCoverDescribed($cover !== null || $previous !== null);
        $path = $cover === null ? null : $this->images->store($cover, 'diary');

        $this->auditor->audited(
            new AuditEntry($actorId, 'diary.updated', 'diary', $id, ['title' => $post->title]),
            fn () => $this->posts->find($id),
            fn () => $this->posts->update($id, $post, $path),
        );
        if ($path !== null) {
            $this->images->delete($previous);
        }
    }

    public function delete(int $actorId, int $id): void
    {
        $this->auditor->audited(
            new AuditEntry($actorId, 'diary.deleted', 'diary', $id),
            fn () => $this->posts->find($id),
            fn () => $this->images->delete($this->posts->delete($id)),
        );
    }
}
