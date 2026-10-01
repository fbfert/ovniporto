<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeWaitlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'max:190', 'email:rfc'],
            'source' => ['nullable', 'string', 'max:40', 'alpha_dash'],
            'consent' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.required' => 'Escreva seu e-mail.',
            'email.email' => 'Esse e-mail não parece certo.',
            'email.max' => 'Esse e-mail não parece certo.',
            'consent.accepted' => 'Marque a caixa para a gente poder te avisar.',
        ];
    }
}
