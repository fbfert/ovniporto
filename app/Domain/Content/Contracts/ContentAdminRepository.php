<?php

namespace App\Domain\Content\Contracts;

/** Writes behind /painel/configuracoes: content blocks and the two ordered lists (FAQ, rules). */
interface ContentAdminRepository
{
    /** @param array<string, string|null> $values */
    public function put(array $values): void;

    /**
     * @param  'faq'|'regras'  $list
     * @return list<array{id: int, title: string, body: string}>
     */
    public function items(string $list): array;

    /** @param 'faq'|'regras' $list */
    public function saveItem(string $list, ?int $id, string $title, string $body): int;

    /** @param 'faq'|'regras' $list */
    public function deleteItem(string $list, int $id): void;

    /**
     * Swaps the item with its neighbour above (-1) or below (+1).
     *
     * @param  'faq'|'regras'  $list
     */
    public function moveItem(string $list, int $id, int $direction): void;
}
