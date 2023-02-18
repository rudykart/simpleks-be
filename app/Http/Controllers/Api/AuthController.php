<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'unique:users,email,except,id', 'max:50'],
            'password' => 'required|min:8',
            'konfirmasi_password' => ['required', 'same:password'],
        ]);

        $request['konfirmasi_password'] = null;
        $request['password'] = Hash::make($request->password);

        $register = User::create($request->all());

        return response()->json(
            ['status' => true, 'data' => $register],
            Response::HTTP_CREATED
        );
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('access_token')->plainTextToken;
        return response()->json(
            [
                'token' => $token,
            ],
            Response::HTTP_OK
        );
    }

    public function logout()
    {
        auth()
            ->user()
            ->tokens()
            ->delete();

        return response()->json(
            ['status' => true, 'data' => 'logout'],
            Response::HTTP_OK
        );
    }
}
