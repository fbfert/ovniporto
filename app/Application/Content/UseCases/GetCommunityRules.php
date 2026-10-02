<?php

namespace App\Application\Content\UseCases;

use App\Domain\Content\Contracts\EditorialListRepository;

final readonly class GetCommunityRules
{
    public function __construct(private EditorialListRepository $lists) {}

    /** @return list<array{title: string, body: string}> */
    public function execute(): array
    {
        return $this->lists->communityRules();
    }
}
