<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Content\Contracts\EditorialListRepository;
use App\Models\CommunityRule;
use App\Models\Faq;

final class EloquentEditorialListRepository implements EditorialListRepository
{
    public function faqs(): array
    {
        return Faq::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Faq $faq) => ['question' => $faq->question, 'answer' => $faq->answer])
            ->values()
            ->all();
    }

    public function communityRules(): array
    {
        return CommunityRule::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CommunityRule $rule) => ['title' => $rule->title, 'body' => $rule->body])
            ->values()
            ->all();
    }
}
