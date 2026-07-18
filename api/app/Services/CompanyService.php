<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Company::query()
            ->with(['createdBy'])
            ->when(isset($filters['segment']), function ($query) use ($filters) {
                return $query->where('segment', $filters['segment']);
            })
            ->when(isset($filters['city']), function ($query) use ($filters) {
                return $query->where('city', $filters['city']);
            })
            ->when(isset($filters['state']), function ($query) use ($filters) {
                return $query->where('state', $filters['state']);
            })
            ->when(isset($filters['search']), function ($query) use ($filters) {
                return $query->where(function ($query) use ($filters) {
                    $query->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('trade_name', 'like', "%{$filters['search']}%")
                        ->orWhere('document', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(15);
    }

    public function findByUuid(string $uuid): Company
    {
        return Company::with(['createdBy'])->where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $company = Company::create($data);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $company->tags()->sync($data['tags']);
            }

            return $company->load(['createdBy', 'tags']);
        });
    }

    public function update(string $uuid, array $data): Company
    {
        return DB::transaction(function () use ($uuid, $data) {
            $company = $this->findByUuid($uuid);
            $company->update($data);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $company->tags()->sync($data['tags']);
            }

            return $company->load(['createdBy', 'tags']);
        });
    }

    public function delete(string $uuid): void
    {
        $company = $this->findByUuid($uuid);
        $company->delete();
    }
}
