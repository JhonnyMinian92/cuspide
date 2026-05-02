<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/AuthServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/UserRepository.php';
require_once __DIR__ . '/../utils/Encripter.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\AuthServiceInterface;
use Repositories\UserRepository;
use RuntimeException;
use Utils\Encripter;
use Utils\Token;

final class AuthServices implements AuthServiceInterface
{
    private string $privateKeyPem;
    private string $jwtSecret;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $securityConfig = require __DIR__ . '/../../properties/security.php';

        $this->privateKeyPem = (string) ($securityConfig['rsa_private_key'] ?? '');
        $this->jwtSecret = (string) ($securityConfig['jwt_secret'] ?? '');

        if ($this->privateKeyPem === '' || $this->jwtSecret === '') {
            throw new RuntimeException('Error de configuración del servicio.', 500);
        }
    }

    public function login(string $encryptedPayload): array
    {
        $decryptedJson = Encripter::decryptWithPrivateKey($encryptedPayload, $this->privateKeyPem);
        $credentials = json_decode($decryptedJson, true);

        if (!is_array($credentials)) {
            throw new RuntimeException('Payload de credenciales inválido.', 400);
        }

        $username = filter_var((string) ($credentials['username'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = (string) ($credentials['password'] ?? '');

        if ($username === '' || $password === '') {
            throw new RuntimeException('username y password son obligatorios.', 400);
        }

        $user = $this->userRepository->findByUsername($username);
        if ($user === null) {
            throw new RuntimeException('El usuario ' . $username . ' es inválido.', 401);
        }

        if (!password_verify($password, $user->getPassword())) {
            throw new RuntimeException('La contraseña es incorrecta. ' . $password, 401);
        }

        $token = Token::createJwt([
            'sub' => (string) $user->getId(),
            'username' => $user->getUsername(),
            'role' => $user->getType()->getNemonico(),
        ], $this->jwtSecret, 3600);

        return [
            'token' => $token,
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ];
    }
}