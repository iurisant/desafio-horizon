<?php

namespace App\Models;

use App\Enums\StatusProduto;
use Database\Factories\ProdutoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $fornecedor_id
 * @property string $nome
 * @property string|null $descricao
 * @property string $preco
 * @property string $codigo_interno
 * @property StatusProduto $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['fornecedor_id', 'nome', 'descricao', 'preco', 'codigo_interno', 'status'])]
class Produto extends Model
{
    /** @use HasFactory<ProdutoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'produtos';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
            'status' => StatusProduto::class,
        ];
    }

    /**
     * @return BelongsTo<Fornecedor, $this>
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
