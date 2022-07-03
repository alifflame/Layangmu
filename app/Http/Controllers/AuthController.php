<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string',
            'email' => 'required|email|unique:tb_users',
            'password' => 'required',
            'phone' => 'required|string',
            'position' => 'required|string',
        ]);

        try {

            $user = new User;
            
            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);
            $user->phone = $request->input('phone');
            $user->position = $request->input('position');

            $user->save();

            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'REGISTER_FAILED'], 409);
        }
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'LOGIN_FAILED'], 401);
        }

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => Auth::user()
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return response()->json(['message' => 'LOGOUT_SUCCESS']);
    }
}
