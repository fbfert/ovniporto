<?php

namespace App\Http\Requests\Panel;

use App\Domain\Sightings\ModerationRules;
use Illuminate\Foundation\Http\FormRequest;

class RequestChangesRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['message' => ['required', 'string', 'min:'.ModerationRules::NOTE_MIN, 'max:1000']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'message.required' => 'Diga ao autor o que ajustar.',
            'message.min' => 'Escreva pelo menos 10 caracteres.',
        ];
    }
}
