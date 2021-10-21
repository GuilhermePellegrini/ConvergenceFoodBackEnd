<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loja;
use App\Models\Pagamento;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MercadoPago;

class PagamentoController extends Controller
{

    public function methodPagamentos()
    {
        MercadoPago\SDK::setAccessToken(ENV('MERCADO_PAGO_ACESS_TOKEN'));
        $mp = new MercadoPago\PaymentMethod();
        $metodoPag = $mp->all();
        $metodos_pagamento = [];
        $i = 0;
        foreach($metodoPag as $metodo){
            if($metodo->payment_type_id == 'debit_card' || $metodo->payment_type_id == 'credit_card'){

                $metodos_pagamento[$i] = [
                    "id" => $metodo->id,
                    "name" => $metodo->name,
                    "payment_type_id" => $metodo->payment_type_id,
                    "thumbnail" => $metodo->thumbnail
                ];
                $i++;

            }
        }
        return response($metodos_pagamento,200);
    }

    public function realizarPagamento($pedido_id, Request $request)
    {
        $request->validate([
            'token' => 'required',
            'metodo_pagamento' => 'required',
            'tipo_documento' => 'required',
            'numero_documento' => 'required',
        ]);

        $user = auth()->user();
        $pedido = Pedido::where('user_id', $user->id)->where('id', $pedido_id)->first();
        if(empty($pedido)){
            return response([
                'message' => 'Pedido não encontrado'
            ], 404);
        }

        $pagamento = $pedido->pagamento()->get();
        if(count($pagamento) > 0){
            return response([
                'message' => 'Pedido ja possui pagamento',
                'pagamentos' => $pagamento
            ], 404);
        }

        $loja = Loja::find($pedido->loja_id);

        MercadoPago\SDK::setAccessToken(ENV('MERCADO_PAGO_ACESS_TOKEN'));

        $payment = new MercadoPago\Payment();
        $payment->transaction_amount = (float)$pedido->price;
        $payment->token = $request->token;
        $payment->statement_descriptor = $loja->trading_name;
        $payment->description = "Pedido de produtos alimenticios na loja ".$loja->corporate_name;
        $payment->installments = (int)1;
        $payment->payment_method_id = $request->metodo_pagamento;
        $payment->binary_mode = true;
        $payer = new MercadoPago\Payer();
        $payer->email = $user->email;
        $payer->identification = array(
            "type" => $request->tipo_documento,
            "number" => $request->numero_documento,
        );
        $payment->payer = $payer;

        $payment->save();
        
        if(!$payment->error){

      
            $date_created = new Carbon($payment->date_created);
            $date_created = $date_created->toDateTimeString();

            if($payment->date_approved == null){
                $date_approved = null;
            }else{
                $date_approved = new Carbon($payment->date_approved);
                $date_approved = $date_approved->toDateTimeString();
            }

            if($payment->money_release_date == null){
                $money_release_date = null;
            }else{
                $money_release_date = new Carbon($payment->money_release_date);
                $money_release_date = $money_release_date->toDateTimeString();
            }

            $pagamento = Pagamento::create([
                'mercado_pago_id' => $payment->id,
                'pedido_id' => $pedido_id,
                'user_id' => $user->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
                'date_created' => $date_created,
                'date_approved' => $date_approved,
                'money_release_date' => $money_release_date,
                'transaction_amount' => $payment->transaction_amount,
                'transaction_amount_refunded' => $payment->transaction_amount_refunded,
                'payment_method_id' => $payment->payment_method_id,
                'payment_type_id' => $payment->payment_type_id,
            ]);
        }

        if($payment->error){
            return response([
                'message' => 'Erro ao realizar pagamento',
                'error' => $payment->error
            ], $payment->error->status);
        }else{
            return response([
                'message' => 'Pagamento',
                'pagamento' => $pagamento
            ], 200);
        }
        
    }

    public function updatePagamento($pedido_id)
    {
        $user = auth()->user();
        $pedido = Pedido::where('user_id', $user->id)->where('id', $pedido_id)->first();

        if(empty($pedido)){
            return response([
                'message' => 'Pedido não encontrado'
            ], 404);
        }

        $pagamento = $pedido->pagamento()->first();

        if(empty($pagamento)){
            return response([
                'message' => 'Pagamento não encontrado'
            ], 404);
        }
        
        MercadoPago\SDK::setAccessToken(ENV('MERCADO_PAGO_ACESS_TOKEN'));
        $payment = new MercadoPago\Payment();
        $payment = $payment->get($pagamento->mercado_pago_id);

        if($payment->date_approved == null){
            $date_approved = null;
        }else{
            $date_approved = new Carbon($payment->date_approved);
            $date_approved = $date_approved->toDateTimeString();
        }

        if($payment->money_release_date == null){
            $money_release_date = null;
        }else{
            $money_release_date = new Carbon($payment->money_release_date);
            $money_release_date = $money_release_date->toDateTimeString();
        }

        $pagamento->mercado_pago_id = $payment->id;
        $pagamento->pedido_id = $pedido_id;
        $pagamento->user_id = $user->id;
        $pagamento->status = $payment->status;
        $pagamento->status_detail = $payment->status_detail;
        $pagamento->date_approved = $date_approved;
        $pagamento->money_release_date = $money_release_date;
        $pagamento->transaction_amount = $payment->transaction_amount;
        $pagamento->transaction_amount_refunded = $payment->transaction_amount_refunded;
        $pagamento->payment_method_id = $payment->payment_method_id;
        $pagamento->payment_type_id = $payment->payment_type_id;
        $pagamento->save();

        if($payment->error){
            return response([
                'message' => 'Erro ao realizar pagamento',
                'error' => $payment->error
            ], $payment->error->status);
        }else{
            return response([
                'message' => 'Pagamento',
                'pagamento' => $pagamento
            ], 200);
        }

    }
    
}
