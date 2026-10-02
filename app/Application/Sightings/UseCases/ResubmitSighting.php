<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Sightings\Contracts\PhotoStorage;
use App\Domain\Sightings\Contracts\SightingNotifier;
use App\Domain\Sightings\Contracts\SightingWriteRepository;
use App\Domain\Sightings\Data\SightingSubmission;
use App\Domain\Sightings\InvalidSubmission;
use App\Domain\Sightings\ModerationRules;
use App\Domain\Sightings\SubmissionRules;
use App\Jobs\LocateSighting;
use App\Jobs\ProcessSightingPhoto;
use DateTimeImmutable;

/**
 * The author fixes what the tower asked and sends the report again: same
 * rules as a new report, the photos they kept stay, removed ones are erased,
 * and the report goes back to the queue as "pending".
 */
final readonly class ResubmitSighting
{
    public function __construct(
        private SightingWriteRepository $sightings,
        private PhotoStorage $storage,
        private SightingNotifier $notifier,
        private Auditor $auditor,
    ) {}

    public function execute(int $memberId, int $sightingId, SightingSubmission $submission): void
    {
        $owned = $this->sightings->findOwned($memberId, $sightingId)
            ?? throw new InvalidSubmission('status', 'Relato não encontrado.');
        if (! ModerationRules::canResubmit($owned['status'])) {
            throw new InvalidSubmission('status', 'Esse relato não está esperando ajuste.');
        }

        $now = new DateTimeImmutable;
        SubmissionRules::validate($submission, $now);

        $kept = array_values(array_unique($submission->keptPhotoIds));
        if (array_diff($kept, $owned['photoIds']) !== []) {
            throw new InvalidSubmission('photos', 'Uma das fotos não foi encontrada. Envie de novo.');
        }
        $uploads = [];
        foreach (array_unique($submission->uploadIds) as $uploadId) {
            $uploads[] = $this->sightings->findUpload($memberId, $uploadId)
                ?? throw new InvalidSubmission('photos', 'Uma das fotos não foi encontrada. Envie de novo.');
        }

        $result = $this->auditor->audited(
            new AuditEntry($memberId, 'sighting.resubmitted', 'sighting', $sightingId),
            fn () => ['status' => $owned['status']->value, 'photoIds' => $owned['photoIds']],
            fn () => $this->sightings->resubmit($sightingId, $submission, $kept, array_column($uploads, 'path'), $now),
        );

        foreach ($uploads as $upload) {
            $this->sightings->deleteUpload($upload['id']);
        }
        foreach ($result['removed'] as $photo) {
            $this->erase($photo['path'], $photo['variants']);
        }
        foreach ($result['photoIds'] as $photoId) {
            ProcessSightingPhoto::dispatch($photoId);
        }
        LocateSighting::dispatch($sightingId);
        $this->notifier->submitted($sightingId);
    }

    /** @param list<int> $variants */
    private function erase(string $path, array $variants): void
    {
        $this->storage->delete($path);
        foreach ($variants as $width) {
            $this->storage->delete("{$path}-{$width}.webp");
        }
    }
}
