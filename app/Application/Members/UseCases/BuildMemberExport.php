<?php

namespace App\Application\Members\UseCases;

use App\Domain\Members\Contracts\MemberDataSource;
use App\Domain\Members\Contracts\MemberRepository;

/** Everything we hold about a member, ready to be written as readable JSON, one section per module. */
final readonly class BuildMemberExport
{
    /** @param iterable<MemberDataSource> $sources */
    public function __construct(
        private MemberRepository $members,
        private iterable $sources,
    ) {}

    /** @return array<string, mixed>|null */
    public function execute(int $memberId): ?array
    {
        $profile = $this->members->find($memberId);
        if ($profile === null) {
            return null;
        }

        $export = [
            'gerado_em' => now()->toIso8601String(),
            'perfil' => [
                'nome' => $profile['name'],
                'email' => $profile['email'],
                'foto_google' => $profile['avatarUrl'],
                'apelido' => $profile['nickname'],
                'cidade' => $profile['city'],
                'termos_aceitos_em' => $profile['termsAcceptedAt'],
                'conta_criada_em' => $profile['createdAt'],
            ],
        ];
        foreach ($this->sources as $source) {
            $export[$source->section()] = $source->exportFor($memberId, $profile['email']);
        }

        return $export;
    }
}
