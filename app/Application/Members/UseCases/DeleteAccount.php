<?php

namespace App\Application\Members\UseCases;

use App\Domain\Members\Contracts\MemberContentEraser;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\Nickname;
use App\Mail\AccountDeletedMail;
use Illuminate\Support\Facades\Mail;

/**
 * "Excluir minha conta", confirmed by typing the nickname. Each module erases
 * its part through MemberContentEraser (reports and photos deleted, orders anonymized
 * for tax law, consents anonymized, newsletter sign-up dropped), then the profile goes
 * and a goodbye e-mail is queued.
 */
final readonly class DeleteAccount
{
    /** @param iterable<MemberContentEraser> $erasers */
    public function __construct(
        private MemberRepository $members,
        private iterable $erasers,
    ) {}

    /** @return bool false when the confirmation doesn't match the nickname: nothing is deleted */
    public function execute(int $memberId, string $confirmation): bool
    {
        $profile = $this->members->find($memberId);
        if ($profile === null || $profile['nickname'] === null
            || Nickname::normalize($confirmation) !== Nickname::normalize($profile['nickname'])) {
            return false;
        }

        return $this->erase($memberId);
    }

    /** Erases the account without the nickname confirmation: the admin, from the panel. */
    public function erase(int $memberId): bool
    {
        $profile = $this->members->find($memberId);
        if ($profile === null) {
            return false;
        }

        foreach ($this->erasers as $eraser) {
            $eraser->eraseFor($memberId, $profile['email']);
        }
        $this->members->delete($memberId);
        Mail::to($profile['email'])->queue(new AccountDeletedMail($profile['nickname'] ?? $profile['name']));

        return true;
    }
}
