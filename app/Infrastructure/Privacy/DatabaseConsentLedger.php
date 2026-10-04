<?php

namespace App\Infrastructure\Privacy;

use App\Domain\Privacy\ConsentType;
use App\Domain\Privacy\Contracts\ConsentLedger;
use App\Models\Consent;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final readonly class DatabaseConsentLedger implements ConsentLedger
{
    private const USER_AGENT_MAX = 512;

    public function __construct(private Request $request) {}

    public function record(
        ConsentType $type,
        string $version,
        ?int $memberId = null,
        ?string $email = null,
        ?string $subject = null,
        ?DateTimeImmutable $givenAt = null,
    ): void {
        $agent = $this->request->userAgent();

        Consent::query()->create([
            'type' => $type->value,
            'version' => $version,
            'member_id' => $memberId,
            'email' => $email === null ? null : mb_strtolower($email),
            'subject' => $subject,
            'ip_address' => $this->request->ip(),
            'user_agent' => $agent === null ? null : Str::limit($agent, self::USER_AGENT_MAX - 3),
            'given_at' => $givenAt ?? now(),
        ]);
    }

    public function latestVersion(int $memberId, ConsentType $type): ?string
    {
        return Consent::query()
            ->where('member_id', $memberId)
            ->where('type', $type->value)
            ->orderByDesc('given_at')
            ->orderByDesc('id')
            ->value('version');
    }

    public function historyOf(int $memberId, string $email): array
    {
        return $this->ownedBy($memberId, $email)
            ->orderBy('given_at')
            ->orderBy('id')
            ->get()
            ->map(fn (Consent $c) => [
                'tipo' => $c->type,
                'versao' => $c->version,
                'assunto' => $c->subject,
                'dado_em' => $c->given_at->toIso8601String(),
            ])
            ->all();
    }

    public function anonymize(int $memberId, string $email): void
    {
        $this->ownedBy($memberId, $email)->update([
            'member_id' => null,
            'email' => null,
            'subject' => null,
            'ip_address' => null,
            'user_agent' => null,
        ]);
    }

    /** @return Builder<Consent> */
    private function ownedBy(int $memberId, string $email): Builder
    {
        return Consent::query()->where(fn ($q) => $q
            ->where('member_id', $memberId)
            ->orWhere('email', mb_strtolower($email)));
    }
}
