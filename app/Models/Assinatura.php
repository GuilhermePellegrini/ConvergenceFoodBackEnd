<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assinatura extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'installments',
        'name',
        'price',
        'month_duration',
        'numbers_lojas',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}
