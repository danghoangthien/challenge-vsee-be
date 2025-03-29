<?php

namespace Database\Factories;

use App\Models\VisitorExamination;
use App\Models\Visitor;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorExaminationFactory extends Factory
{
    protected $model = VisitorExamination::class;

    public function definition(): array
    {
        return [
            'visitor_id' => Visitor::factory(),
            'provider_id' => Provider::factory(),
            'queue_entry_id' => $this->faker->uuid,
            'started_at' => now(),
            'ended_at' => null,
            'status' => 'in_progress'
        ];
    }

    /**
     * Indicate that the examination is completed.
     */
    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'ended_at' => now()
            ];
        });
    }

    /**
     * Indicate that the examination is cancelled.
     */
    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'ended_at' => now()
            ];
        });
    }
} 