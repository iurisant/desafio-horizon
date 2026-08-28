<?php

namespace App\Http\Requests;

use App\Enums\StatusFornecedor;
use App\Rules\Cnpj;
use App\Rules\TelefoneComDdiDdd;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreFornecedorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize the CNPJ and telefone inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cnpj' => strtoupper((string) preg_replace('/[^0-9A-Za-z]/', '', (string) $this->input('cnpj'))),
            'telefone' => preg_replace('/[\s()-]/', '', (string) $this->input('telefone')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:3', 'max:150'],
            'cnpj' => ['required', 'string', 'size:14', new Cnpj, Rule::unique('fornecedores', 'cnpj')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('fornecedores', 'email')],
            'telefone' => ['required', 'string', new TelefoneComDdiDdd],
            'status' => ['required', new Enum(StatusFornecedor::class)],
        ];
    }
}
