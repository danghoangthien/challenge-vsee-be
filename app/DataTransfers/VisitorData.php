<?php

namespace App\DataTransfers;

use App\Models\Visitor;

class VisitorData
{
    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
    ) {}

    public static function fromModel(Visitor $visitor): self
    {
        return new self(
            id: $visitor->id,
            user_id: $visitor->user_id,
            name: $visitor->user ? $visitor->user->firstname . ' ' . $visitor->user->lastname : null,
            email: $visitor->user?->email,
            created_at: $visitor->created_at?->toISOString(),
            updated_at: $visitor->updated_at?->toISOString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 