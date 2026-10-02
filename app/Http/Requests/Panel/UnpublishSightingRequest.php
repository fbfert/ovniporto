<?php

namespace App\Http\Requests\Panel;

use App\Domain\Sightings\ModerationRules;
use Illuminate\Foundation\Http\FormRequest;

class UnpublishSightingRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['note' => ['required', 'string', 'min:'.ModerationRules::NOTE_MIN, 'max:500']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'note.required' => 'Registre por que o relato saiu do ar.',
            'note.min' => 'Escreva pelo menos 10 caracteres.',
        ];
    }
}
