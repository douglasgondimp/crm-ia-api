<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DealService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Deal::query()
            ->with(['company', 'contact', 'pipelineStage', 'assignedTo'])
            ->when(isset($filters['status']), function ($query) use ($filters) {
                return $query->where('status', $filters['status']);
            })
            ->when(isset($filters['company_id']), function ($query) use ($filters) {
                return $query->where('company_id', $filters['company_id']);
            })
            ->when(isset($filters['contact_id']), function ($query) use ($filters) {
                return $query->where('contact_id', $filters['contact_id']);
            })
            ->when(isset($filters['pipeline_stage_id']), function ($query) use ($filters) {
                return $query->where('pipeline_stage_id', $filters['pipeline_stage_id']);
            })
            ->when(isset($filters['assigned_to']), function ($query) use ($filters) {
                return $query->where('assigned_to', $filters['assigned_to']);
            })
            ->when(isset($filters['search']), function ($query) use ($filters) {
                return $query->where(function ($query) use ($filters) {
                    $query->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('description', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(15);
    }

    public function findByUuid(string $uuid): Deal
    {
        return Deal::with(['company', 'contact', 'pipelineStage', 'assignedTo'])->where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data): Deal
    {
        return DB::transaction(function () use ($data) {
            $deal = Deal::create($data);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $deal->tags()->sync($data['tags']);
            }

            return $deal->load(['company', 'contact', 'pipelineStage', 'assignedTo', 'tags']);
        });
    }

    public function update(string $uuid, array $data): Deal
    {
        return DB::transaction(function () use ($uuid, $data) {
            $deal = $this->findByUuid($uuid);
            $deal->update($data);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $deal->tags()->sync($data['tags']);
            }

            return $deal->load(['company', 'contact', 'pipelineStage', 'assignedTo', 'tags']);
        });
    }

    public function delete(string $uuid): void
    {
        $deal = $this->findByUuid($uuid);
        $deal->delete();
    }
}
