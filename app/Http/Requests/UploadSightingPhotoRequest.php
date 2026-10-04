<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadSightingPhotoRequest extends FormRequest
{
    public const MAX_KB = 8192;

    /** Accepted by type but not decodable on the server (e.g. a raw HEIC the browser didn't convert). */
    public const UNREADABLE = 'Não consegui ler esta foto. Tente de novo em JPG ou PNG.';

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,heic,heif', 'max:'.self::MAX_KB],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'photo.required' => 'Escolha uma foto.',
            'photo.mimes' => 'Envie a foto em JPG, PNG ou HEIC.',
            'photo.max' => 'A foto passa de 8 MB. Tente uma menor.',
        ];
    }
}
