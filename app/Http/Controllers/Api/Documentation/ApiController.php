<?php

namespace App\Http\Controllers\Api\Documentation;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     description="API documentation with Swagger"
 * )
 * @OA\Server(
 *     url="/api",
 *     description="API Server RRHH"
 * )
 */
class ApiController extends Controller
{
    //
}