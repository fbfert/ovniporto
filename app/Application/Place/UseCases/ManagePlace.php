<?php

namespace App\Application\Place\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Content\Contracts\ContentAdminRepository;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Content\Contracts\ImageLibrary;
use App\Domain\Place\Contracts\PlaceAdminRepository;
use App\Domain\Place\EmbedPolicy;
use DateTimeImmutable;

/**
 * /painel/lugar, admin only: the planned spaces and their concept art, real
 * photos of the terrain and the 3D viewer. The place still doesn't exist:
 * nothing here can mark it as open to visitors.
 */
final readonly class ManagePlace
{
    public const MAP3D_KEY = 'place_map3d_embed';

    /** @param list<string> $embedHosts */
    public function __construct(
        private PlaceAdminRepository $place,
        private ContentBlockRepository $blocks,
        private ContentAdminRepository $content,
        private ImageLibrary $images,
        private Auditor $auditor,
        private array $embedHosts,
    ) {}

    /** @return array{spaces: list<array<string, mixed>>, photos: list<array<string, mixed>>, map3d: ?string, embedHosts: list<string>} */
    public function page(): array
    {
        return [
            'spaces' => $this->place->spaces(),
            'photos' => $this->place->photos(),
            'map3d' => $this->blocks->values([self::MAP3D_KEY])[self::MAP3D_KEY] ?: null,
            'embedHosts' => $this->embedHosts,
        ];
    }

    /** @param array{name: string, role: string, description: ?string, phase: int, status: string} $fields */
    public function updateSpace(int $actorId, int $id, array $fields): void
    {
        $this->record($actorId, 'place.space_updated', $id, $fields, fn () => $this->place->updateSpace($id, $fields));
    }

    public function moveSpace(int $actorId, int $id, int $direction): void
    {
        $this->record($actorId, 'place.space_moved', $id, ['direction' => $direction], fn () => $this->place->moveSpace($id, $direction));
    }

    public function setConcept(int $actorId, int $id, string $image): void
    {
        $path = $this->images->store($image, 'concepts');
        $this->record($actorId, 'place.concept_uploaded', $id, ['path' => $path], fn () => $this->images->delete($this->place->setConcept($id, $path)));
    }

    public function addPhoto(int $actorId, string $image, string $alt, ?string $caption, ?DateTimeImmutable $takenAt): void
    {
        $path = $this->images->store($image, 'site');
        $this->record($actorId, 'place.photo_added', 0, ['alt' => $alt], fn () => $this->place->addPhoto($path, $alt, $caption, $takenAt));
    }

    public function updatePhoto(int $actorId, int $id, string $alt, ?string $caption, ?DateTimeImmutable $takenAt): void
    {
        $this->record($actorId, 'place.photo_updated', $id, ['alt' => $alt], fn () => $this->place->updatePhoto($id, $alt, $caption, $takenAt));
    }

    public function deletePhoto(int $actorId, int $id): void
    {
        $this->record($actorId, 'place.photo_deleted', $id, [], fn () => $this->images->delete($this->place->deletePhoto($id)));
    }

    public function movePhoto(int $actorId, int $id, int $direction): void
    {
        $this->record($actorId, 'place.photo_moved', $id, ['direction' => $direction], fn () => $this->place->movePhoto($id, $direction));
    }

    /** Empty clears the viewer; anything else must come from an allowed host (InvalidArgumentException). */
    public function setMap3d(int $actorId, ?string $input): void
    {
        $src = $input === null || trim($input) === '' ? '' : EmbedPolicy::src($input, $this->embedHosts);
        $this->record($actorId, 'place.map3d_updated', 0, ['src' => $src], fn () => $this->content->put([self::MAP3D_KEY => $src]));
    }

    /** @param array<string, mixed> $context */
    private function record(int $actorId, string $action, int $subjectId, array $context, callable $change): void
    {
        $this->auditor->audited(new AuditEntry($actorId, $action, 'place', $subjectId, $context), fn () => null, $change);
    }
}
