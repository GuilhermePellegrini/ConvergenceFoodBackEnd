<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Foto;
use App\Models\Loja;
use App\Models\Produto;
use App\Models\ProdutoFoto;
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
            'images' => 'required',
            'images.*' => 'image',
        ]);

        $produto = Produto::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'loja_id' => $request->loja_id,
        ]);

        if($request->hasfile('images')){
            $i = 1;
            foreach($request->images as $image){
                $path = $image->store('public/produtos');
                $path = Storage::url($path);

                $foto = Foto::create([
                    'path' => $path,
                    'order' => $i
                ]);

                ProdutoFoto::create([
                    'produto_id' => $produto->id,
                    'foto_id' => $foto->id
                ]);

                $i++;
            }
        }

        $fotos = $produto->fotos()->get();

        return response([
            'produto' => $produto,
            'fotos' => $fotos
        ], 201);
    }

    public function update(Request $request, $produto_id)
    {
        $request->validate([
            'name' => 'required|string|max:60',
            'price' => 'required|numeric|between:0.01,9999999999.99',
            'description' => 'required|string|max:255',
            'loja_id' => 'required',
            'categoria_id' => 'required',
        ]);

        $user = auth()->user();
        $produto = Produto::where('id', $produto_id)->where('loja_id', $user->loja_id)->first();

        if(!$produto){
            return response([
                'message' => 'Produto not found'
            ], 404);
        }

        $produto->name = $request->name;
        $produto->price = $request->price;
        $produto->description = $request->description;
        $produto->loja_id = $request->loja_id;
        $produto->save();
        
        return response([
            'message' => 'Produto updated successfully',
            'produto' => $produto,
            'fotos' => $produto->fotos()->get()
        ], 200);
    }

    public function addFoto($produto_id, Request $request)
    {

    }

    public function updateOrderFoto($produto_id, Request $request)
    {

    }

    public function delete($produto_id)
    {
        //buscando usuario autenticado
        $user = auth()->user();

        //verificando se loja do usuario é a mesma do produto
        $produtoFotos = ProdutoFoto::where('id', $produto_id)->where('loja_id', $user->loja_id)->get();

        if(!$produtoFotos){
            return response([
                'message' => 'Produto not found'
            ], 404);
        }

        //rodando todas as fotos dos produtos
        foreach ($produtoFotos as $produtoFoto){
            $fotos = Foto::find($produtoFoto->id);
            //deletando foto do produto e pivot
            $fotos->delete();
            $produtoFoto->delete();
        }

        //buscando e deletando produto
        $produto = Produto::find($produto_id);
        $produto->delete();
        
        return response([
            'message' => 'Produto deleted successfully'
        ], 200);
    }

    public function deleteFoto($produto_id, $foto_id)
    {
        //buscando usuario autenticado
        $user = auth()->user();

        //verificando se a foto pertence ao produto enviado
        $produtoFoto = ProdutoFoto::where('produto_id', $produto_id)->where('foto_id', $foto_id)->first();
        if(!$produtoFoto){
            return response([
                'message' => 'Foto Produto not found'
            ], 404);
        }

        //verificando se loja do usuario é a mesma do produto
        $produto = Produto::where('id', $produto_id)->where('loja_id', $user->loja_id)->first();
        if(!$produto){
            return response([
                'message' => 'Foto Produto not found'
            ], 404);
        }

        //buscando foto
        $foto = Foto::find($produtoFoto->foto_id);
        
        //deletando foto e pivot produtoFoto
        $foto->delete();
        $produtoFoto->delete();

    }

    public function getProduto($produto_id)
    {
        //buscando produto
        $produto = Produto::find($produto_id);

        //verificando se produto existe
        if(!$produto){
            return response([
                'message' => 'Foto Produto not found'
            ], 404);
        }

        //buscando fotos do produto
        $fotos = $produto->fotos()->get();

        return response([
            'produto' => $produto,
            'fotos' => $fotos
        ], 200);
    }

    public function getLoja($loja_id)
    {
        //buscando loja dos produtos
        $loja = Loja::find($loja_id);

        //verificando se loja existe
        if(!$loja){
            return response([
                'message' => 'Loja not found'
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
            //somando contador i
            $i++;
        }
        
        return response([
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

}
