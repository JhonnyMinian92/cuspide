<?php

declare(strict_types=1);

return [
    // Para JWT HS256 (mantener fuera de control de versiones en producción).
    'jwt_secret' => 'cuspide-change-this-jwt-secret',

    // Llave privada para descifrar contraseña enviada desde frontend.
    'rsa_private_key' => <<<'PEM'
-----BEGIN PRIVATE KEY-----
REEMPLAZAR_CON_TU_LLAVE_PRIVADA_RSA
-----END PRIVATE KEY-----
PEM,

    // Llave pública para cifrado desde frontend JS.
    'rsa_public_key' => <<<'PEM'
-----BEGIN PUBLIC KEY-----
REEMPLAZAR_CON_TU_LLAVE_PUBLICA_RSA
-----END PUBLIC KEY-----
PEM,
];
