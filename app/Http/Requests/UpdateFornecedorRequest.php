<?php

namespace App\Http\Requests;

use App\Enums\StatusFornecedor;
use App\Rules\Cnpj;
use App\Rules\TelefoneComDdiDdd;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateFornecedorRequest extends FormRequest
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
            'cnpj' => preg_replace('/\D/', '', (string) $this->input('cnpj')),
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
        $fornecedor = $this->route('fornecedor');

        return [
            'nome' => ['required', 'string', 'min:3', 'max:150'],
            'cnpj' => ['required', 'string', 'size:14', new Cnpj, Rule::unique('fornecedores', 'cnpj')->ignore($fornecedor)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('fornecedores', 'email')->ignore($fornecedor)],
            'telefone' => ['required', 'string', new TelefoneComDdiDdd],
            'status' => ['required', new Enum(StatusFornecedor::class)],
        ];
    }
}
