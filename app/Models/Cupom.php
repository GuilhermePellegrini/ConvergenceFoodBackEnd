<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cupom extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cupons';

    protected $fillable = [
        'token',
        'discount',
        'min_price'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
