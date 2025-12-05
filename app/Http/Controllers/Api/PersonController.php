<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Interaction;

class PersonController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/people",
     *     summary="List people for swiping",
     *     description="Get a paginated list of people who haven't been interacted with yet. Excludes already liked or disliked people.",
     *     tags={"People"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of people per page (default: 10)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of people retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="أحمد الخالد"),
     *                     @OA\Property(property="age", type="integer", example=28),
     *                     @OA\Property(property="pictures", type="array", @OA\Items(type="string")),
     *                     @OA\Property(
     *                         property="location",
     *                         type="object",
     *                         @OA\Property(property="city", type="string", example="Kuwait City"),
     *                         @OA\Property(property="country", type="string", example="Kuwait"),
     *                         @OA\Property(property="latitude", type="number", format="float", example=29.3759),
     *                         @OA\Property(property="longitude", type="number", format="float", example=47.9774)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="last_page", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing JWT token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $device = $request->input('authenticated_device');
        $perPage = $request->input('per_page', 10);

        // Get IDs of people already interacted with
        $interactedIds = Interaction::where('device_id', $device->id)
            ->pluck('person_id')
            ->toArray();

        // Get people not yet interacted with
        $people = Person::whereNotIn('id', $interactedIds)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $people->map(function ($person) {
                return [
                    'id' => $person->id,
                    'name' => $person->name,
                    'age' => $person->age,
                    'pictures' => $person->pictures,
                    'location' => [
                        'city' => $person->city,
                        'country' => $person->country,
                        'latitude' => $person->latitude,
                        'longitude' => $person->longitude,
                    ],
                ];
            }),
            'pagination' => [
                'current_page' => $people->currentPage(),
                'per_page' => $people->perPage(),
                'total' => $people->total(),
                'last_page' => $people->lastPage(),
            ],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/people/{id}",
     *     summary="Get a person's profile",
     *     description="Retrieve detailed information about a specific person by their ID",
     *     tags={"People"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Person ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Person retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="أحمد الخالد"),
     *                 @OA\Property(property="age", type="integer", example=28),
     *                 @OA\Property(property="pictures", type="array", @OA\Items(type="string")),
     *                 @OA\Property(
     *                     property="location",
     *                     type="object",
     *                     @OA\Property(property="city", type="string", example="Kuwait City"),
     *                     @OA\Property(property="country", type="string", example="Kuwait"),
     *                     @OA\Property(property="latitude", type="number", format="float", example=29.3759),
     *                     @OA\Property(property="longitude", type="number", format="float", example=47.9774)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Person not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Person not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing JWT token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $person = Person::find($id);

        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => 'Person not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $person->id,
                'name' => $person->name,
                'age' => $person->age,
                'pictures' => $person->pictures,
                'location' => [
                    'city' => $person->city,
                    'country' => $person->country,
                    'latitude' => $person->latitude,
                    'longitude' => $person->longitude,
                ],
            ],
        ], 200);
    }
}
