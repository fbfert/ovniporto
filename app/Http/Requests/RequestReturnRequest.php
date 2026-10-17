<?php

namespace App\Http\Requests;

use App\Domain\Orders\ReturnReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestReturnRequest extends FormRequest
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
            'orderNumber' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'max:160', 'email:rfc'],
            'name' => ['required', 'string', 'max:120'],
            'reason' => ['required', 'string', Rule::enum(ReturnReason::class)],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            self::HONEYPOT => ['nullable', 'string', 'max:200'],
        ];
    }

    public function isBot(): bool
    {
        return $this->filled(self::HONEYPOT);
    }

    public function reason(): ReturnReason
    {
        return ReturnReason::from($this->string('reason')->toString());
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'orderNumber.required' => 'Escreva o número do pedido, como OVP-2026-000123.',
            'orderNumber.max' => 'Confira o número do pedido, como OVP-2026-000123.',
            'email.required' => 'Escreva o e-mail usado na compra.',
            'email.email' => 'Esse e-mail não parece certo.',
            'email.max' => 'Esse e-mail não parece certo.',
            'name.required' => 'Escreva seu nome.',
            'name.max' => 'Use até 120 caracteres.',
            'reason.required' => 'Escolha o motivo.',
            'reason.enum' => 'Escolha um dos motivos da lista.',
            'message.required' => 'Conte em poucas linhas o que aconteceu.',
            'message.min' => 'Conte um pouco mais: pelo menos 10 caracteres.',
            'message.max' => 'Use até 2.000 caracteres.',
        ];
    }
}
