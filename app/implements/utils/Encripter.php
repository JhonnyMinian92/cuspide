<?php

declare(strict_types=1);

namespace Utils;

use RuntimeException;

final class Encripter
{
    public static function decryptWithPrivateKey(string $encryptedPayload, string $privateKeyPem): string
    {
        $privateKey = openssl_pkey_get_private($privateKeyPem);

        if ($privateKey === false) {
            throw new RuntimeException('No fue posible cargar la llave privada.');
        }

        $encryptedBinary = base64_decode($encryptedPayload, true);
        if ($encryptedBinary === false) {
            throw new RuntimeException('La contraseña cifrada no está en Base64 válido.');
        }

        $decryptedText = '';
        $isDecrypted = openssl_private_decrypt(
            $encryptedBinary,
            $decryptedText,
            $privateKey,
            OPENSSL_PKCS1_OAEP_PADDING
        );

        openssl_free_key($privateKey);

        if (!$isDecrypted) {
            throw new RuntimeException('No fue posible descifrar las credenciales.');
        }

        return $decryptedText;
    }
}
