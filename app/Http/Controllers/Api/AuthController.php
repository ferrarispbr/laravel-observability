<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'address' => ['required', 'string', 'max:255'],
            'address_number' => ['required', 'string', 'max:20'],
            'address_complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'address' => $data['address'],
            'address_number' => $data['address_number'],
            'address_complement' => $data['address_complement'],
            'neighborhood' => $data['neighborhood'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zip_code' => $data['zip_code'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json(['message' => 'Usuário cadastrado com sucesso.','user' => $user,], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password'],]))
        {
            return response()->json(['message' => 'E-mail ou senha inválidos.', ], 401);
        }

        $request->session()->regenerate();
        return response()->json(['message' => 'Login realizado com sucesso.','user' => $request->user(), ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logout realizado com sucesso.',]);
    }
}
