<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderRequest;
use App\Http\Requests\VisitorRequest;
use App\Services\LoungeQueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Exceptions\ProviderBusyException;
use App\Exceptions\NotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Lounge Queue",
 *     description="API endpoints for managing the lounge queue system"
 * )
 */
class LoungeController extends Controller
{
    public function __construct(
        private readonly LoungeQueueService $loungeQueueService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/visitor/lounge/queue",
     *     summary="Add visitor to queue",
     *     description="Add a visitor to the waiting room queue",
     *     operationId="enqueueVisitor",
     *     tags={"Lounge Queue"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"visitor_id"},
     *             @OA\Property(property="visitor_id", type="integer", description="ID of the visitor to add to queue")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Visitor successfully added to queue",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="position", type="integer", example=1),
     *                 @OA\Property(property="message", type="string", example="Successfully added to waiting room queue")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Visitor already in queue"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/provider/lounge/list",
     *     summary="Get waiting list",
     *     description="Get the current waiting room queue list",
     *     operationId="getWaitingList",
     *     tags={"Lounge Queue"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved waiting list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=2),
     *                 @OA\Property(property="visitors", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="position", type="integer", example=1),
     *                         @OA\Property(property="visitor_id", type="integer", example=1),
     *                         @OA\Property(property="visitor_name", type="string", example="John Doe"),
     *                         @OA\Property(property="reason", type="string", nullable=true),
     *                         @OA\Property(property="waiting_time", type="string", example="5 minutes ago")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/provider/lounge/pickup",
     *     summary="Pick up visitor",
     *     description="Pick up a visitor from the waiting room queue",
     *     operationId="pickupVisitor",
     *     tags={"Lounge Queue"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"visitor_id"},
     *             @OA\Property(property="visitor_id", type="integer", description="ID of the visitor to pick up")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Visitor successfully picked up",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="visitor_id", type="integer", example=1),
     *                 @OA\Property(property="visitor_name", type="string", example="John Doe"),
     *                 @OA\Property(property="waited_time", type="string", example="5 minutes ago"),
     *                 @OA\Property(property="message", type="string", example="Successfully picked up visitor from queue")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Visitor not found in queue"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Provider is busy"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
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
            Log::error('LoungeController: Visitor not found', [
                'visitor_id' => $visitorId,
                'message' => $e->getMessage()
            ]);
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
     * @OA\Post(
     *     path="/api/provider/lounge/dropoff",
     *     summary="Drop off visitor",
     *     description="Complete a visitor examination and remove them from the provider's care",
     *     operationId="dropoffVisitor",
     *     tags={"Lounge Queue"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"visitor_id"},
     *             @OA\Property(property="visitor_id", type="integer", description="ID of the visitor to drop off")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Visitor successfully dropped off",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="visitor_id", type="integer", example=1),
     *                 @OA\Property(property="visitor_name", type="string", example="John Doe"),
     *                 @OA\Property(property="examination_duration", type="string", example="15 minutes"),
     *                 @OA\Property(property="message", type="string", example="Successfully completed visitor examination")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Visitor examination not found or not in progress"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
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
                'message' => 'An unexpected error occurred while dropping off the visitor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/visitor/lounge/queue",
     *     summary="Remove visitor from queue",
     *     description="Remove a visitor from the waiting room queue",
     *     operationId="exitQueue",
     *     tags={"Lounge Queue"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"visitor_id"},
     *             @OA\Property(property="visitor_id", type="integer", description="ID of the visitor to remove from queue")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Visitor successfully removed from queue",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully removed from queue")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Visitor not found in queue"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
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
