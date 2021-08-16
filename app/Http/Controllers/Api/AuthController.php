<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Endereco;
use App\Models\Loja;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users,email',
            'password' => 'required|string|confirmed',
            'cpf' => 'required|unique:users,cpf',
            'genero' => ['required', Rule::in(['m','f', 'o', 'n'])],
            'admin' => 'boolean',
            'address_name' => 'required|string|max:255',
            'cep' => 'required|string|size:8',
            'address' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'number' => 'required|string',
            'complement' => 'string|max:255',
            'cidade_id' => 'required|integer',
            'estado_id' => 'required|integer',
        ]);

        $endereco =  Endereco::create([
            'name' => $request->address_name,
            'cep' => $request->cep,
            'address' => $request->address,
            'district' => $request->district,
            'number' => $request->number,
            'complement' => $request->complement,
            'cidade_id' => $request->cidade_id,
            'estado_id' => $request->estado_id,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'genero' => $request->genero,
            'password' => Hash::make($request->password),
            'endereco_id' => $endereco->id
        ]);

        $token = $user->createToken(env('APP_API'))->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|unique:users,cpf',
            'genero' => ['required', Rule::in(['m','f', 'o', 'n'])],
            'password' => 'required|string|confirmed',
            'admin' => 'required|boolean',
            'address_name' => 'required|string|max:255',
            'cep' => 'required|string|size:8',
            'address' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'number' => 'required|string',
            'complement' => 'string|max:255',
            'cidade_id' => 'required|integer',
            'estado_id' => 'required|integer',
            'corporate_name' => 'required|string|max:255',
            'trading_name' => 'required|string|max:20',
            'cnpj' => 'required|string|size:14',
            'web_site' => 'string|max:255',
            'phone' => 'required|string|max:11',
            'cel_phone' => 'size:11',
            'email_loja' => 'required|email|max:255',
            'representante_legal' => 'required|string|max:255',
            'representante_legal_email' => 'required|email|max:255',
        ]);

        $endereco =  Endereco::create([
            'name' => $request->address_name,
            'cep' => $request->cep,
            'address' => $request->address,
            'district' => $request->district,
            'number' => $request->number,
            'complement' => $request->complement,
            'cidade_id' => $request->cidade_id,
            'estado_id' => $request->estado_id,
        ]);

        $loja = Loja::create([
            'corporate_name' => $request->corporate_name,
            'trading_name' => $request->trading_name,
            'cnpj' => $request->cnpj,
            'web_site' => $request->web_site,
            'phone' => $request->phone,
            'cel_phone' => $request->cel_phone,
            'email' => $request->email,
            'representante_legal' => $request->representante_legal,
            'representante_legal_email' => $request->representante_legal_email,
            'endereco_id' => $endereco->id,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'genero' => $request->genero,
            'password' => Hash::make($request->password),
            'loja_id' => $loja->id,
            'endereco_id' => $endereco->id,
            'admin' => $request->admin,
        ]);

        $token = $user->createToken(env('APP_API'), ['admin'])->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function updateUser(Request $request)
    {
        
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        //Check email and password
        $user = User::where('email', $request->email)->first();
        if(!$user || !Hash::check($request->password, $user->password)){
            return response([
                'message' => 'Bad credentials'
            ], 401);
        }

        $user->tokens()->delete();

        if($user->admin == true){
            $token = $user->createToken(env('APP_API'), ['admin'])->plainTextToken;
        }else{
            $token = $user->createToken(env('APP_API'), [''])->plainTextToken;
        }
        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 200);
    }
    
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response([
            'message' => 'Logged out'
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        $user = auth()->user();

        //Verificando senha antiga
        if(!$user || !Hash::check($request->old_password, $user->password)){
            return response([
                'message' => 'old password is incorrect'
            ], 401);
        }

        //Alterando senha
        $user->password = Hash::make($request->password);
        $user->save();
        auth()->user()->tokens()->delete();
        return response([
            'message' => 'password changed successfully'
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
        ? response([
            'message' => __($status)
        ], 200)
        : response([
            'message' => __($status)
        ], 401);

    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
    
                $user->save();
                
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
        ? response([
            'message' => __($status)
        ], 200)
        : response([
            'email' => __($status)
        ], 401);

    }

}
