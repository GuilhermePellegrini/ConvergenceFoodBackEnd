<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\CepController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\AuthController;
use App\Models\Cidade;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Rotas Auth
Route::group(['prefix' => '/auth'], function(){
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/register/admin', [ApiAuthController::class, 'registerAdmin']);
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/forgotPassword', [ApiAuthController::class, 'forgotPassword']);
    Route::post('/resetPassword', [ApiAuthController::class, 'resetPassword']);
});

//Rota Cep
Route::group(['prefix' => '/cep'], function(){
    Route::post('/', [CepController::class, 'cep']);
    Route::get('/cidade/{estado_id}', [CepController::class, 'cidade']);
    Route::get('/estados', [CepController::class, 'estados']);
});

//Rotas Gerais
/*Produtos*/
Route::get('/produtos', [ProdutoController::class, 'getAll']);
Route::get('/produtos/{loja_id}', [ProdutoController::class, 'getLoja']);
Route::get('/produto/{produto_id}', [ProdutoController::class, 'getProduto']);

Route::group(['middleware' => 'auth:sanctum'], function (){

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
    Route::post('/auth/changePassword', [ApiAuthController::class, 'changePassword']);

    Route::group(['middleware' => 'sanctum.abilities:admin'], function (){
        Route::post('/produto', [ProdutoController::class, 'create']);
        Route::delete('/produto/{produto_id}', [ProdutoController::class, 'delete']);
        Route::put('/produto/{produto_id}', [ProdutoController::class, 'update']);
    });
});