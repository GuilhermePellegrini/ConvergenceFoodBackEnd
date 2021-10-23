<?php

namespace App\Http\Controllers\Api;

use App\Events\CancelarPedido;
use App\Http\Controllers\Controller;
use App\Models\Carrinho;
use App\Models\MovimentoEstoques;
use App\Models\Pedido;
use App\Models\PedidoProduto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use MercadoPago;

class PedidoController extends Controller
{
    
    public function createPedido($carrinho_id, Request $request)
    {
        $user = auth()->user();
        $user = User::find($user->id);

        $carrinho = Carrinho::where('id', $carrinho_id)->where('user_id', $user->id)->first();

        if(empty($carrinho) || !$carrinho){
            return response([
                'message' => 'Carrinho não encontrado'
            ], 404);
        }

        if($carrinho->closed == true){
            $pedido = Pedido::where('carrinho_id', $carrinho->id)->first();

            return response([
                'message' => 'Carrinho se encontra finalizado',
                'pedido_id' => $pedido->id
            ], 422);
        }

        $carrinho->closed = true;
        $carrinho->save();
        
        $pedido = Pedido::create([
            'loja_id' => $carrinho->loja_id,
            'user_id' => $carrinho->user_id,
            'endereco_id' => $carrinho->endereco_id,
            'carrinho_id' => $carrinho->id,
            'cupom_id' => $carrinho->cupom_id,
            'price' => $carrinho->price,
            'discount' => $carrinho->discount,
            'note' => $carrinho->note,
            'status' => 'Pagamento_Pendente'
        ]);

        $carrinhoProdutos = $carrinho->produtos()->get();

        foreach($carrinhoProdutos as $carrinhoProduto){
            PedidoProduto::create([
                'pedido_id' => $pedido->id,
                'produto_id' => $carrinhoProduto->produto_id,
                'amount' => $carrinhoProduto->amount,
                'note' => $carrinhoProduto->note
            ]);

            $produto = $carrinhoProduto->produto()->first();
            $estoque = $produto->estoque()->first();

            $estoque->quantidade = ($estoque->quantidade - $carrinhoProduto->amount);
            $estoque->save();

            MovimentoEstoques::create([
                'pedido_id' => $pedido->id,
                'estoque_id' => $estoque->id,
                'produto_id' => $produto->id,
                'entrada' => null,
                'saida' => $carrinhoProduto->amount,
                'motivo' => null,
            ]);
        }

        $pedidoProdutos = $pedido->produtos()->get();
        $i = 0;
        $produtos = [];
        foreach($pedidoProdutos as $produto){
            $produtos[$i] = [
                'detalhe' => $produto,
                'produto' => $produto->produto()->first()
            ];

            $i++;
        }
        $loja = $pedido->loja()->get();
        $endereco = $pedido->endereco()->get();
        $pagamento = $pedido->pagamento()->get();

        return response([
            "message" => 'Pedido Realizado com sucesso',
            "pedido" => $pedido,
            "produtos" => $produtos,
            "loja" => $loja,
            "endereco" => $endereco,
            "pagamento" => $pagamento
        ], 200);

    }

    public function getPedido($pedido_id)
    {
        $user = auth()->user();
        $user = User::find($user->id);
        $pedido = Pedido::where('id', $pedido_id)->where('user_id', $user->id)->first();

        if(empty($pedido) || !$pedido){
            return response([
                'message' => 'Pedido não encontrado'
            ], 404);
        }

        $pedidoProdutos = $pedido->produtos()->get();
        $i = 0;
        $produtos = [];
        foreach($pedidoProdutos as $produto){
            $produtos[$i] = [
                'detalhe' => $produto,
                'produto' => $produto->produto()->get()
            ];
            $i++;
        }
        $loja = $pedido->loja()->get();
        $endereco = $pedido->endereco()->get();
        $pagamento = $pedido->pagamento()->get();
        return response([
            "pedido" => $pedido,
            "produtos" => $produtos,
            "loja" => $loja,
            "endereco" => $endereco,
            "pagamento" => $pagamento
        ], 200);
    }

    public function getAll()
    {
        $user = User::find(auth()->user()->id)->first();
        $pedidos = $user->pedidos()->get();

        $c = 0;
        foreach($pedidos as $pedido){
            $pedidoProdutos = $pedido->produtos()->get();
            $i = 0;
            $produtos = [];
            foreach($pedidoProdutos as $produto){
                $produtos[$i] = [
                    'detalhe' => $produto,
                    'produto' => $produto->produto()->get()
                ];
                $i++;
            }
            $loja = $pedido->loja()->get();
            $endereco = $pedido->endereco()->get();
            $pagamento = $pedido->pagamento()->get();
            $allPedidos[$c] = [
                "pedido" => $pedido,
                "produtos" => $produtos,
                "loja" => $loja,
                "endereco" => $endereco,
                "pagamento" => $pagamento
            ];
        }

        return response([
            "pedidos" => $allPedidos,
        ], 200);
    }

    public function updatePedido($pedido_id, Request $request)
    {
        $request->validate([
            'status' => ['required', Rule::in(['Pendente', 'Aceito', 'Cancelado', 'Entrega', 'Finalizado'])]
        ]);

        $pedido = Pedido::find($pedido_id)->first();
        
        if((empty($pedido) || !$pedido)){
            return response([
                'message' => 'Pedido não encontrado'
            ], 404);
        }

        $pedido->status = $request->status;
        $pedido->save();

        $pagamento = $pedido->pagamento()->first();

        if($request->status == 'Cancelado' && $pagamento->status == "approved"){
            if(!empty($pagamento)){
                MercadoPago\SDK::setAccessToken(ENV('MERCADO_PAGO_ACESS_TOKEN'));
                $payment = new MercadoPago\Payment();
                $payment = $payment->get($pagamento->mercado_pago_id);
                $payment->refund();
    
                $pagamento->status = $payment->status;
                $pagamento->status_detail = $payment->status_detail;
                $pagamento->transaction_amount_refunded = $payment->transaction_amount_refunded;
                $pagamento->save();
            }
        }

        $pedidoProdutos = $pedido->produtos()->get();
        $i = 0;
        $produtos = [];
        foreach($pedidoProdutos as $produto){
            $produtos[$i] = [
                'detalhe' => $produto,
                'produto' => $produto->produto()->get()
            ];
            $i++;
        }
        $loja = $pedido->loja()->get();
        $endereco = $pedido->endereco()->get();
        $pagamento = $pedido->pagamento()->get();
        
        return response([
            "message" => 'Pedido atualizado com sucesso',
            "pedido" => $pedido,
            "produtos" => $produtos,
            "loja" => $loja,
            "endereco" => $endereco,
            "pagamento" => $pagamento
        ], 200);

    }

}
