<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

/**
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="Bearer",
 *   in="header",
 *   name="Authorization",
 *   description="Pega tu token así: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *   name="Auth",
 *   description="Operaciones de autenticación (registro, login, logout)"
 * )
 */
class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario.
     *
     * @OA\Post(
     *   path="/register",
     *   tags={"Auth"},
     *   summary="Registrar nuevo usuario",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Usuario registrado",
     *     @OA\JsonContent(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/User"),
     *         @OA\Schema(
     *           @OA\Property(property="meta", type="object",
     *             @OA\Property(property="token",      type="string", example="abcd1234..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=10080)
     *           )
     *         )
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Errores de validación",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The given data was invalid."),
     *       @OA\Property(
     *         property="errors",
     *         type="object",
     *         example={"email": {"El correo ya está registrado."}}
     *       )
     *     )
     *   )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = \App\Models\User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        return (new UserResource($user))
            ->additional([
                'meta' => [
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => 60 * 24 * 7, // minutos
                ],
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Autenticar usuario y obtener token.
     *
     * @OA\Post(
     *   path="/login",
     *   tags={"Auth"},
     *   summary="Autenticar usuario y obtener token",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Inicio de sesión exitoso",
     *     @OA\JsonContent(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/User"),
     *         @OA\Schema(
     *           @OA\Property(property="meta", type="object",
     *             @OA\Property(property="token",      type="string", example="abcd1234..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=10080)
     *           )
     *         )
     *       }
     *     )
     *   ),
     *   @OA\Response(response=401, description="Credenciales inválidas")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas.'], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return (new UserResource($user))
            ->additional([
                'meta' => [
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => 60 * 24 * 7,
                ],
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Cerrar sesión (revocar el token actual).
     *
     * @OA\Post(
     *   path="/logout",
     *   tags={"Auth"},
     *   summary="Cerrar sesión y revocar token",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Sesión cerrada correctamente")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }
}
