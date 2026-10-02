<?php

namespace App\Domain\Place\Contracts;

use App\Domain\Place\Data\DiaryPostDraft;

/** /painel/obra: every post, drafts and scheduled ones included. */
interface DiaryAdminRepository
{
    /** @return list<array{id: int, slug: string, title: string, phase: int, publishedAt: ?string, cover: ?string}> newest first, drafts on top */
    public function all(): array;

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array;

    public function coverOf(int $id): ?string;

    public function create(DiaryPostDraft $post, ?string $coverPath): int;

    public function update(int $id, DiaryPostDraft $post, ?string $coverPath): void;

    /** @return string|null the cover path to erase */
    public function delete(int $id): ?string;
}
