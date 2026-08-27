<?php

namespace App\Http\Requests;

use App\Enums\StatusFornecedor;
use App\Enums\StatusProduto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateProdutoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $produto = $this->route('produto');

        return [
            'fornecedor_id' => [
                'required',
                'integer',
                Rule::exists('fornecedores', 'id')->where(function ($query) {
                    $query->where('status', StatusFornecedor::Ativo->value)->whereNull('deleted_at');
                }),
            ],
            'nome' => ['required', 'string', 'min:3', 'max:150'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'preco' => ['required', 'numeric', 'gt:0', 'decimal:2'],
            'codigo_interno' => [
                'required',
                'string',
                'max:50',
                Rule::unique('produtos', 'codigo_interno')
                    ->where(fn ($query) => $query->where('fornecedor_id', $this->input('fornecedor_id')))
                    ->ignore($produto),
            ],
            'status' => ['required', new Enum(StatusProduto::class)],
        ];
    }
}
