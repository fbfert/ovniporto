<?php

namespace App\Domain\Content\Contracts;

/** Ordered editorial lists that the panel will edit: FAQ and community rules. */
interface EditorialListRepository
{
    /** @return list<array{question: string, answer: string}> answer in markdown */
    public function faqs(): array;

    /** @return list<array{title: string, body: string}> */
    public function communityRules(): array;
}
