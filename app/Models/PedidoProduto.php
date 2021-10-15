<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoProduto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'amount',
        'note',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function produto()
    {
        return $this->hasOne(Produto::class, 'id', 'produto_id');
    }
}
