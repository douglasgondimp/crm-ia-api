<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->when(isset($filters['role']), function ($query) use ($filters) {
                return $query->where('role', $filters['role']);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                return $query->where('status', $filters['status']);
            })
            ->when(isset($filters['search']), function ($query) use ($filters) {
                return $query->where(function ($query) use ($filters) {
                    $query->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(15);
    }

    public function findByUuid(string $uuid): User
    {
        return User::where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            return User::create($data);
        });
    }

    public function update(string $uuid, array $data): User
    {
        return DB::transaction(function () use ($uuid, $data) {
            $user = $this->findByUuid($uuid);

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            return $user;
        });
    }

    public function delete(string $uuid): void
    {
        $user = $this->findByUuid($uuid);
        $user->delete();
    }
}
