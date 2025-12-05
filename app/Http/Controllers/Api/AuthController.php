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
