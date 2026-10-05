<?php

namespace App\Http\Requests;

use App\Domain\Origin\CollaborationArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyAsCollaboratorRequest extends FormRequest
{
    /** Hidden field people never see: bots fill it in. */
    public const HONEYPOT = 'website';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'max:190', 'email:rfc'],
            'location' => ['required', 'string', 'max:120'],
            'areas' => ['required', 'array', 'min:1'],
            'areas.*' => ['string', Rule::in(CollaborationArea::values())],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'consent' => ['accepted'],
            self::HONEYPOT => ['nullable', 'string', 'max:200'],
        ];
    }

    public function isBot(): bool
    {
        return $this->filled(self::HONEYPOT);
    }

    /** @return list<string> */
    public function areas(): array
    {
        return array_values(array_map('strval', (array) $this->input('areas', [])));
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Escreva seu nome.',
            'name.max' => 'Use até 120 caracteres.',
            'email.required' => 'Escreva seu e-mail.',
            'email.email' => 'Esse e-mail não parece certo.',
            'email.max' => 'Esse e-mail não parece certo.',
            'location.required' => 'Diga sua cidade e seu país.',
            'location.max' => 'Use até 120 caracteres.',
            'areas.required' => 'Escolha pelo menos uma forma de ajudar.',
            'areas.min' => 'Escolha pelo menos uma forma de ajudar.',
            'areas.*.in' => 'Escolha uma das opções da lista.',
            'message.required' => 'Conte em poucas linhas como você pode ajudar.',
            'message.min' => 'Conte um pouco mais: pelo menos 10 caracteres.',
            'message.max' => 'Use até 2.000 caracteres.',
            'consent.accepted' => 'Marque a caixa para a gente poder guardar seus dados e responder.',
        ];
    }
}
