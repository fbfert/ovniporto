<?php

namespace App\Http\Requests;

use App\Domain\Region\PartnerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegionFilterRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tipo' => ['nullable', Rule::enum(PartnerType::class)],
            'q' => ['nullable', 'string', 'max:60'],
        ];
    }

    public function type(): ?PartnerType
    {
        return PartnerType::tryFrom((string) $this->validated('tipo'));
    }

    public function search(): ?string
    {
        $q = $this->validated('q');

        return is_string($q) && trim($q) !== '' ? trim($q) : null;
    }
}
