<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarrinhoProduto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'carrinho_id',
        'produto_id',
        'amount',
        'note',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}
