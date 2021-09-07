<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\CepController;
use App\Http\Controllers\Api\EstoqueController;
use App\Http\Controllers\Api\ProdutoController;
use App\Models\User;
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

    //Auth user
    Route::get('/user',  [ApiAuthController::class, 'getUser']);
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
    Route::post('/auth/changePassword', [ApiAuthController::class, 'changePassword']);
    Route::post('/auth/updateUser', [ApiAuthController::class, 'updateUser']);
    
    //Administrador Lojas
    Route::group(['middleware' => 'sanctum.abilities:admin'], function (){

        //Auth Loja
        Route::put('/loja/{loja_id}', [ApiAuthController::class, 'updateLoja']);
        Route::delete('/loja/{loja_id}', [ApiAuthController::class, 'deleteLoja']);
        Route::post('/loja/create', [ApiAuthController::class, 'createLoja']);

        //Produto
        Route::post('/produto', [ProdutoController::class, 'create']);
        Route::delete('/produto/{produto_id}', [ProdutoController::class, 'delete']);
        Route::put('/produto/{produto_id}', [ProdutoController::class, 'updateProduto']);
        Route::put('/produto/foto/{produto_id}', [ProdutoController::class, 'addFoto']);
        Route::post('/produto/foto/{produto_id}', [ProdutoController::class, 'updateOrderFoto']);
        Route::delete('/produto/foto/{produto_id}/{foto_id}', [ProdutoController::class, 'deleteFoto']);
        
        //Estoque
        Route::get('/estoque/{produto_id}', [EstoqueController::class, 'getEstoque']);
        Route::put('/estoque/{produto_id}', [EstoqueController::class, 'changeEstoque']);
    });

});