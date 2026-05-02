<?php

declare(strict_types=1);

namespace Services;

use Interfaces\AuthServiceInterface;
use Repositories\UserRepository;
use RuntimeException;
use Utils\Encripter;
use Utils\Token;

final class AuthServices implements AuthServiceInterface
{
    //la llave privada y la clave secreta debe obtenerse aquí no en el controlador, el controlador solo debe recibir los datos de autenticación y pasarla al servicio, el servicio se encarga de todo lo demás, incluido el manejo de errores y la generación del token
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly string $privateKeyPem,
        private readonly string $jwtSecret
    ) {}

    public function login(string $username, string $encryptedPassword): array
    {
        $password = Encripter::decryptWithPrivateKey($encryptedPassword, $this->privateKeyPem);

        $user = $this->userRepository->findByUsername($username);
        if ($user === null || !password_verify($password, $user->getPassword())) {
            throw new RuntimeException('Credenciales inválidas.', 401);
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