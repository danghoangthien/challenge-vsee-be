<?php

namespace App\Services;

use App\Models\VisitorExamination;
use App\Models\Visitor;
use App\Models\Provider;
use App\Exceptions\NotFoundException;
use Illuminate\Support\Facades\Log;
use App\Events\VisitorExaminationCompletedEvent;

class ExaminationService
{
    /**
     * Get the current visitor's examination.
     *
     * @param Visitor $visitor
     * @return array
     * @throws NotFoundException
     */
    public function getVisitorExamination(Visitor $visitor): array
    {
        $examination = VisitorExamination::where('visitor_id', $visitor->id)
            ->where('status', 'in_progress')
            ->with('provider')
            ->first();

        if (!$examination) {
            throw new NotFoundException('No active examination found');
        }

        return [
            'examination_id' => $examination->id,
            'provider_id' => $examination->provider_id,
            'provider_name' => $examination->provider->user->name ?? 'Unknown',
            'started_at' => $examination->started_at->toISOString(),
            'status' => $examination->status,
            'duration' => $examination->started_at->diffForHumans(),
            'reason' => $examination->reason
        ];
    }

    /**
     * Get the current provider's examination.
     *
     * @param Provider $provider
     * @return array
     * @throws NotFoundException
     */
    public function getProviderExamination(Provider $provider): array
    {
        $examination = VisitorExamination::where('provider_id', $provider->id)
            ->where('status', 'in_progress')
            ->with('visitor')
            ->first();

        if (!$examination) {
            throw new NotFoundException('No active examination found');
        }
        $visitor = $examination->visitor;
        return [
            'examination_id' => $examination->id,
            'visitor_id' => $examination->visitor_id,
            'visitor_name' => $visitor->user->firstname . ' ' . $visitor->user->lastname ?? 'Unknown',
            'started_at' => $examination->started_at->toISOString(),
            'status' => $examination->status,
            'duration' => $examination->started_at->diffForHumans(),
            'reason' => $examination->reason
        ];
    }

    /**
     * Complete a visitor examination.
     */
    public function completeExamination(Provider $provider, string $visitorId): array
    {
        // Get examination record
        $examination = VisitorExamination::where('visitor_id', $visitorId)
            ->where('provider_id', $provider->id)
            ->where('status', 'in_progress')
            ->first();
        
        if (!$examination) {
            throw new NotFoundException('Visitor examination not found or not in progress');
        }

        // Update examination record
        $examination->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // Fire completion event
        Log::info('Firing VisitorExaminationCompletedEvent', [
            'examination_id' => $examination->id,
            'provider_id' => $provider->id,
            'visitor_id' => $visitorId
        ]);

        event(new VisitorExaminationCompletedEvent($examination));

        return [
            'success' => true,
            'data' => [
                'visitor_id' => $examination->visitor_id,
                'visitor_name' => $examination->visitor->user->name ?? 'Unknown',
                'examination_duration' => $examination->started_at->diffForHumans($examination->completed_at),
                'message' => 'Successfully completed visitor examination'
            ]
        ];
    }
}
