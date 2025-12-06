<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Interaction;
use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\PersonPopularNotification;
use Illuminate\Support\Facades\DB;

class InteractionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/interactions/like",
     *     summary="Like a person",
     *     description="Create a like interaction with a person. Sends email notification when person reaches 50 likes.",
     *     tags={"Interactions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"person_id"},
     *             @OA\Property(property="person_id", type="integer", example=1, description="ID of the person to like")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Person liked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Person liked successfully"),
     *             @OA\Property(
     *                 property="person",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="أحمد الخالد"),
     *                 @OA\Property(property="like_count", type="integer", example=51)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Already interacted with this person",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You have already interacted with this person")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function like(Request $request)
    {
        $device = $request->input('authenticated_device');

        $validator = Validator::make($request->all(), [
            'person_id' => 'required|integer|exists:people,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if already interacted
        $existingInteraction = Interaction::where('device_id', $device->id)
            ->where('person_id', $request->person_id)
            ->first();

        if ($existingInteraction) {
            return response()->json([
                'success' => false,
                'message' => 'You have already interacted with this person'
            ], 409);
        }

        // Use transaction to ensure data consistency
        DB::beginTransaction();
        try {
            // Create like interaction
            Interaction::create([
                'device_id' => $device->id,
                'person_id' => $request->person_id,
                'type' => 'like',
            ]);

            // Get person and increment like count
            $person = Person::findOrFail($request->person_id);
            $person->incrementLikeCount();
            $person->refresh();

            // Check if person reached 50 likes milestone
            if ($person->like_count == 50) {
                $admin = Admin::first();
                if ($admin && $admin->email) {
                    Mail::to($admin->email)->send(new PersonPopularNotification($person));
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Person liked successfully',
                'person' => [
                    'id' => $person->id,
                    'name' => $person->name,
                    'like_count' => $person->like_count,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to like person: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/interactions/dislike",
     *     summary="Dislike a person",
     *     description="Create a dislike interaction with a person",
     *     tags={"Interactions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"person_id"},
     *             @OA\Property(property="person_id", type="integer", example=1, description="ID of the person to dislike")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Person disliked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Person disliked successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Already interacted with this person",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You have already interacted with this person")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function dislike(Request $request)
    {
        $device = $request->input('authenticated_device');

        $validator = Validator::make($request->all(), [
            'person_id' => 'required|integer|exists:people,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if already interacted
        $existingInteraction = Interaction::where('device_id', $device->id)
            ->where('person_id', $request->person_id)
            ->first();

        if ($existingInteraction) {
            return response()->json([
                'success' => false,
                'message' => 'You have already interacted with this person'
            ], 409);
        }

        // Create dislike interaction
        Interaction::create([
            'device_id' => $device->id,
            'person_id' => $request->person_id,
            'type' => 'dislike',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Person disliked successfully',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/interactions/liked",
     *     summary="Get list of liked people",
     *     description="Retrieve a paginated list of people you have liked",
     *     tags={"Interactions"},
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
     *         description="List of liked people retrieved successfully",
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
     *                         @OA\Property(property="country", type="string", example="Kuwait")
     *                     ),
     *                     @OA\Property(property="liked_at", type="string", format="date-time", example="2024-01-15 10:30:00")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=25),
     *                 @OA\Property(property="last_page", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function likedPeople(Request $request)
    {
        $device = $request->input('authenticated_device');
        $perPage = $request->input('per_page', 10);

        $likedPeople = Interaction::with('person')
            ->where('device_id', $device->id)
            ->where('type', 'like')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $likedPeople->map(function ($interaction) {
                return [
                    'id' => $interaction->person->id,
                    'name' => $interaction->person->name,
                    'age' => $interaction->person->age,
                    'pictures' => $interaction->person->pictures,
                    'location' => [
                        'city' => $interaction->person->city,
                        'country' => $interaction->person->country,
                    ],
                    'liked_at' => $interaction->created_at->toDateTimeString(),
                ];
            }),
            'pagination' => [
                'current_page' => $likedPeople->currentPage(),
                'per_page' => $likedPeople->perPage(),
                'total' => $likedPeople->total(),
                'last_page' => $likedPeople->lastPage(),
            ],
        ], 200);
    }
}
