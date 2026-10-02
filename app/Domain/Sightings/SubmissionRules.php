<?php

namespace App\Domain\Sightings;

use App\Domain\Region\Distance;
use App\Domain\Sightings\Data\SightingSubmission;
use DateTimeImmutable;

/**
 * Rules every report must follow, whatever the form did: explicit consent,
 * a point within 300 km of Lages, at most 3 photos, a time range or an exact
 * time (never both), and no date in the future.
 */
final class SubmissionRules
{
    public const LAGES = ['lat' => -27.816, 'lng' => -50.326];

    public const MAX_DISTANCE_KM = 300;

    public const MAX_PHOTOS = 3;

    public const DESCRIPTION_MIN = 20;

    public const DESCRIPTION_MAX = 1000;

    public static function validate(SightingSubmission $submission, DateTimeImmutable $today): void
    {
        if (! $submission->consent) {
            throw new InvalidSubmission('consent', 'Marque a autorização para publicar o relato.');
        }

        $length = mb_strlen(trim($submission->description));
        if ($length < self::DESCRIPTION_MIN || $length > self::DESCRIPTION_MAX) {
            throw new InvalidSubmission('description', 'Conte o que viu em 20 a 1000 caracteres.');
        }

        if (($submission->timeRange === null) === ($submission->exactTime === null)) {
            throw new InvalidSubmission('time', 'Escolha uma faixa de horário ou a hora exata, não os dois.');
        }

        if ($submission->observedDate->format('Y-m-d') > $today->format('Y-m-d')) {
            throw new InvalidSubmission('observedDate', 'A data não pode estar no futuro.');
        }

        $km = Distance::km(self::LAGES['lat'], self::LAGES['lng'], $submission->lat, $submission->lng);
        if ($km > self::MAX_DISTANCE_KM) {
            throw new InvalidSubmission('point', 'O ponto precisa estar a até 300 km de Lages.');
        }

        if (count($submission->uploadIds) > self::MAX_PHOTOS) {
            throw new InvalidSubmission('photos', 'Envie no máximo 3 fotos.');
        }
    }
}
