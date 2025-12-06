<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="TinderKW API",
 *     version="1.0.0",
 *     description="Tinder-like API for mobile applications with device-based JWT authentication",
 *     @OA\Contact(
 *         email="admin@tinderkw.com",
 *         name="TinderKW Support"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT token obtained from /api/register-device endpoint"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Device registration and authentication endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="People",
 *     description="Browse and view people profiles"
 * )
 * 
 * @OA\Tag(
 *     name="Interactions",
 *     description="Like, dislike, and view liked people"
 * )
 * 
 * @OA\Tag(
 *     name="Admin",
 *     description="Manage admin information and notification email"
 * )
 */
abstract class Controller
{
    //
}
