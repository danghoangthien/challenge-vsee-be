<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="Events",
 *     description="WebSocket events documentation for real-time updates"
 * )
 */
class EventsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="List all available WebSocket events",
     *     description="Documentation for all WebSocket events that clients can subscribe to",
     *     operationId="listEvents",
     *     tags={"Events"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all available events",
     *         @OA\JsonContent(
     *             @OA\Property(property="events", type="object",
     *                 @OA\Property(
     *                     property="visitor.{id}",
     *                     type="object",
     *                     description="Private channel for visitor-specific events. Replace {id} with visitor_id",
     *                     @OA\Property(
     *                         property="VisitorPickedUpEvent",
     *                         type="object",
     *                         description="Triggered when a provider picks up a visitor",
     *                         @OA\Property(property="examination_id", type="integer", example=1),
     *                         @OA\Property(property="provider_id", type="integer", example=1),
     *                         @OA\Property(property="provider_name", type="string", example="Dr. Smith"),
     *                         @OA\Property(property="started_at", type="string", format="date-time", example="2024-03-29T10:00:00Z"),
     *                         @OA\Property(property="waited_time", type="string", example="5 minutes")
     *                     ),
     *                     @OA\Property(
     *                         property="VisitorExaminationCompletedEvent",
     *                         type="object",
     *                         description="Triggered when provider completes examination",
     *                         @OA\Property(property="examination_id", type="integer", example=1),
     *                         @OA\Property(property="provider_id", type="integer", example=1),
     *                         @OA\Property(property="provider_name", type="string", example="Dr. Smith"),
     *                         @OA\Property(property="completed_at", type="string", format="date-time"),
     *                         @OA\Property(property="duration", type="string", example="15 minutes")
     *                     ),
     *                     @OA\Property(
     *                         property="VisitorExitedEvent",
     *                         type="object",
     *                         description="Triggered when visitor exits the queue",
     *                         @OA\Property(property="position", type="integer", example=3),
     *                         @OA\Property(property="waited_time", type="string", example="10 minutes")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="provider.{id}",
     *                     type="object",
     *                     description="Private channel for provider-specific events. Replace {id} with provider_id",
     *                     @OA\Property(
     *                         property="ProviderPickedUpVisitorEvent",
     *                         type="object",
     *                         description="Triggered when provider successfully picks up a visitor",
     *                         @OA\Property(property="examination_id", type="integer", example=1),
     *                         @OA\Property(property="visitor_id", type="integer", example=1),
     *                         @OA\Property(property="visitor_name", type="string", example="John Doe"),
     *                         @OA\Property(property="started_at", type="string", format="date-time"),
     *                         @OA\Property(property="waited_time", type="string", example="5 minutes")
     *                     ),
     *                     @OA\Property(
     *                         property="ProviderExaminationCompletedEvent",
     *                         type="object",
     *                         description="Triggered when provider completes an examination",
     *                         @OA\Property(property="examination_id", type="integer", example=1),
     *                         @OA\Property(property="visitor_id", type="integer", example=1),
     *                         @OA\Property(property="visitor_name", type="string", example="John Doe"),
     *                         @OA\Property(property="completed_at", type="string", format="date-time"),
     *                         @OA\Property(property="duration", type="string", example="15 minutes")
     *                     ),
     *                     @OA\Property(
     *                         property="ProviderPostponeVisitorEvent",
     *                         type="object",
     *                         description="Triggered when provider is busy and visitor needs to wait",
     *                         @OA\Property(property="visitor_id", type="integer", example=1),
     *                         @OA\Property(property="visitor_name", type="string", example="John Doe"),
     *                         @OA\Property(property="message", type="string", example="Provider is currently busy with another visitor")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="lounge.queue",
     *                     type="object",
     *                     description="Public channel for queue-related events",
     *                     @OA\Property(
     *                         property="VisitorJoinedQueue",
     *                         type="object",
     *                         description="Triggered when a new visitor joins the queue",
     *                         @OA\Property(property="visitor_id", type="integer", example=1),
     *                         @OA\Property(property="visitor_name", type="string", example="John Doe"),
     *                         @OA\Property(property="position", type="integer", example=4),
     *                         @OA\Property(property="joined_at", type="string", format="date-time")
     *                     ),
     *                     @OA\Property(
     *                         property="VisitorExitedQueue",
     *                         type="object",
     *                         description="Triggered when a visitor exits the queue",
     *                         @OA\Property(property="visitor_id", type="integer", example=1),
     *                         @OA\Property(property="visitor_name", type="string", example="John Doe"),
     *                         @OA\Property(property="position", type="integer", example=3),
     *                         @OA\Property(property="waited_time", type="string", example="10 minutes")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        // This is just a documentation endpoint, no actual implementation needed
        return response()->json([
            'message' => 'This is a documentation endpoint. Please refer to the API documentation for WebSocket events information.'
        ]);
    }
} 