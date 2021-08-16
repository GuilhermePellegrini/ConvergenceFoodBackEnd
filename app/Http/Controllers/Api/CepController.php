<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cidade;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CepController extends Controller
{
    public function cidade($estado_id)
    {
        $cidade = Cidade::where('estado_id', $estado_id)->get();
        return response()->json($cidade);
    }

    public function estados()
    {
        $estados = Estado::all();
        return response()->json($estados);
    }

    public function cep(Request $request)
    {
        $request->validate([
            'cep' => 'required|size:8'
        ]);

        $response = Http::get('http://viacep.com.br/ws/'.$request->cep.'/json/');
        $estado = Estado::where('ibge', substr($response->json('ibge'), 0, 2))->first();
        $cidades = Cidade::where('estado_id', $estado->id)->get();
        $cidade = Cidade::where('ibge', $response->json('ibge'))->first();
        return response([
            "viaCep" => $response->json(),
            "estado" => $estado,
            "cidade_id" => $cidade->id,
            "cidades" => $cidades,
        ], 200);
    }
}
