<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Services\JWTService;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected JWTService $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * @OA\Post(
     *     path="/api/register-device",
     *     summary="Register a new device and get JWT token",
     *     description="Register a device to receive a JWT token for authentication. If device already exists, returns a refreshed token.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"device_id"},
     *             @OA\Property(property="device_id", type="string", example="unique-device-identifier-123", description="Unique device identifier"),
     *             @OA\Property(property="device_type", type="string", example="iOS", description="Type of device"),
     *             @OA\Property(property="device_model", type="string", example="iPhone 14 Pro", description="Device model")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Device registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Device registered successfully"),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
     *             @OA\Property(
     *                 property="device",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="device_id", type="string", example="unique-device-identifier-123"),
     *                 @OA\Property(property="device_type", type="string", example="iOS"),
     *                 @OA\Property(property="device_model", type="string", example="iPhone 14 Pro")
     *             )
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
     *     )
     * )
     */
    public function registerDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:50',
            'device_model' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if device already exists
        $device = Device::where('device_id', $request->device_id)->first();

        if ($device) {
            // Update existing device info
            $device->update([
                'device_type' => $request->device_type ?? $device->device_type,
                'device_model' => $request->device_model ?? $device->device_model,
                'last_active_at' => now(),
            ]);

            $message = 'Device already registered, token refreshed';
        } else {
            // Create new device
            $device = Device::create([
                'device_id' => $request->device_id,
                'device_type' => $request->device_type,
                'device_model' => $request->device_model,
                'last_active_at' => now(),
            ]);

            $message = 'Device registered successfully';
        }

        // Generate JWT token
        $token = $this->jwtService->generateToken($device->id, $device->device_id);

        return response()->json([
            'success' => true,
            'message' => $message,
            'token' => $token,
            'device' => [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'device_type' => $device->device_type,
                'device_model' => $device->device_model,
            ],
        ], 200);
    }
}
