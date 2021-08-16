<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Endereco extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'cep',
        'address',
        'district',
        'number',
        'complement',
        'cidade_id',
        'estado_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function cidade()
    {
        return $this->hasOne(Cidade::class, 'id');
    }

    public function estado()
    {
        return $this->hasOne(Estado::class, 'id');
    }
}
