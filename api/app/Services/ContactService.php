<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ContactService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Contact::query()
            ->with(['company', 'createdBy'])
            ->when(isset($filters['company_id']), function ($query) use ($filters) {
                return $query->where('company_id', $filters['company_id']);
            })
            ->when(isset($filters['decision_maker']), function ($query) use ($filters) {
                return $query->where('decision_maker', $filters['decision_maker']);
            })
            ->when(isset($filters['search']), function ($query) use ($filters) {
                return $query->where(function ($query) use ($filters) {
                    $query->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%")
                        ->orWhere('phone', 'like', "%{$filters['search']}%")
                        ->orWhere('job_title', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(15);
    }

    public function findByUuid(string $uuid): Contact
    {
        return Contact::with(['company', 'createdBy'])->where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data): Contact
    {
        return DB::transaction(function () use ($data) {
            $contact = Contact::create($data);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $contact->tags()->sync($data['tags']);
            }

            return $contact->load(['company', 'createdBy', 'tags']);
        });
    }

    public function update(string $uuid, array $data): Contact
    {
        return DB::transaction(function () use ($uuid, $data) {
            $contact = $this->findByUuid($uuid);
            $contact->update($data);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $contact->tags()->sync($data['tags']);
            }

            return $contact->load(['company', 'createdBy', 'tags']);
        });
    }

    public function delete(string $uuid): void
    {
        $contact = $this->findByUuid($uuid);
        $contact->delete();
    }
}
