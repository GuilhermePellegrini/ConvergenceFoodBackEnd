<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estoque;
use App\Models\MovimentoEstoques;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstoqueController extends Controller
{
    public function getEstoque($produto_id)
    {
        $estoque = Estoque::where('produto_id', $produto_id)->first();
        if(empty($estoque)){
            return response([
                'message' => 'Estoque não encontrado'
            ], 404);
        }
        $movimento = $estoque->movimento()->get();

        return response([
            'estoque' => $estoque,
            'movimento' => $movimento
        ], 200);
    }

    public function changeEstoque($produto_id, Request $request)
    {
        $request->validate([
            'entrada' => 'required|numeric|between:0,9999999999',
            'saida' => 'required|numeric|between:0,9999999999',
            'motivo' => ['required', Rule::in(['vencimento','ajuste','entrada'])],
        ]);
        
        $user = User::find(auth()->user()->id);
        $lojas = $user->lojas()->get();

        $produto = Produto::where('id', $produto_id)->whereIn('loja_id', $lojas)->first();
        //verificando se Produto existe
        if(empty($produto)){
            //retornando mensagem
            return response([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        $estoque = $produto->estoque()->first();
        
        if(($estoque->quantidade - $request->saida) < 0){
            return response([
                'message' => 'Estoque negativo, alteração não realizada!'
            ], 200);
        }

        //somando estoque atual mais entrada
        $estoque->quantidade = ($estoque->quantidade + $request->entrada);
        //subtraindo estoque atual com saida
        $estoque->quantidade = ($estoque->quantidade - $request->saida);

        $estoque->save();
    
        //adicionando movimento
        MovimentoEstoques::create([
            'estoque_id' => $estoque->id,
            'produto_id' => $produto->id,
            'entrada' => $request->entrada,
            'saida' => $request->saida,
            'motivo' => $request->motivo,
        ]);

        $movimento = $estoque->movimento()->get();

        return response([
            'estoque' => $estoque,
            'movimento' => $movimento
        ], 200);

    }
}
