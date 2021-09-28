<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrinho;
use App\Models\CarrinhoProduto;
use App\Models\Produto;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
{

    public function createCarrinho(Request $request)
    {
        $request->validate([
            'loja_id' => 'required',
            'endereco_id' => 'required',
            'price' => 'required'
        ]);

        $user = auth()->user();

        $carrinho = Carrinho::create([
            'user_id' => $user->id,
            'loja_id' => $request->loja_id,
            'endereco_id' => $request->endereco_id,
            'price' => 0.00
        ]);

        $produtos = $carrinho->produtos()->get();

        return response([
            "carrinho" => $carrinho,
            "produtos" => $produtos,
        ], 200);
    }

    public function getCarrinho($carrinho_id)
    {
        $user = auth()->user();

        $carrinho = Carrinho::where('user_id', $user->id)
        ->where('id', $carrinho_id)
        ->first();

        if(empty($carrinho)){
            return response([
                'message' => 'Carrinho not found'
            ], 404);
        }

        $produtos = $carrinho->produtos()->get();

        return response([
            "carrinho" => $carrinho,
            "produtos" => $produtos,
        ], 200);
    }

    public function insertProduto($carrinho_id, Request $request)
    {
        $request->validate([
            'amount' => 'request|integer',
            'produto_id' => 'required',
            'note' => 'max:255'
        ]);

        $user = auth()->user();

        $carrinho = Carrinho::where('user_id', $user->id)
        ->where('id', $carrinho_id)
        ->first();

        if(empty($carrinho)){
            return response([
                'message' => 'Carrinho not found'
            ], 404);
        }

        $produto = Produto::find($request->produto_id);

        if(empty($produto)){
            return response([
                'message' => 'Produto not found'
            ], 404);
        }

        $carrinho_produto = CarrinhoProduto::where('carrinho_id', $carrinho_id)
        ->where('produto_id', $produto->id)
        ->first();

        if(empty($carrinho_produto)){
            CarrinhoProduto::create([
                'carrinho_id' => $carrinho->id,
                'amount' => $request->amount,
                'produto_id' => $produto->id,
                'note' => $request->note,
            ]);
        }else{
            $carrinho_produto->amount = ($carrinho_produto->amount + 1);
            $carrinho_produto->save();
        }

        $produtos = $carrinho->produtos()->get();

        return response([
            "carrinho" => $carrinho,
            "produtos" => $produtos,
        ], 200);
    }

    public function removeProduto($carrinho_id, $produto_id)
    {
        $user = auth()->user();

        $carrinho = Carrinho::where('user_id', $user->id)
        ->where('id', $carrinho_id)
        ->first();

        $carrinho_produto = CarrinhoProduto::where('carrinho_id', $carrinho_id)
        ->where('produto_id', $produto_id)
        ->first();

        if(empty($carrinho)){
            return response([
                'message' => 'Carrinho not found'
            ], 404);
        }

        if(empty($carrinho_produto)){
            return response([
                'message' => 'Carrinho Produto not found'
            ], 404);
        }

        $carrinho_produto->delete();

        $produtos = $carrinho->produtos()->get();

        return response([
            "carrinho" => $carrinho,
            "produtos" => $produtos,
        ], 200);

    }

    public function updateProduto($carrinho_id, $produto_id, Request $request)
    {
        $request->validate([
            'amount' => 'request|integer',
            'note' => 'max:255'
        ]);

        $user = auth()->user();

        $carrinho = Carrinho::where('user_id', $user->id)
        ->where('id', $carrinho_id)
        ->first();
        
        $carrinho_produto = CarrinhoProduto::where('carrinho_id', $carrinho_id)
        ->where('produto_id', $produto_id)
        ->first();

        if(empty($carrinho)){
            return response([
                'message' => 'Carrinho not found'
            ], 404);
        }

        if(empty($carrinho_produto)){
            return response([
                'message' => 'Carrinho Produto not found'
            ], 404);
        }

        $carrinho_produto->amount = $request->amount;
        $carrinho_produto->note = $request->note;
        $carrinho_produto->save();

        $produtos = $carrinho->produtos()->get();

        return response([
            "carrinho" => $carrinho,
            "produtos" => $produtos,
        ], 200);

    }

    public function cleanCarrinho($carrinho_id)
    {
        $user = auth()->user();

        $carrinho = Carrinho::where('user_id', $user->id)
        ->where('id', $carrinho_id)
        ->first();

        $carrinho_produtos = CarrinhoProduto::where('carrinho_id', $carrinho->id)
        ->get();
        
        if(empty($carrinho)){
            return response([
                'message' => 'Carrinho not found'
            ], 404);
        }

        if(empty($carrinho_produto)){
            return response([
                'message' => 'Carrinho Produto not found'
            ], 404);
        }

        foreach($carrinho_produtos as $carrinho_produto){
            $carrinho_produto->delete();
        }

        return response([
            "message" => 'Carrinho limpo',
        ], 200);
    }

}
