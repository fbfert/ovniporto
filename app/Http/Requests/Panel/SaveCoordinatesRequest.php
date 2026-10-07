<?php

namespace App\Http\Requests\Panel;

use App\Domain\Orders\Cpf;
use App\Domain\Settings\SettingsGroup;
use App\Domain\Shipping\Cnpj;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** One block of /painel/coordenadas. Empty is always allowed: it means "back to the .env". */
class SaveCoordinatesRequest extends FormRequest
{
    public const STATES = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

    public function group(): SettingsGroup
    {
        return SettingsGroup::from((string) $this->route('group'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return match ($this->group()) {
            SettingsGroup::Mail => [
                'host' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9.-]+$/'],
                'port' => ['nullable', 'integer', 'between:1,65535'],
                'scheme' => ['nullable', 'in:smtp,smtps'],
                'username' => ['nullable', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'max:255'],
                'fromAddress' => ['nullable', 'email:rfc', 'max:255'],
                'fromName' => ['nullable', 'string', 'max:120'],
            ],
            SettingsGroup::Alerts => [
                'email' => ['nullable', 'email:rfc', 'max:255'],
            ],
            SettingsGroup::Shipping => [
                'name' => ['nullable', 'string', 'max:120'],
                'phone' => ['nullable', 'string', 'regex:/^[\d\s()+-]{10,20}$/'],
                'email' => ['nullable', 'email:rfc', 'max:255'],
                'document' => ['nullable', 'string', 'max:20', $this->document(...)],
                'postalCode' => ['nullable', 'string', 'regex:/^\d{5}-?\d{3}$/'],
                'street' => ['nullable', 'string', 'max:120'],
                'number' => ['nullable', 'string', 'max:20'],
                'district' => ['nullable', 'string', 'max:80'],
                'city' => ['nullable', 'string', 'max:80'],
                'state' => ['nullable', 'string', 'in:'.implode(',', [...self::STATES, ...array_map('strtolower', self::STATES)])],
                'packageLength' => ['nullable', 'integer', 'between:1,100'],
                'packageWidth' => ['nullable', 'integer', 'between:1,100'],
                'packageHeight' => ['nullable', 'integer', 'between:1,100'],
            ],
        };
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'host.regex' => 'Use só o nome do servidor, como smtp.exemplo.com.br.',
            'port.integer' => 'A porta é um número, como 587 ou 465.',
            'port.between' => 'A porta vai de 1 a 65535 (as comuns são 587 e 465).',
            'scheme.in' => 'Escolha STARTTLS ou SSL/TLS.',
            '*.email' => 'Confira o e-mail.',
            'phone.regex' => 'Telefone com DDD, só números.',
            'postalCode.regex' => 'CEP com 8 números.',
            'state.in' => 'Use a sigla do estado, como SC.',
            '*.between' => 'De 1 a 100 cm.',
            '*.integer' => 'Use centímetros inteiros.',
            '*.max' => 'Texto longo demais.',
        ];
    }

    private function document(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = (string) preg_replace('/\D/', '', (string) $value);
        $valid = strlen($digits) === 11 ? Cpf::isValid($digits) : Cnpj::isValid($digits);
        if (! $valid) {
            $fail('CPF ou CNPJ inválido. Confira os números.');
        }
    }
}
