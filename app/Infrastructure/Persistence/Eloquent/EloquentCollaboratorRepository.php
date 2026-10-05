<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Origin\CollaborationArea;
use App\Domain\Origin\Contracts\CollaboratorRepository;
use App\Models\ResearchCollaborator;

final class EloquentCollaboratorRepository implements CollaboratorRepository
{
    public function add(string $name, string $email, string $location, array $areas, string $message, string $consentText): int
    {
        return ResearchCollaborator::query()->create([
            'name' => $name,
            'email' => $email,
            'location' => $location,
            'areas' => array_map(fn (CollaborationArea $area) => $area->value, $areas),
            'message' => $message,
            'consent_text' => $consentText,
            'consented_at' => now(),
        ])->id;
    }

    public function collaborators(): array
    {
        return ResearchCollaborator::query()
            ->latest('id')
            ->get()
            ->map(fn (ResearchCollaborator $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'location' => $c->location,
                'areas' => $c->areas,
                'message' => $c->message,
                'consentedAt' => $c->consented_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    public function remove(int $id): ?string
    {
        $collaborator = ResearchCollaborator::query()->find($id);
        $collaborator?->delete();

        return $collaborator?->email;
    }
}
