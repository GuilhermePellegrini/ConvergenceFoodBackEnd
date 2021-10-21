<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pagamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mercado_pago_id',
        'pedido_id',
        'user_id',
        'assinatura_id',
        'status',
        'status_detail',
        'date_created',
        'date_approved',
        'money_release_date',
        'transaction_amount',
        'transaction_amount_refunded',
        'payment_method_id',
        'payment_type_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}
