<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderRequest;
use App\Http\Requests\VisitorRequest;
use App\Services\LoungeQueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Exceptions\ProviderBusyException;
use App\Exceptions\NotFoundException;

class LoungeController extends Controller
{
    public function __construct(
        private readonly LoungeQueueService $loungeQueueService
    ) {
    }

    /**
     * Enqueue a visitor to the lounge queue.
     */
    public function enqueue(VisitorRequest $request): JsonResponse
    {
        try {
            $visitor = $request->getVisitor();
            $position = $this->loungeQueueService->enqueueVisitor($visitor);

            return response()->json([
                'success' => true,
                'data' => [
                    'position' => $position,
                    'message' => 'Successfully added to waiting room queue'
                ]
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while adding to queue'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the list of visitors in the queue.
     */
    public function getWaitingList(ProviderRequest $request): JsonResponse
    {
        try {
            $provider = $request->getProvider();
            $result = $this->loungeQueueService->getWaitingList();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while retrieving the waiting list'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Pick up a visitor from the queue.
     */
    public function pickupVisitor(ProviderRequest $request): JsonResponse
    {
        try {
            $provider = $request->getProvider();
            $visitorId = $request->input('visitor_id');
            $result = $this->loungeQueueService->pickupVisitor($provider, $visitorId);

            return response()->json($result);
        } catch (ProviderBusyException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (NotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while picking up the visitor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Complete a visitor examination.
     */
    public function dropoffVisitor(ProviderRequest $request): JsonResponse
    {
        try {
            $provider = $request->getProvider();
            $visitorId = $request->input('visitor_id');
            $result = $this->loungeQueueService->dropoffVisitor($provider, $visitorId);

            return response()->json($result);
        } catch (NotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while completing the examination'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove a visitor from the queue.
     */
    public function exit(VisitorRequest $request): JsonResponse
    {
        try {
            $visitor = $request->getVisitor();
            $result = $this->loungeQueueService->exitQueue($visitor);

            return response()->json($result);
        } catch (NotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while removing from queue'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
