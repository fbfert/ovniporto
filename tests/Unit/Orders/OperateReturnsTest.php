<?php

use App\Application\Orders\UseCases\OperateReturns;
use App\Domain\Orders\InvalidReturnTransition;
use App\Domain\Orders\ReturnReason;
use App\Domain\Orders\ReturnRequestStatus;
use Tests\Support\MemoryReturns;
use Tests\Support\SnapshotAuditor;

beforeEach(function () {
    $this->returns = new MemoryReturns;
    $this->protocol = $this->returns->create(1, 'Ana', 'ana@example.com', ReturnReason::Withdrawal, 'Não serviu.', true, null);
    $this->auditor = new SnapshotAuditor;
    $this->operate = new OperateReturns($this->returns, $this->auditor);
});

it('moves a request forward with the note, the author and an audit record', function () {
    $this->operate->changeStatus(7, $this->protocol, 'in_progress', ' Etiqueta reversa combinada. ');
    $this->operate->changeStatus(7, $this->protocol, 'completed', 'Etiqueta reversa enviada e valor reembolsado');

    expect($this->returns->statusOf($this->protocol)['status'])->toBe(ReturnRequestStatus::Completed)
        ->and($this->returns->changes[0])->toMatchArray(['actorId' => 7, 'note' => 'Etiqueta reversa combinada.'])
        ->and(array_map(fn ($r) => [$r['entry']->action, $r['entry']->context['to']], $this->auditor->records))
        ->toBe([['return.status_changed', 'in_progress'], ['return.status_changed', 'completed']]);
});

it('refuses a change without a note', function () {
    expect(fn () => $this->operate->changeStatus(7, $this->protocol, 'declined', '  '))
        ->toThrow(InvalidArgumentException::class, 'Escreva uma nota');
    expect($this->returns->changes)->toBe([]);
});

it('refuses to reopen a closed request', function () {
    $this->operate->changeStatus(7, $this->protocol, 'completed', 'Resolvido por WhatsApp.');

    expect(fn () => $this->operate->changeStatus(7, $this->protocol, 'in_progress', 'Voltar.'))->toThrow(InvalidReturnTransition::class)
        ->and($this->auditor->records)->toHaveCount(1);
});

it('refuses an unknown request or status', function () {
    expect(fn () => $this->operate->changeStatus(7, 'DEV-2026-999999', 'completed', 'Nota.'))->toThrow(InvalidArgumentException::class, 'não encontrado')
        ->and(fn () => $this->operate->changeStatus(7, $this->protocol, 'lost', 'Nota.'))->toThrow(InvalidArgumentException::class, 'Status desconhecido');
});
