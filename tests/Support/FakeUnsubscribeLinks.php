<?php

namespace Tests\Support;

use App\Domain\Privacy\Contracts\UnsubscribeLinkGenerator;
use App\Domain\Privacy\MailingList;

/** Predictable unsubscribe links for unit tests of the CSV exports. */
final class FakeUnsubscribeLinks implements UnsubscribeLinkGenerator
{
    public function for(MailingList $list, int $id): string
    {
        return "https://ovniporto.test/descadastrar/{$list->value}/{$id}";
    }
}
