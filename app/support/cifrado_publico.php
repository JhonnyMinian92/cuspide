<?php

declare(strict_types=1);

$config = require __DIR__ . '/../properties/security.php';

$username = $argv[1] ?? 'jminan';
$password = $argv[2] ?? 'Zeus2@26';
$plainJson = json_encode(['username' => $username, 'password' => $password], JSON_UNESCAPED_UNICODE);

$publicKey = openssl_pkey_get_public($config['rsa_public_key']);
if ($publicKey === false || $plainJson === false) {
    fwrite(STDERR, "No se pudo preparar el cifrado.\n");
    exit(1);
}

$encrypted = '';
$ok = openssl_public_encrypt($plainJson, $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);
openssl_free_key($publicKey);

if (!$ok) {
    fwrite(STDERR, "No se pudo cifrar el payload login.\n");
    exit(1);
}

echo base64_encode($encrypted) . PHP_EOL;