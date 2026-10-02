<?php

namespace App\Domain\Sightings;

use App\Domain\Sightings\Data\ModerationDecision;

/**
 * The report's state machine as the tower sees it:
 *
 *   pending ──approve──▶ approved ──unpublish──▶ pending
 *   pending|approved ──request changes──▶ changes_requested ──author resends──▶ pending
 *   pending|changes_requested ──reject──▶ rejected
 */
final class ModerationRules
{
    public const NOTE_MIN = 10;

    public static function approve(SightingStatus $from): ModerationDecision
    {
        self::expect($from, [SightingStatus::Pending], 'Só relatos em análise podem ser aprovados.');

        return new ModerationDecision(SightingStatus::Approved, null, publish: true);
    }

    public static function requestChanges(SightingStatus $from, string $message): ModerationDecision
    {
        self::expect($from, [SightingStatus::Pending, SightingStatus::Approved], 'Esse relato não pode receber pedido de ajuste agora.');

        return new ModerationDecision(SightingStatus::ChangesRequested, self::note($message, 'message'), publish: false);
    }

    public static function reject(SightingStatus $from, RejectionReason $reason, ?string $detail): ModerationDecision
    {
        self::expect($from, [SightingStatus::Pending, SightingStatus::ChangesRequested], 'Despublique o relato antes de rejeitar.');
        $note = $reason === RejectionReason::Other
            ? self::note((string) $detail, 'detail')
            : $reason->label();

        return new ModerationDecision(SightingStatus::Rejected, $note, publish: false);
    }

    /** The note stays in the history only: the author is not told. */
    public static function unpublish(SightingStatus $from, string $note): ModerationDecision
    {
        self::expect($from, [SightingStatus::Approved], 'Só relatos aprovados podem ser despublicados.');
        self::note($note, 'note');

        return new ModerationDecision(SightingStatus::Pending, null, publish: false);
    }

    /** The author resends a report the tower asked to adjust. */
    public static function canResubmit(SightingStatus $from): bool
    {
        return $from === SightingStatus::ChangesRequested;
    }

    /** @param list<SightingStatus> $allowed */
    private static function expect(SightingStatus $from, array $allowed, string $message): void
    {
        if (! in_array($from, $allowed, true)) {
            throw new InvalidModeration('status', $message);
        }
    }

    private static function note(string $text, string $field): string
    {
        $text = trim($text);
        if (mb_strlen($text) < self::NOTE_MIN) {
            throw new InvalidModeration($field, 'Escreva pelo menos 10 caracteres.');
        }

        return $text;
    }
}
