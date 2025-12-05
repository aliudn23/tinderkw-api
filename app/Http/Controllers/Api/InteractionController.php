<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Interaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\PersonPopularNotification;
use Illuminate\Support\Facades\DB;

class InteractionController extends Controller
{
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
                $adminEmail = config('app.admin_email');
                Mail::to($adminEmail)->send(new PersonPopularNotification($person));
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
