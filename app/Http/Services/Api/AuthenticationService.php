<?php

namespace App\Http\Services\Api;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthenticationService
{
    public function register(array $data): array
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $user->createToken('passportToken')->accessToken;

            DB::commit();

            return [
                'user'  => $user->only(['id', 'name', 'email']),
                'token' => $token,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw new \RuntimeException('Registration failed. Please try again later.');
        }
    }

    public function login(array $credentials): array
    {
        try {
            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = Auth::user();
            $token = $user->createToken('passportToken')->accessToken;

            return [
                'user'  => $user->only(['id', 'name', 'email']),
                'token' => $token,
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new \RuntimeException('Login failed. Please try again later.');
        }
    }
}
