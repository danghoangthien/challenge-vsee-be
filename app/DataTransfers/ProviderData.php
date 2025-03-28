<?php

namespace App\DataTransfers;

use App\Models\Provider;

class ProviderData
{
    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly int $department_id,
        public readonly int $role_id,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
    ) {}

    public static function fromModel(Provider $provider): self
    {
        return new self(
            id: $provider->id,
            user_id: $provider->user_id,
            department_id: $provider->department_id,
            role_id: $provider->role_id,
            name: $provider->user ? $provider->user->firstname . ' ' . $provider->user->lastname : null,
            email: $provider->user?->email,
            created_at: $provider->created_at?->toISOString(),
            updated_at: $provider->updated_at?->toISOString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'department_id' => $this->department_id,
            'role_id' => $this->role_id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 