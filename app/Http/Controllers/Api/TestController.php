<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Test",
 *     description="API test endpoints"
 * )
 */
class TestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/test",
     *     operationId="getTestData",
     *     tags={"Test"},
     *     summary="Get test data",
     *     description="Returns test data",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="API is working!")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'API is working!']);
    }
}