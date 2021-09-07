<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimentoEstoques extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pedido_id',
        'estoque_id',
        'produto_id',
        'entrada',
        'saida'
    ];
    
    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
}
