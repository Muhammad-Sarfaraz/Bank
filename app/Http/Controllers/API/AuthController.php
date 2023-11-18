<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'account_type' => ['required', 'string', Rule::in(['individual', 'business'])],
                'name' => 'required|string',
                'email' => 'required|string|unique:users',
                'password' => 'required|string',
            ]);

            $user = User::create([
                'account_type' => $request->account_type,
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            if ($user->save()) {
                $tokenResult = $user->createToken('mediusware');
                $token = $tokenResult->plainTextToken;

                return response()->json([
                    'message' => 'User created!',
                    'accessToken' => $token,
                ], 201);
            } else {
                return response()->json(['error' => 'Sorry, something went wrong!']);
            }
        } catch (Exception $ex) {
            return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
        }

    }

    public function login(Request $request)
    {
        try {
            if (! Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return response([
                    'message' => 'Email or password is incorrect',
                ], 422);
            }

            $user = Auth::user();
            $token = $user->createToken('mediusware')->plainTextToken;

            return response([
                'user' => new UserResource($user),
                'token' => $token,
            ]);

        } catch (Exception $ex) {
            return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
        }
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
