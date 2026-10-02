<?php

namespace App\Http\Requests;

use App\Domain\Members\Nickname;
use App\Domain\Sightings\Data\SightingSubmission;
use App\Domain\Sightings\GazeDirection;
use App\Domain\Sightings\SightingType;
use App\Domain\Sightings\SubmissionRules;
use App\Domain\Sightings\TimeRange;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Shape only; the domain rules (consent, radius, photos, time) live in SubmissionRules. */
class SubmitSightingRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(SightingType::class)],
            'description' => ['required', 'string', 'min:'.SubmissionRules::DESCRIPTION_MIN, 'max:'.SubmissionRules::DESCRIPTION_MAX],
            'observedDate' => ['required', 'date_format:Y-m-d'],
            'timeRange' => ['nullable', Rule::enum(TimeRange::class)],
            'exactTime' => ['nullable', 'date_format:H:i'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'gaze' => ['nullable', Rule::enum(GazeDirection::class)],
            'nickname' => ['required', 'string', 'min:'.Nickname::MIN, 'max:'.Nickname::MAX],
            'consent' => ['accepted'],
            'photos' => ['array', 'max:'.SubmissionRules::MAX_PHOTOS],
            'photos.*' => ['string', 'uuid'],
            'keptPhotos' => ['array', 'max:'.SubmissionRules::MAX_PHOTOS],
            'keptPhotos.*' => ['integer'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'type.required' => 'Escolha o que você viu.',
            'description.required' => 'Conte o que você viu.',
            'description.min' => 'Conte um pouco mais: pelo menos 20 caracteres.',
            'description.max' => 'No máximo 1000 caracteres.',
            'lat.required' => 'Marque no mapa de onde você olhou o céu.',
            'consent.accepted' => 'Marque a autorização para publicar o relato.',
            'photos.max' => 'Envie no máximo 3 fotos.',
        ];
    }

    public function submission(): SightingSubmission
    {
        return new SightingSubmission(
            type: SightingType::from($this->string('type')->toString()),
            description: $this->string('description')->toString(),
            observedDate: new DateTimeImmutable($this->string('observedDate')->toString()),
            timeRange: TimeRange::tryFrom((string) $this->input('timeRange')),
            exactTime: $this->filled('exactTime') ? $this->string('exactTime')->toString() : null,
            lat: (float) $this->input('lat'),
            lng: (float) $this->input('lng'),
            gaze: GazeDirection::tryFrom((string) $this->input('gaze')),
            nickname: Nickname::normalize($this->string('nickname')->toString()),
            consent: $this->boolean('consent'),
            uploadIds: array_values(array_map('strval', (array) $this->input('photos', []))),
            keptPhotoIds: array_values(array_map('intval', (array) $this->input('keptPhotos', []))),
        );
    }
}
