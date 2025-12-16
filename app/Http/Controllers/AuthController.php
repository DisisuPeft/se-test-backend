<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 
     * Login con token en cookie HttpOnly
     * 
     * **/
    public function login(Request $request){
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->email)->first();
            // dump($user);
            if (!$user || !Hash::check($request->password, $user->password)){
                return response()->json([
                    'detail' => 'Por favor, verifica el email y password.'
                ], 403);
            }
            
            //aqui genera token 
            $token = $user->createToken('auth_token')->plainTextToken;
            $response = response()->json([
                'message' => 'Inicio de sesion exitoso'
            ], 200);

            //aqui se aplican las cookies
            return $response->cookie('access_token',
                $token,
                config('sanctum.cookie_max_age', 525600),
                config('sanctum.cookie_path', '/'),
                config('sanctum.cookie_domain', null),
                config('sanctum.cookie_secure', true),
                true,
                false,
                config('sanctum.cookie_samesite', 'lax')
            );
        } catch (ValidationException $e){
            return response()->json([
                'detail' => 'Por favor, verifica los datos enviados en la solicitud.',
                'details' => $e->errors()
            ], 400);
        } catch (\Exception $e){
            return response()->json([
                'detail' => 'Ocurrio un error al autenticar el usuario.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request){
        try {
            $request->user()->tokens()->delete();
            $response = response()->json([
                'message' => 'cierre de sesion exitoso'
            ], 200);

            return $response->withCookie(cookie()->forget('access_token'));

        } catch (\Exception $e){
            return response()->json([
                'detail' => 'Error al eliminar el token.'
            ]);
        }
    }

    public function me(Request $request){
        return response()->json([
            'user' => $request->user()
        ]);
    }

    public function refresh(Request $request){
        try {
            $user = $request->user();
            $request->user()->currentAccessToken()->delete();
            $newToken = $user->createToken('auth_token')->plainText;

            $response = response()->json([
                'message' => 'Token renovado exitosamente'
            ]);

            return $response->cookie(
                'access_token',
                $newToken,
                config('sanctum.cookie_max_age', 525600),
                config('sanctum.cookie_path', '/'),
                config('sanctum.cookie_domain', null),
                config('sanctum.cookie_secure', true),
                true,
                false,
                config('sanctum.cookie_same_site', 'lax')
            );
        } catch (\Exception $e){
            return response()->json([
                'detail' => 'Error al renovar el token'
            ]);
        }
    }

}
