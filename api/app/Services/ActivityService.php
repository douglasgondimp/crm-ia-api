<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ActivityService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Activity::query()
            ->with(['deal', 'company', 'contact', 'user'])
            ->when(isset($filters['type']), function ($query) use ($filters) {
                return $query->where('type', $filters['type']);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                return $query->where('status', $filters['status']);
            })
            ->when(isset($filters['priority']), function ($query) use ($filters) {
                return $query->where('priority', $filters['priority']);
            })
            ->when(isset($filters['deal_id']), function ($query) use ($filters) {
                return $query->where('deal_id', $filters['deal_id']);
            })
            ->when(isset($filters['company_id']), function ($query) use ($filters) {
                return $query->where('company_id', $filters['company_id']);
            })
            ->when(isset($filters['contact_id']), function ($query) use ($filters) {
                return $query->where('contact_id', $filters['contact_id']);
            })
            ->when(isset($filters['user_id']), function ($query) use ($filters) {
                return $query->where('user_id', $filters['user_id']);
            })
            ->when(isset($filters['search']), function ($query) use ($filters) {
                return $query->where(function ($query) use ($filters) {
                    $query->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('description', 'like', "%{$filters['search']}%")
                        ->orWhere('type', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy('starts_at', 'desc');

        return $query->paginate(15);
    }

    public function findById(int $id): Activity
    {
        return Activity::with(['deal', 'company', 'contact', 'user'])->findOrFail($id);
    }

    public function create(array $data): Activity
    {
        return DB::transaction(function () use ($data) {
            return Activity::create($data);
        });
    }

    public function update(int $id, array $data): Activity
    {
        return DB::transaction(function () use ($id, $data) {
            $activity = $this->findById($id);
            $activity->update($data);

            return $activity->load(['deal', 'company', 'contact', 'user']);
        });
    }

    public function delete(int $id): void
    {
        $activity = $this->findById($id);
        $activity->delete();
    }
}
