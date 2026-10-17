<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberDataSource;
use App\Domain\Orders\Contracts\ReturnRequestRepository;

/** "Devoluções" of "Baixar meus dados": the requests on the same orders the "pedidos" section lists. */
final readonly class ReturnRequestsDataSource implements MemberDataSource
{
    public function __construct(private ReturnRequestRepository $returns) {}

    public function section(): string
    {
        return 'devolucoes';
    }

    public function exportFor(int $memberId, string $email): array
    {
        return $this->returns->ofMember($memberId, $email);
    }
}
