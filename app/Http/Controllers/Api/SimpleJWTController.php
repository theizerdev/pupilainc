<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class SimpleJWTController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Normalizar EMAIL y PASSWORD a minúsculas si vienen en mayúsculas
            if ($request->has('EMAIL')) {
                $request->merge(['email' => $request->input('EMAIL')]);
            }
            if ($request->has('PASSWORD')) {
                $request->merge(['password' => $request->input('PASSWORD')]);
            }

            $credentials = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => 'Credenciales inválidas'
                ], 401);
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
