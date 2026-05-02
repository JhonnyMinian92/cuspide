<?php

declare(strict_types=1);

namespace Utils;

use RuntimeException;

final class Token
{
    public static function createJwt(array $payload, string $secret, int $ttlSeconds = 3600): string
    {
        $now = time();

        $jwtPayload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $ttlSeconds,
        ]);

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $encodedHeader = self::base64UrlEncode((string) json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = self::base64UrlEncode((string) json_encode($jwtPayload, JSON_THROW_ON_ERROR));

        $signature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $secret, true);
        if ($signature === false) {
            throw new RuntimeException('No fue posible firmar el token.');
        }

        return $encodedHeader . '.' . $encodedPayload . '.' . self::base64UrlEncode($signature);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
