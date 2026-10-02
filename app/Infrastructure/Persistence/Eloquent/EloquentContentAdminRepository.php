<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Content\Contracts\ContentAdminRepository;
use App\Models\CommunityRule;
use App\Models\ContentBlock;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class EloquentContentAdminRepository implements ContentAdminRepository
{
    /** List → model and its two text columns. */
    private const LISTS = [
        'faq' => [Faq::class, 'question', 'answer'],
        'regras' => [CommunityRule::class, 'title', 'body'],
    ];

    public function put(array $values): void
    {
        DB::transaction(function () use ($values) {
            foreach ($values as $key => $value) {
                ContentBlock::query()->updateOrCreate(['key' => $key], ['value' => $value ?? '']);
            }
        });
    }

    public function items(string $list): array
    {
        [$model, $title, $body] = self::LISTS[$list];

        return $model::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Model $item) => [
                'id' => (int) $item->getKey(),
                'title' => (string) $item->getAttribute($title),
                'body' => (string) $item->getAttribute($body),
            ])
            ->values()
            ->all();
    }

    public function saveItem(string $list, ?int $id, string $title, string $body): int
    {
        [$model, $titleColumn, $bodyColumn] = self::LISTS[$list];
        $item = $id === null ? new $model(['sort_order' => (int) $model::query()->max('sort_order') + 1]) : $model::query()->findOrFail($id);
        $item->fill([$titleColumn => $title, $bodyColumn => $body])->save();

        return (int) $item->getKey();
    }

    public function deleteItem(string $list, int $id): void
    {
        [$model] = self::LISTS[$list];
        $model::query()->whereKey($id)->delete();
    }

    public function moveItem(string $list, int $id, int $direction): void
    {
        [$model] = self::LISTS[$list];
        DB::transaction(function () use ($model, $id, $direction) {
            /** @var list<int> $ids */
            $ids = $model::query()->orderBy('sort_order')->orderBy('id')->pluck('id')->map(fn ($v) => (int) $v)->all();
            $at = array_search($id, $ids, true);
            $to = $at === false ? false : $at + ($direction < 0 ? -1 : 1);
            if ($at === false || ! isset($ids[$to])) {
                return;
            }
            [$ids[$at], $ids[$to]] = [$ids[$to], $ids[$at]];
            foreach ($ids as $order => $itemId) {
                $model::query()->whereKey($itemId)->update(['sort_order' => $order]);
            }
        });
    }
}
