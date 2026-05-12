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



    public static function validateBearerToken(string $authorizationHeader, string $secret): array
    {
        if (!preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches)) {
            throw new RuntimeException('Token Bearer requerido.', 401);
        }

        $parts = explode('.', trim($matches[1]));
        if (count($parts) !== 3) {
            throw new RuntimeException('Token inválido.', 401);
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $expectedSignature = rtrim(strtr(base64_encode(hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $secret, true)), '+/', '-_'), '=');

        if (!hash_equals($expectedSignature, $encodedSignature)) {
            throw new RuntimeException('Firma del token inválida.', 401);
        }

        $payload = json_decode((string) base64_decode(strtr($encodedPayload, '-_', '+/')), true);
        if (!is_array($payload) || !isset($payload['exp']) || (int) $payload['exp'] < time()) {
            throw new RuntimeException('Token expirado o inválido.', 401);
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
