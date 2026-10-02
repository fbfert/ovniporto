<?php

namespace App\Http\Requests\Panel;

use App\Domain\Sightings\ModerationRules;
use App\Domain\Sightings\RejectionReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RejectSightingRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::enum(RejectionReason::class)],
            'detail' => ['nullable', 'required_if:reason,'.RejectionReason::Other->value, 'string', 'min:'.ModerationRules::NOTE_MIN, 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'reason.required' => 'Escolha o motivo da rejeição.',
            'detail.required_if' => 'Conte o motivo ao autor.',
            'detail.min' => 'Escreva pelo menos 10 caracteres.',
        ];
    }

    public function reason(): RejectionReason
    {
        return RejectionReason::from($this->string('reason')->toString());
    }
}
