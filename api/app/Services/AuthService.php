<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => 'seller',
            'status' => true,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new AuthenticationException('Credenciais inválidas.');
        }

        if (! $user->status) {
            throw new AuthenticationException('Usuário inativo.');
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function me(User $user): User
    {
        return $user;
    }

    public function updateProfile(User $user, array $data): User
    {
        $fillable = ['name', 'email', 'phone', 'avatar'];

        $filtered = collect($data)->only($fillable)->filter(fn($value) => ! is_null($value))->toArray();

        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $filtered['avatar'] = $data['avatar']->store('avatars', 'public');
        }

        $user->update($filtered);

        return $user->fresh();
    }

    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new AuthenticationException('Senha atual incorreta.');
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
