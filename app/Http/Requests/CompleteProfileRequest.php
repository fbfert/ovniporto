<?php

namespace App\Http\Requests;

use App\Domain\Members\Nickname;
use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
{
    public const TERMS_MESSAGE = 'Para entrar na comunidade, aceite os termos.';

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'min:'.Nickname::MIN, 'max:'.Nickname::MAX],
            'city' => ['nullable', 'string', 'max:80'],
            'terms' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nickname.required' => 'Escolha um apelido.',
            'nickname.min' => self::nicknameMessage('format'),
            'nickname.max' => self::nicknameMessage('format'),
            'terms.accepted' => self::TERMS_MESSAGE,
        ];
    }

    public static function nicknameMessage(string $reason): string
    {
        return match ($reason) {
            'taken' => 'Esse apelido já tem dono. Tente outro.',
            'reserved' => 'Esse apelido é reservado para a torre.',
            default => 'Use de 3 a 20 letras minúsculas, números, "_" ou ".".',
        };
    }
}
