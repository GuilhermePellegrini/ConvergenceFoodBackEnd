<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assinatura;
use Illuminate\Http\Request;

class AssinaturaController extends Controller
{
    
    public function getAll()
    {
        $assinaturas = Assinatura::all();
        return response([
            'assinaturas' => $assinaturas
        ], 200);
    }

}
