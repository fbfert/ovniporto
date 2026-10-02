<?php

namespace App\Application\Place\UseCases;

use App\Application\Content\UseCases\RenderContentBlocks;
use App\Domain\Place\Contracts\ConstructionPostRepository;

final readonly class GetConstructionPost
{
    public function __construct(
        private ConstructionPostRepository $posts,
        private RenderContentBlocks $render,
    ) {}

    /** @return array<string, mixed>|null null when the slug doesn't exist or isn't published yet */
    public function execute(string $slug): ?array
    {
        $post = $this->posts->findPublished($slug);
        if ($post === null) {
            return null;
        }

        $body = $post['body'];
        unset($post['body']);

        return [...$post, 'bodyHtml' => $this->render->render($body) ?? ''];
    }
}
