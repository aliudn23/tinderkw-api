<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Interaction;

class PersonController extends Controller
{
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
