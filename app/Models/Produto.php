<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'description',
        'categoria_id',
        'loja_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function fotos()
    {
        return $this->belongsToMany(Foto::class, 'produto_fotos', 'produto_id');
    }

    public function estoque()
    {
        return $this->hasOne(Estoque::class);
    }
}
