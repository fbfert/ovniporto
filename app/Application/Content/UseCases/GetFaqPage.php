<?php

namespace App\Application\Content\UseCases;

use App\Domain\Content\Contracts\EditorialListRepository;

final readonly class GetFaqPage
{
    public function __construct(
        private EditorialListRepository $lists,
        private RenderContentBlocks $render,
    ) {}

    /** @return list<array{question: string, answerHtml: string}> */
    public function execute(): array
    {
        return array_map(fn (array $faq) => [
            'question' => $faq['question'],
            'answerHtml' => $this->render->render($faq['answer']) ?? '',
        ], $this->lists->faqs());
    }
}
