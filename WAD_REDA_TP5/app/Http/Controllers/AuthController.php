<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'error' => $validator->errors()
            ], 422);
        }

        // 2. Buat user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        // Token valid 1 jam → diatur di sanctum.php
        $token = $user->createToken('auth_token')->plainTextToken;

        // 3. Response
        return response()->json([
            'message' => 'Registration successful',
            'user'    => $user,
            'token'   => $token
        ], 201);
    }


    public function login(Request $request)
    {
        // 4. Validasi
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Email or password incorrect'], 401);
        }

        $user = Auth::user();

        // 5. Generate token (expired = config sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 6. Response
        return response()->json([
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        // 7. Hapus token aktif
        $request->user()->currentAccessToken()->delete();

        // 8. Response
        return response()->json([
            'message' => 'Logout successful'
        ]);
    }
}
