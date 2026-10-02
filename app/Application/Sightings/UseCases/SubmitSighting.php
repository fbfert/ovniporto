<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Sightings\Contracts\SightingNotifier;
use App\Domain\Sightings\Contracts\SightingWriteRepository;
use App\Domain\Sightings\Data\SightingSubmission;
use App\Domain\Sightings\InvalidSubmission;
use App\Domain\Sightings\SubmissionRules;
use App\Jobs\ProcessSightingPhoto;
use DateTimeImmutable;

/**
 * "Enviar para a torre": checks the domain rules, creates the report in
 * analysis, attaches the member's own uploads, queues the second metadata
 * pass on every photo and the e-mails.
 */
final readonly class SubmitSighting
{
    public function __construct(
        private SightingWriteRepository $sightings,
        private SightingNotifier $notifier,
    ) {}

    public function execute(int $memberId, SightingSubmission $submission): int
    {
        $now = new DateTimeImmutable;
        SubmissionRules::validate($submission, $now);

        $uploads = [];
        foreach (array_unique($submission->uploadIds) as $uploadId) {
            $uploads[] = $this->sightings->findUpload($memberId, $uploadId)
                ?? throw new InvalidSubmission('photos', 'Uma das fotos não foi encontrada. Envie de novo.');
        }

        $created = $this->sightings->createPending($memberId, $submission, array_column($uploads, 'path'), $now);
        foreach ($uploads as $upload) {
            $this->sightings->deleteUpload($upload['id']);
        }
        foreach ($created['photoIds'] as $photoId) {
            ProcessSightingPhoto::dispatch($photoId);
        }
        $this->notifier->submitted($created['sightingId']);

        return $created['sightingId'];
    }
}
