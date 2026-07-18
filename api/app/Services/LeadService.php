<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Lead::query()
            ->with(['assignedTo'])
            ->when(isset($filters['status']), function ($query) use ($filters) {
                return $query->where('status', $filters['status']);
            })
            ->when(isset($filters['source']), function ($query) use ($filters) {
                return $query->where('source', $filters['source']);
            })
            ->when(isset($filters['temperature']), function ($query) use ($filters) {
                return $query->where('temperature', $filters['temperature']);
            })
            ->when(isset($filters['assigned_to']), function ($query) use ($filters) {
                return $query->where('assigned_to', $filters['assigned_to']);
            })
            ->when(isset($filters['search']), function ($query) use ($filters) {
                return $query->where(function ($query) use ($filters) {
                    $query->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%")
                        ->orWhere('company', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(15);
    }

    public function findByUuid(string $uuid): Lead
    {
        return Lead::with(['assignedTo'])->where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            $lead = Lead::create($data);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $lead->tags()->sync($data['tags']);
            }

            return $lead->load(['assignedTo', 'tags']);
        });
    }

    public function update(string $uuid, array $data): Lead
    {
        return DB::transaction(function () use ($uuid, $data) {
            $lead = $this->findByUuid($uuid);
            $lead->update($data);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $lead->tags()->sync($data['tags']);
            }

            return $lead->load(['assignedTo', 'tags']);
        });
    }

    public function delete(string $uuid): void
    {
        $lead = $this->findByUuid($uuid);
        $lead->delete();
    }
}
