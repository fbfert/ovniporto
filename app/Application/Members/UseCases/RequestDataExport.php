<?php

namespace App\Application\Members\UseCases;

use App\Jobs\ExportMemberData;

/** "Baixar meus dados": the file is built in the background and e-mailed to the account address. */
final class RequestDataExport
{
    public function execute(int $memberId): void
    {
        ExportMemberData::dispatch($memberId);
    }
}
