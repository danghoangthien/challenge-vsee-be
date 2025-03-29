<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use L5Swagger\Http\Controllers\SwaggerController as BaseSwaggerController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Lounge Queue API Documentation",
 *     description="API documentation for the Lounge Queue system",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerController extends BaseSwaggerController
{
} 