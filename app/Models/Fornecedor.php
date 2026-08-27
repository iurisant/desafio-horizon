<?php

namespace App\Models;

use App\Enums\StatusFornecedor;
use Database\Factories\FornecedorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nome
 * @property string $cnpj
 * @property string $email
 * @property string $telefone
 * @property StatusFornecedor $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nome', 'cnpj', 'email', 'telefone', 'status'])]
class Fornecedor extends Model
{
    /** @use HasFactory<FornecedorFactory> */
    use HasFactory;

    /**
     * Eloquent's English pluralizer would otherwise guess "fornecedors".
     */
    protected $table = 'fornecedores';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusFornecedor::class,
        ];
    }
}
