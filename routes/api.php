<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\CepController;
use App\Http\Controllers\Api\EstoqueController;
use App\Http\Controllers\Api\ProdutoController;
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
    Route::get('/', [CepController::class, 'cep']);
    Route::get('/cidade/{estado_id}', [CepController::class, 'cidade']);
    Route::get('/estados', [CepController::class, 'estados']);
});

//Rotas Gerais
/*Produtos*/
Route::get('/produtos', [ProdutoController::class, 'getAll']);
Route::get('/produto/{produto_id}', [ProdutoController::class, 'getProduto']);
Route::get('/produtos/{loja_id}', [ProdutoController::class, 'getLoja']);

/*Loja*/
Route::get('/lojas', [ProdutoController::class, 'getLojas']);

Route::group(['middleware' => 'auth:sanctum'], function (){

    //Auth user
    Route::get('/user',  [ApiAuthController::class, 'getUser']);
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
    Route::post('/auth/changePassword', [ApiAuthController::class, 'changePassword']);
    Route::put('/auth/updateUser', [ApiAuthController::class, 'updateUser']);

    //Carrinho
    Route::post('/carrinho', [CarrinhoController::class, 'createCarrinho']);
    Route::get('/carrinho', [CarrinhoController::class, 'getCarrinho']);
    Route::post('/carrinho/{carrinho_id}', [CarrinhoController::class, 'insertProduto']);
    Route::delete('/carrinho/{carrinho_id}/{produto_id}', [CarrinhoController::class, 'removeProduto']);
    Route::put('/carrinho/{carrinho_id}/{produto_id}', [CarrinhoController::class, 'updateProduto']);
    Route::delete('/carrinho/{carrinho_id}', [CarrinhoController::class, 'cleanCarrinho']);
    
    //Administrador Lojas
    Route::group(['middleware' => 'sanctum.abilities:admin'], function (){

        //Auth Loja
        Route::delete('/loja/{loja_id}', [ApiAuthController::class, 'deleteLoja']);
        Route::post('/loja/create', [ApiAuthController::class, 'createLoja']);
        Route::post('/loja/{loja_id}', [ApiAuthController::class, 'updateLoja']);

        //Produto
        Route::post('/produto', [ProdutoController::class, 'create']);
        Route::delete('/produto/{produto_id}', [ProdutoController::class, 'delete']);
        Route::put('/produto/{produto_id}', [ProdutoController::class, 'updateProduto']);
        Route::post('/produto/foto/{produto_id}', [ProdutoController::class, 'addFoto']);
        Route::put('/produto/foto/{produto_id}', [ProdutoController::class, 'updateOrderFoto']);
        Route::delete('/produto/foto/{produto_id}/{foto_id}', [ProdutoController::class, 'deleteFoto']);
        
        //Estoque
        Route::get('/estoque/{produto_id}', [EstoqueController::class, 'getEstoque']);
        Route::put('/estoque/{produto_id}', [EstoqueController::class, 'changeEstoque']);

    });

});