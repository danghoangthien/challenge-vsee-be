<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\VisitorExamination;
use App\Services\ExaminationService;
use App\Http\Requests\ProviderRequest;
use App\Http\Requests\VisitorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Exceptions\NotFoundException;

/**
 * @OA\Tag(
 *     name="Examination",
 *     description="API endpoints for managing visitor examinations"
 * )
 */
class ExaminationController extends Controller
{
    public function __construct(
        private readonly ExaminationService $examinationService
    ) {
    }

    /**
     * Get the current visitor's examination.
     *
     * @OA\Get(
     *     path="/api/examination",
     *     summary="Get current visitor's examination",
     *     description="Retrieves the current examination details for the authenticated visitor. Requires visitor authentication.",
     *     operationId="getExaminationByCurrentVisitor",
     *     tags={"Examination"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Examination details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="examination_id", type="integer", example=1, description="Unique identifier of the examination"),
     *                 @OA\Property(property="provider_id", type="integer", example=1, description="ID of the provider conducting the examination"),
     *                 @OA\Property(property="provider_name", type="string", example="Dr. Smith", description="Full name of the provider"),
     *                 @OA\Property(property="started_at", type="string", format="date-time", example="2024-03-29T10:00:00Z", description="When the examination started"),
     *                 @OA\Property(property="status", type="string", example="in_progress", description="Current status of the examination", enum={"in_progress", "completed", "cancelled"}),
     *                 @OA\Property(property="duration", type="string", example="5 minutes", description="How long the examination has been running"),
     *                 @OA\Property(property="reason", type="string", example="Regular checkup", description="Reason for the examination")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active examination found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No active examination found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing authentication token"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User is not a visitor"
     *     )
     * )
     */
    public function getExaminationByCurrentVisitor(VisitorRequest $request): JsonResponse
    {
        try {
            $visitor = $request->getVisitor();
            $examinationData = $this->examinationService->getVisitorExamination($visitor);

            return response()->json([
                'success' => true,
                'data' => $examinationData
            ]);
        } catch (NotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching examination'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the current provider's examination.
     *
     * @OA\Get(
     *     path="/api/provider/examination",
     *     summary="Get current provider's examination",
     *     description="Retrieves the current examination details for the authenticated provider. Requires provider authentication.",
     *     operationId="getExaminationByCurrentProvider",
     *     tags={"Examination"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Examination details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="examination_id", type="integer", example=1, description="Unique identifier of the examination"),
     *                 @OA\Property(property="visitor_id", type="integer", example=1, description="ID of the visitor being examined"),
     *                 @OA\Property(property="visitor_name", type="string", example="John Doe", description="Full name of the visitor"),
     *                 @OA\Property(property="started_at", type="string", format="date-time", example="2024-03-29T10:00:00Z", description="When the examination started"),
     *                 @OA\Property(property="status", type="string", example="in_progress", description="Current status of the examination", enum={"in_progress", "completed", "cancelled"}),
     *                 @OA\Property(property="duration", type="string", example="5 minutes", description="How long the examination has been running"),
     *                 @OA\Property(property="reason", type="string", example="Regular checkup", description="Reason for the examination")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active examination found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No active examination found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing authentication token"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User is not a provider"
     *     )
     * )
     */
    public function getExaminationByCurrentProvider(ProviderRequest $request): JsonResponse
    {
        try {
            $provider = $request->getProvider();
            $examinationData = $this->examinationService->getProviderExamination($provider);

            return response()->json([
                'success' => true,
                'data' => $examinationData
            ]);
        } catch (NotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching examination'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function completeExamination(ProviderRequest $request): JsonResponse
    {
        try {
            $provider = $request->getProvider();
            $visitorId = $request->input('visitor_id');
            $result = $this->examinationService->completeExamination($provider, $visitorId);

            return response()->json($result);
        } catch (NotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while complete the examination'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 