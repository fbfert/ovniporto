<?php

namespace App\Domain\Settings\Data;

/**
 * The outcome of a test send. $problem is one of auth, connection, certificate,
 * sender, other; $detail is the server's answer with the user and the password cut out.
 */
final readonly class MailTestResult
{
    public function __construct(
        public bool $sent,
        public ?string $problem = null,
        public ?string $detail = null,
    ) {}

    /** @return array{sent: bool, problem: ?string, detail: ?string} */
    public function toArray(): array
    {
        return ['sent' => $this->sent, 'problem' => $this->problem, 'detail' => $this->detail];
    }
}
