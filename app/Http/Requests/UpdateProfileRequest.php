<?php

namespace App\Http\Requests;

use App\Domain\Members\Nickname;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'min:'.Nickname::MIN, 'max:'.Nickname::MAX],
            'city' => ['nullable', 'string', 'max:80'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nickname.required' => 'Escolha um apelido.',
            'nickname.min' => CompleteProfileRequest::nicknameMessage('format'),
            'nickname.max' => CompleteProfileRequest::nicknameMessage('format'),
        ];
    }
}
