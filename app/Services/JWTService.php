<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTService
{
    private string $secret;
    private string $algo;
    private int $ttl;

    public function __construct()
    {
        $this->secret = config('app.jwt_secret');
        $this->algo = config('app.jwt_algo', 'HS256');
        $this->ttl = config('app.jwt_ttl', 43200); // 12 hours in seconds
    }

    /**
     * Generate JWT token for a device
     */
    public function generateToken(int $deviceId, string $deviceUid): string
    {
        $payload = [
            'iss' => config('app.url'),
            'iat' => time(),
            'exp' => time() + $this->ttl,
            'device_id' => $deviceId,
            'device_uid' => $deviceUid,
        ];

        return JWT::encode($payload, $this->secret, $this->algo);
    }

    /**
     * Decode and verify JWT token
     */
    public function verifyToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Extract device ID from token
     */
    public function getDeviceIdFromToken(string $token): ?int
    {
        $payload = $this->verifyToken($token);
        return $payload->device_id ?? null;
    }
}
