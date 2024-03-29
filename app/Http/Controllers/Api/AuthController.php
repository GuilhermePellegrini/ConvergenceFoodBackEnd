<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssinaturaUser;
use App\Models\Endereco;
use App\Models\EnderecoUser;
use App\Models\Loja;
use App\Models\LojaUser;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{

    public function getUser()
    {
        $user = User::find($user = auth()->user()->id);
        $assinatura = $user->assinaturas()->first();
        if(!empty($assinatura)){
            $userAssinatura = AssinaturaUser::where('user_id', $user->id)->where('assinatura_id', $assinatura->id)->first();
            $assinatura['active'] = $userAssinatura->active;
        }
        $response = [
            'user' => $user,
            'lojas' => $user->lojas()->get(),
            'enderecos' => $user->enderecos()->get(),
            'assinatura' => $assinatura,
        ];

        return response($response, 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users,email',
            'password' => 'required|string|confirmed',
            'cpf' => 'required|unique:users,cpf',
            'gender' => ['required', Rule::in(['m','f', 'o', 'n'])],
            'admin' => 'boolean',
            'address_name' => 'required|string|max:255',
            'cep' => 'required|string|size:8',
            'address' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'number' => 'required|string',
            'complement' => 'string|max:255|nullable',
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
            'default' => true,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'gender' => $request->gender,
            'password' => Hash::make($request->password),
        ]);

        EnderecoUser::create([
            'endereco_id' => $endereco->id,
            'user_id' => $user->id,
        ]);

        $token = $user->createToken(env('APP_API'))->plainTextToken;

        $response = [
            'user' => $user,
            'lojas' => $user->lojas()->get(),
            'enderecos' => $user->enderecos()->get(),
            'token' => $token,
        ];

        return response($response, 201);
    }

    public function insertEndereco(Request $request)
    {
        $request->validate([
            'address_name' => 'required|string|max:255',
            'cep' => 'required|string|size:8',
            'address' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'number' => 'required|string',
            'complement' => 'string|max:255|nullable',
            'cidade_id' => 'required|integer',
            'estado_id' => 'required|integer',
            'default' => 'required|boolean',
        ]);

        $user = auth()->user();
        $user = User::find($user->id);

        $endereco =  Endereco::create([
            'name' => $request->address_name,
            'cep' => $request->cep,
            'address' => $request->address,
            'district' => $request->district,
            'number' => $request->number,
            'complement' => $request->complement,
            'cidade_id' => $request->cidade_id,
            'estado_id' => $request->estado_id,
            'default' => $request->default,
        ]);

        EnderecoUser::create([
            'endereco_id' => $endereco->id,
            'user_id' => $user->id,
        ]);

        $enderecos = $user->enderecos()->get();

        $response = [
            'message' => 'Endereço cadastrado com sucesso',
            'user' => $user,
            'enderecos' => $enderecos
        ];

        return response($response, 201);
    }

    public function updateEndereco($endereco_id, Request $request)
    {
        $request->validate([
            'address_name' => 'required|string|max:255',
            'cep' => 'required|string|size:8',
            'address' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'number' => 'required|string',
            'complement' => 'string|max:255|nullable',
            'cidade_id' => 'required|integer',
            'estado_id' => 'required|integer',
            'default' => 'required|boolean',
        ]);

        $user = auth()->user();
        $user = User::find($user->id);

        $enderecoUser = EnderecoUser::where('endereco_id', $endereco_id)->where('user_id', $user->id)->first();
        if(empty($enderecoUser)){
            return response([
                'message' => 'Endereço não encontrado'
            ], 404);
        }

        $endereco = Endereco::find($enderecoUser->endereco_id);
        $endereco->name = $request->address_name;
        $endereco->cep = $request->cep;
        $endereco->address = $request->address;
        $endereco->district = $request->district;
        $endereco->number = $request->number;
        $endereco->complement = $request->complement;
        $endereco->cidade_id = $request->cidade_id;
        $endereco->estado_id = $request->estado_id;
        $endereco->default = $request->default;
        $endereco->save();
        
        $response = [
            'message' => 'Endereço atualizado com sucesso',
            'user' => $user,
            'lojas' => $user->lojas()->get(),
            'enderecos' => $user->enderecos()->get()
        ];

        return response($response, 200);
    }

    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|unique:users,cpf',
            'gender' => ['required', Rule::in(['m','f', 'o', 'n'])],
            'password' => 'required|string|confirmed',
            'admin' => 'required|boolean',
            'address_name' => 'required|string|max:255',
            'cep' => 'required|string|size:8',
            'address' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'number' => 'required|string',
            'complement' => 'string|max:255|nullable',
            'cidade_id' => 'required|integer',
            'estado_id' => 'required|integer',
            'corporate_name' => 'required|string|max:255',
            'trading_name' => 'required|string|max:20',
            'cnpj' => 'required|string|size:14|unique:lojas,cnpj',
            'web_site' => 'string|max:255|nullable',
            'phone' => 'required|string|max:11|nullable',
            'cel_phone' => 'size:11',
            'email_loja' => 'required|email|max:255',
            'representante_legal' => 'required|string|max:255',
            'representante_legal_email' => 'required|email|max:255|unique:lojas,representante_legal_email',
            'photo' => 'required|image'
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

        if($request->hasfile('photo')){
            $photo = $request->file('photo');
            $aws = $photo->store('produto', 's3');
            $path = Storage::url($aws);
        }

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
            'photo' => $path
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'gender' => $request->gender,
            'password' => Hash::make($request->password),
            'admin' => $request->admin,
        ]);

        LojaUser::create([
            'user_id' => $user->id,
            'loja_id' => $loja->id
        ]);

        EnderecoUser::create([
            'endereco_id' => $endereco->id,
            'user_id' => $user->id,
        ]);

        $token = $user->createToken(env('APP_API'), ['admin'])->plainTextToken;

        $response = [
            'user' => $user,
            'lojas' => $user->lojas()->get(),
            'enderecos' => $user->enderecos()->get(),
            'assinatura' => $user->assinaturas()->get(),
            'token' => $token,
        ];

        return response($response, 201);
    }
    
    public function deleteEndereco($endereco_id)
    {
        $user = auth()->user();
        $enderecoUser = EnderecoUser::where('endereco_id', $endereco_id)->where('user_id', $user->id)->first();
        if(empty($enderecoUser)){
            return response([
                'message' => 'Endereço não encontrado'
            ], 404);
        }

        $endereco = Endereco::find($enderecoUser->endereco_id);
        $endereco->delete();
        $response = [
            'message' => 'Endereço deletado com sucesso',
        ];

        return response($response, 200);
    }

    public function updateUser(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => ['required', Rule::in(['m','f', 'o', 'n'])],
            'admin' => 'boolean|required',
        ]);

        $user->name = $request->name;
        $user->gender = $request->gender;
        $user->admin = $request->admin;

        $token = $user->createToken(env('APP_API'))->plainTextToken;

        $response = [
            'user' => $user,
            'lojas' => $user->lojas()->get(),
            'enderecos' => $user->enderecos()->get(),
            'token' => $token
        ];

        return response($response, 201);
    }

    public function createLoja(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'address_name' => 'required|string|max:255',
            'cep' => 'required|string|size:8',
            'address' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'number' => 'required|string',
            'complement' => 'string|max:255|nullable',
            'cidade_id' => 'required|integer',
            'estado_id' => 'required|integer',
            'corporate_name' => 'required|string|max:255',
            'trading_name' => 'required|string|max:20',
            'cnpj' => 'required|string|size:14|unique:lojas,cnpj',
            'web_site' => 'string|max:255|nullable',
            'phone' => 'required|string|max:11|nullable',
            'cel_phone' => 'size:11',
            'email_loja' => 'required|email|max:255|unique:lojas,email',
            'representante_legal' => 'required|string|max:255',
            'representante_legal_email' => 'required|email|max:255|unique:lojas,representante_legal_email',
            'photo' => 'required|image'
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

        if($request->hasfile('photo')){
            $photo = $request->file('photo');
            $path = $photo->store('lojas', 's3');
            $aws = Storage::url($path);
        }

        $loja = Loja::create([
            'corporate_name' => $request->corporate_name,
            'trading_name' => $request->trading_name,
            'cnpj' => $request->cnpj,
            'web_site' => $request->web_site,
            'phone' => $request->phone,
            'cel_phone' => $request->cel_phone,
            'email' => $request->email_loja,
            'representante_legal' => $request->representante_legal,
            'representante_legal_email' => $request->representante_legal_email,
            'endereco_id' => $endereco->id,
            'photo' => $aws
        ]);

        LojaUser::create([
            'user_id' => $user->id,
            'loja_id' => $loja->id
        ]);

        $response = [
            'message' => 'Loja criada com sucesso!',
            'user' => $user,
            'lojas' => $user->lojas()->get(),
        ];

        return response($response, 201);
    }

    public function updateLoja($loja_id ,Request $request)
    {
        $user = auth()->user();
        $loja = LojaUser::where('loja_id', $loja_id)->where('user_id', $user->id)->first();
        $loja = Loja::find($loja_id);

        if(empty($loja)){
            //retornando mensagem
            return response([
                'message' => 'Loja não encontrado'
            ], 404);
        }

        $request->validate([
            'address_name' => 'required|string|max:255',
            'cep' => 'required|string|size:8',
            'address' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'number' => 'required|string',
            'complement' => 'string|max:255|nullable',
            'cidade_id' => 'required|integer',
            'estado_id' => 'required|integer',
            'corporate_name' => 'required|string|max:255',
            'trading_name' => 'required|string|max:20',
            'web_site' => 'string|max:255|nullable',
            'phone' => 'required|string|max:11',
            'cel_phone' => 'size:11',
            'representante_legal' => 'required|string|max:255',
            'representante_legal_email' => 'required|email|max:255|unique:lojas,representante_legal_email',
            'photo' => 'required|image|nullable'
        ]);

        $endereco = Endereco::find($loja->endereco_id);
        $endereco->name = $request->address_name;
        $endereco->cep = $request->cep;
        $endereco->address = $request->address;
        $endereco->district = $request->district;
        $endereco->number = $request->number;
        $endereco->complement = $request->complement;
        $endereco->cidade_id = $request->cidade_id;
        $endereco->estado_id = $request->estado_id;
        $endereco->save();

        $loja->corporate_name = $request->corporate_name;
        $loja->trading_name = $request->trading_name;
        $loja->web_site = $request->web_site;
        $loja->phone = $request->phone;
        $loja->cel_phone = $request->cel_phone;
        $loja->representante_legal = $request->representante_legal;
        $loja->representante_legal_email = $request->representante_legal_email;
        $loja->endereco_id = $endereco->id;
        if($request->hasfile('photo')){
            $photo = $request->file('photo');
            $aws = $photo->store('produto', 's3');
            $path = Storage::url($aws);
            $loja->photo = $path;
        }
        $loja->save();

        $response = [
            'message' => 'Loja atualizada com sucesso!',
            'user' => $user,
            'lojas' => $user->lojas()->get(),
        ];

        return response($response, 201);

        return response($response, 201);
    }

    public function deleteLoja($idLoja)
    {
        $user = auth()->user();
        $loja = Loja::find($idLoja);
        $lojaUser = LojaUser::where('loja_id', $loja->id)->where('user_id', $user->id)->first();
        if(empty($lojaUser)){
            //retornando mensagem
            return response([
                'message' => 'Loja não encontrado'
            ], 404);
        }

        $lojaUser->delete();
        $loja->delete();

        $response = [
            'message' => 'Loja deletada com sucesso!'
        ];

        return response($response, 200);

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

        $assinatura = $user->assinaturas()->first();
        if(!empty($assinatura)){
            $userAssinatura = AssinaturaUser::where('user_id', $user->id)->where('assinatura_id', $assinatura->id)->first();
            $assinatura['active'] = $userAssinatura->active;
        }

        $response = [
            'user' => $user,
            'lojas' => $user->lojas()->get(),
            'enderecos' => $user->enderecos()->get(),
            'assinatura' => $assinatura,
            'token' => $token,
        ];

        return response($response, 200);
    }
    
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response([
            'message' => 'Sessão finalizada'
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
                'message' => 'Senha anterior incorreta'
            ], 401);
        }

        //Alterando senha
        $user->password = Hash::make($request->password);
        $user->save();
        auth()->user()->tokens()->delete();
        return response([
            'message' => 'Senha alterada com sucesso'
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
