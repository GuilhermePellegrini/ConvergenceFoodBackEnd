<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loja extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'corporate_name',
        'trading_name',
        'cnpj',
        'web_site',
        'phone',
        'cel_phone',
        'email',
        'representante_legal',
        'representante_legal_email',
        'endereco_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }
}