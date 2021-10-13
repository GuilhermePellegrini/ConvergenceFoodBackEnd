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
        ]);

        $user = auth()->user();

        $carrinho = Carrinho::create([
            'user_id' => $user->id,
            'loja_id' => $request->loja_id,
            'endereco_id' => $request->endereco_id,
            'price' => 0.00,
            'closed' => false,
        ]);

        $carrinhoProduto = $carrinho->produtos()->get();
        $i = 0;
        $produtos = [];
        foreach($carrinhoProduto as $produto){
            $produtos[$i] = [
                'detalhe' => $produto,
                'produto' => $produto->produto()->get()
            ];
            $i++;
        }

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
        ->where('closed', false)
        ->first();

        if(empty($carrinho)){
            return response([
                'message' => 'Carrinho não encontrado'
            ], 404);
        }

        $carrinhoProduto = $carrinho->produtos()->get();
        $i = 0;
        $produtos = [];
        foreach($carrinhoProduto as $produto){
            $produtos[$i] = [
                'detalhe' => $produto,
                'produto' => $produto->produto()->get()
            ];
            $i++;
        }
        $loja = $carrinho->loja()->get();
        $endereco = $carrinho->endereco()->get();

        return response([
            "carrinho" => $carrinho,
            "produtos" => $produtos,
            "loja" => $loja,
            "endereco" => $endereco
        ], 200);
    }

    public function updateCarrinho($carrinho_id, Request $request)
    {
        $request->validate([
            'endereco_id' => 'nullable',
            'loja_id' => 'nullable'
        ]);

        $user = auth()->user();
        $carrinho = Carrinho::where('user_id', $user->id)
        ->where('id', $carrinho_id)
        ->where('closed', false)
        ->first();

        if(empty($carrinho)){
            return response([
                'message' => 'Carrinho não encontrado'
            ], 404);
        }

        if($request->endereco_id){
            $carrinho->endereco_id = $request->endereco_id;
        }
        if($request->loja_id){
            $carrinho->loja_id = $request->loja_id;
        }

        $carrinho->save();

        $produtos = $carrinho->produtos()->get();

        foreach($produtos as $produto){
            if($produto->loja_id != $request->loja_id){
                $produto->delete();
            }
        }

        $carrinhoProduto = $carrinho->produtos()->get();
        $i = 0;
        $produtos = [];
        foreach($carrinhoProduto as $produto){
            $produtos[$i] = [
                'detalhe' => $produto,
                'produto' => $produto->produto()->get()
            ];
            $i++;
        }

        return response([
            "message" => 'Carrinho atualizado com sucesso',
            "carrinho" => $carrinho,
            "produtos" => $produtos,
        ], 200);

    }

    public function insertProduto($carrinho_id, Request $request)
    {
        $request->validate([
            'amount' => 'required|integer',
            'produto_id' => 'required',
            'note' => 'string|max:255|nullable'
        ]);

        $user = auth()->user();

        $carrinho = Carrinho::where('user_id', $user->id)
        ->where('id', $carrinho_id)
        ->first();

        if(empty($carrinho)){
            return response([
                'message' => 'Carrinho não encontrado'
            ], 404);
        }

        $produto = Produto::find($request->produto_id);

        if(empty($produto)){
            return response([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        if($produto->loja_id != $carrinho->loja_id){
            return response([
                'message' => 'Produto de lojas diferentes'
            ], 406);
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
            $carrinho_produto->amount = ($carrinho_produto->amount + $request->amount);
            $carrinho_produto->save();
        }

        $carrinhoProduto = $carrinho->produtos()->get();
        $i = 0;
        $price = 0;
        foreach($carrinhoProduto as $detalhe){
            $produto = $detalhe->produto()->first();
            $price = (($produto->price * $detalhe->amount) + $price);
            $produtos[$i] = [
                'detalhe' => $detalhe,
                'produto' => $produto
            ];
            $i++;
        }

        $carrinho->price = round($price, 2);
        $carrinho->save();

        return response([
            "carrinho" => $carrinho,
            "produtos" => $produtos,
        ], 200);
    }

    public function removeProduto($carrinho_id, $produto_id, Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|nullable',
            'delete' => 'required|boolean',
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
                'message' => 'Carrinho não encontrado'
            ], 404);
        }

        if(empty($carrinho_produto)){
            return response([
                'message' => 'Carrinho Produto não encontrado'
            ], 404);
        }

        if($carrinho_produto->amount <= $request->amount || $request->delete == true){
            $produto = $carrinho_produto->produto()->first();
            $price = ($carrinho_produto->amount * $produto->price);
            $carrinho->price = round(($carrinho->price - $price), 2);
            $carrinho->save();
            $carrinho_produto->delete();
        }else{
            $amount = ($carrinho_produto->amount - $request->amount);
            $carrinho_produto->amount = $amount;
            $carrinho_produto->save();
            $produto = $carrinho_produto->produto()->first();
            $price = $amount * $produto->price;
            $carrinho->price = round(($carrinho->price - $price), 2);
            $carrinho->save();
        }


        $carrinhoProduto = $carrinho->produtos()->get();
        $i = 0;
        $produtos = [];
        foreach($carrinhoProduto as $produto){
            $produtos[$i] = [
                'detalhe' => $produto,
                'produto' => $produto->produto()->get()
            ];
            $i++;
        }

        return response([
            "carrinho" => $carrinho,
            "produtos" => $produtos,
        ], 200);

    }

    public function updateProduto($carrinho_id, $produto_id, Request $request)
    {
        $request->validate([
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
                'message' => 'Carrinho não encontrado'
            ], 404);
        }

        if(empty($carrinho_produto)){
            return response([
                'message' => 'Carrinho Produto não encontrado'
            ], 404);
        }

        $carrinho_produto->note = $request->note;
        $carrinho_produto->save();

        $carrinhoProduto = $carrinho->produtos()->get();
        $i = 0;
        $produtos = [];
        foreach($carrinhoProduto as $produto){
            $produtos[$i] = [
                'detalhe' => $produto,
                'produto' => $produto->produto()->get()
            ];
            $i++;
        }

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

        $carrinho_produtos = $carrinho->produtos()->get();
        
        if(empty($carrinho)){
            return response([
                'message' => 'Carrinho não encontrado'
            ], 404);
        }

        if(empty($carrinho_produtos)){
            return response([
                'message' => 'Carrinho Produtos não encontrado'
            ], 404);
        }

        foreach($carrinho_produtos as $carrinho_produto){
            $carrinho_produto->delete();
        }

        $carrinho->price = 0.00;
        $carrinho->save();

        return response([
            "message" => 'Carrinho limpo',
        ], 200);
    }

}
