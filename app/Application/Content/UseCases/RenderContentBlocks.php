<?php

namespace App\Application\Content\UseCases;

use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Content\Contracts\MarkdownRenderer;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Editable blocks as safe HTML. An empty block is a first-class state (null),
 * never an error. Rendering is cached by the hash of the markdown, so an edit
 * is visible at once and nothing has to be invalidated.
 */
final readonly class RenderContentBlocks
{
    public function __construct(
        private ContentBlockRepository $blocks,
        private MarkdownRenderer $markdown,
        private Cache $cache,
    ) {}

    /**
     * @param  list<string>  $keys
     * @return array<string, string|null> HTML per key; null while the block is empty
     */
    public function execute(array $keys): array
    {
        $html = [];
        foreach ($this->blocks->values($keys) as $key => $markdown) {
            $html[$key] = $this->render($markdown);
        }

        return $html;
    }

    public function render(?string $markdown): ?string
    {
        if (blank($markdown)) {
            return null;
        }

        return $this->cache->rememberForever(
            'content:html:'.sha1((string) $markdown),
            fn () => $this->markdown->render((string) $markdown),
        );
    }
}
