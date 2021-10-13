<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrinho extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loja_id',
        'user_id',
        'endereco_id',
        'cupom_id',
        'price',
        'discount',
        'note',
        'closed'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function produtos()
    {
        return $this->hasMany(CarrinhoProduto::class, 'carrinho_id');
    }

    public function cupom()
    {
        return $this->hasOne(Cupom::class, null, 'cupom_id');
    }

    public function endereco()
    {
        return $this->hasOne(Endereco::class, 'id', 'endereco_id');
    }

    public function loja()
    {
        return $this->hasOne(Loja::class, 'id', 'loja_id');
    }
}
