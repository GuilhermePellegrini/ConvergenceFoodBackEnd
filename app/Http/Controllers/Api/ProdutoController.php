<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Estoque;
use App\Models\Foto;
use App\Models\Loja;
use App\Models\MovimentoEstoques;
use App\Models\Produto;
use App\Models\ProdutoFoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:60',
            'price' => 'required|numeric|between:0.01,9999999999.99',
            'description' => 'required|string|max:255',
            'loja_id' => 'required',
            'categoria_id' => 'required',
            'quantidade' => 'required|numeric|between:1,9999999999',
            'images' => 'required',
            'images.*' => 'required|image',
        ]);

        $produto = Produto::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'loja_id' => $request->loja_id,
            'categoria_id' => $request->categoria_id,
        ]);
        
        if($request->hasfile('images')){
            $i = 1;
            foreach($request->images as $image){
                $path = $image->store('produto', 's3');
                $aws = Storage::url($path);
                $foto = Foto::create([
                    'path' => $path,
                    'order' => $i,
                    'aws' => $aws
                ]);
                
                ProdutoFoto::create([
                    'produto_id' => $produto->id,
                    'foto_id' => $foto->id
                ]);
                
                $i++;
            }
        }

        $estoque = Estoque::create([
            'produto_id' => $produto->id,
            'loja_id' => $request->loja_id,
            'quantidade' => $request->quantidade,
        ]);

        MovimentoEstoques::create([
            'estoque_id' => $estoque->id,
            'produto_id' => $produto->id,
            'entrada' => $request->quantidade,
            'motivo' => 'entrada',
            'saida' => 0
        ]);
        
        $fotos = $produto->fotos()->get();

        return response([
            'produto' => $produto,
            'fotos' => $fotos,
            'estoque' => $estoque,
        ], 201);
    }

    public function updateProduto($produto_id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:60',
            'price' => 'required|numeric|between:0.01,9999999999.99',
            'description' => 'required|string|max:255',
            'categoria_id' => 'required',
        ]);

        $user = User::find(auth()->user()->id);

        $lojas = $user->lojas()->get();
        $lojasId = [];
        $i = 0;
        foreach($lojas as $lojas){
            $lojasId[$i] = $lojas->id;
            $i++;
        }
        //verificando se loja do usuario é a mesma do produto
        $produto = Produto::where('id', $produto_id)->whereIn('loja_id', $lojasId)->first();

        if(empty($produto)){
            return response([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        $produto->name = $request->name;
        $produto->price = $request->price;
        $produto->description = $request->description;
        $produto->save();

        $estoque = Estoque::where('produto_id', $produto_id)->first();

        $fotos = $produto->fotos()->get();
        
        return response([
            'message' => 'Produto updated successfully',
            'produto' => $produto,
            'estoque' => $estoque,
            'fotos' => $fotos
        ], 200);
    }

    public function addFoto($produto_id, Request $request)
    {
        $request->validate([
            'image' => 'required|image',
        ]);

        $image = $request->file('image');
        $produto = Produto::find($produto_id);
        if(empty($produto)){
            return response([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        $lastFoto = $produto->fotos()->orderBy('order', 'desc')->first();
        $i = $lastFoto->order + 1;
        if($request->hasfile('image')){
            $path = $image->store('produto', 's3');
            $aws = Storage::url($path);

            $foto = Foto::create([
                'path' => $path,
                'order' => $i,
                'aws' => $aws
            ]);
            
            ProdutoFoto::create([
                'produto_id' => $produto->id,
                'foto_id' => $foto->id
            ]);
            
            $i++;
        }
        
        $estoque = Estoque::where('produto_id', $produto_id)->first();

        $fotos = $produto->fotos()->get();

        return response([
            'message' => 'Produto atualizado com sucesso',
            'produto' => $produto,
            'estoque' => $estoque,
            'fotos' => $fotos
        ], 200);

    }

    public function updateOrderFoto($produto_id, Request $request)
    {
        $request->validate([
            'foto_id' => 'required',
            'order' => 'required',
        ]);

        $produtoFoto = ProdutoFoto::where('produto_id', $produto_id)->where('foto_id', $request->foto_id)->first();

        $foto = Foto::find($produtoFoto->foto_id);
        $foto->order = $request->order;
        $foto->save();

        return response([
            'message' => 'Ordem das fotos atualizadas com sucesso'
        ], 200);
    }

    //method Delete
    public function delete($produto_id)
    {
        //buscando usuario autenticado
        $user = User::find(auth()->user()->id);
        
        $lojas = $user->lojas()->get();
        $lojasId = [];
        $i = 0;
        foreach($lojas as $lojas){
            $lojasId[$i] = $lojas->id;
            $i++;
        }
        //verificando se loja do usuario é a mesma do produto
        $produto = Produto::where('id', $produto_id)->whereIn('loja_id', $lojasId)->first();

        //verificando se fotos do produto existem
        if(empty($produto)){
            //retornando mensagem
            return response([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        //buscando fotos produto
        $produtoFotos = ProdutoFoto::where('produto_id', $produto_id)->get();

        //rodando todas as fotos dos produtos
        foreach ($produtoFotos as $produtoFoto){
            $foto = Foto::find($produtoFoto->id);
            Storage::disk('s3')->delete($foto->aws);
            //deletando foto do produto e pivot
            $foto->delete();
            $produtoFoto->delete();
        }

        //deletando estoque e movimentações do estoque
        $estoque = $produto->estoque()->first();
        $movimentacao = $estoque->movimento()->get();
        if($movimentacao != null){
            foreach($movimentacao as $movimento){
                $movimento->delete();
            }
        }
        $estoque->delete();

        //deletando produto
        $produto->delete();
        
        return response([
            'message' => 'Produto deletado com sucesso'
        ], 200);
    }

    //method deleteFoto
    public function deleteFoto($produto_id, $foto_id)
    {
        //buscando usuario autenticado
        $user = User::find(auth()->user()->id);

        //verificando se a foto pertence ao produto enviado
        $produtoFoto = ProdutoFoto::where('produto_id', $produto_id)->where('foto_id', $foto_id)->first();
        if(empty($produtoFoto)){
            return response([
                'message' => 'Foto Produto não encontrado'
            ], 404);
        }

        $lojas = $user->lojas()->get();
        $lojasId = [];
        $i = 0;
        foreach($lojas as $lojas){
            $lojasId[$i] = $lojas->id;
            $i++;
        }
        //verificando se loja do usuario é a mesma do produto
        $produto = Produto::where('id', $produto_id)->whereIn('loja_id', $lojasId)->first();
        if(empty($produto)){
            return response([
                'message' => 'Foto Produto não encontrado'
            ], 404);
        }

        //buscando foto
        $foto = Foto::find($produtoFoto->foto_id);
        Storage::disk('s3')->delete($foto->aws);
        //deletando foto e pivot produtoFoto
        $foto->delete();
        $produtoFoto->delete();

        return response([
            'message' => 'Foto do produto deletada com sucesso'
        ], 200);

    }

    public function getLojas()
    {
        $lojas = Loja::all();
        return response([
            'lojas' => $lojas,
        ], 200);
    }

    public function getProduto($produto_id)
    {
        //buscando produto
        $produto = Produto::find($produto_id);


        //verificando se produto existe
        if(empty($produto)){
            return response([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        //buscando fotos do produto
        $fotos = $produto->fotos()->get();

        //buscando estoque do produto
        $estoque = $produto->estoque()->get();

        return response([
            'produto' => $produto,
            'estoque' => $estoque,
            'fotos' => $fotos,
        ], 200);
    }

    public function getLojaProdutos($loja_id)
    {
        //buscando loja dos produtos
        $loja = Loja::find($loja_id);

        //verificando se loja existe
        if(!$loja){
            return response([
                'message' => 'Loja não encontrado'
            ], 404);
        }

        //buscando produtos das lojas
        $produtos = $loja->produtos()->get();

        //criando array de resposta e contado i
        $produto_response = array();
        $i = 0;
        //rodando todos os produtos encontrados 
        foreach ($produtos as $produto){
            //adicionando produto em array de retorno
            $produto_response[$i] = $produto;
            //buscando fotos do produto
            $fotos = $produto->fotos()->get();
            //iniciando contado de fotos f e rodando fotos do produto
            $f = 0;
            foreach ($fotos as $foto){
                //adicionando foto em array de retorno
                $produto_response[$i]['fotos'][$f] = $foto;
                //somando contador f
                $f++;
            }

            //buscando estoque do produto
            $estoque = $produto->estoque()->get();
            $produto_response[$i]['estoque'] = $estoque;
            
            //somando contador i
            $i++;
        }
        
        
        return response([
            'loja' => $loja,
            'produtos' => $produto_response,
        ], 200);
    }

    public function getAll()
    {
        //buscando todos produtos
        $produtos = Produto::all();
        
        //verificando se existe algum produto registrado
        if(!$produtos){
            return response([
                'message' => 'no Produtos found'
            ], 404);
        }

        //criando array de resposta e contado i
        $produto_response = array();
        $i = 0;
        //rodando todos os produtos encontrados 
        foreach ($produtos as $produto){
            //adicionando produto em array de retorno
            $produto_response[$i] = $produto;
            //buscando fotos do produto
            $fotos = $produto->fotos()->get();

            $produto_response[$i]['estoque'] = $produto->estoque()->get();
            //iniciando contado de fotos f e rodando fotos do produto
            $f = 0;
            foreach ($fotos as $foto){
                //adicionando foto em array de retorno
                $produto_response[$i]['fotos'][$f] = $foto;
                //somando contador f
                $f++;
            }
            //somando contador i
            $i++;

        }
        
        return response([
            'produtos' => $produto_response,
        ], 200);
    }

    public function getCategorias()
    {
        $categorias = Categoria::all();

        return response([
            'categorias' => $categorias,
        ], 200);
    }

}
