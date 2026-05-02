<?php

declare(strict_types=1);

use Repositories\UserRepository;
use Services\AuthServices;

require_once __DIR__ . '/../../../implements/persistence/conexion/Database.php';
require_once __DIR__ . '/../../../implements/persistence/entitys/TypeUser.php';
require_once __DIR__ . '/../../../implements/persistence/entitys/User.php';
require_once __DIR__ . '/../../../implements/persistence/repository/TypeUserRepository.php';
require_once __DIR__ . '/../../../implements/persistence/repository/UserRepository.php';
require_once __DIR__ . '/../../../services/AuthServices.php';
require_once __DIR__ . '/../../../../interfaces/AuthServiceInterface.php';
require_once __DIR__ . '/../../../utils/Encripter.php';
require_once __DIR__ . '/../../../utils/Token.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Método HTTP no permitido para este endpoint.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $rawBody = file_get_contents('php://input');
    if (!is_string($rawBody) || trim($rawBody) === '') {
        throw new InvalidArgumentException('El body no puede estar vacío.', 400);
    }

    $payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($payload)) {
        throw new InvalidArgumentException('Formato JSON inválido.', 400);
    }

    $username = filter_var((string) ($payload['username'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $encryptedPassword = trim((string) ($payload['password'] ?? ''));

    if ($username === '' || $encryptedPassword === '') {
        throw new InvalidArgumentException('username y password son obligatorios.', 400);
    }

    $securityConfig = require __DIR__ . '/../../../properties/security.php';

    $privateKeyPem = (string) ($securityConfig['rsa_private_key'] ?? '');
    $jwtSecret = (string) ($securityConfig['jwt_secret'] ?? '');

    if ($privateKeyPem === '' || $jwtSecret === '') {
        throw new RuntimeException('Error de configuración del servicio.', 500);
    }

    $service = new AuthServices(new UserRepository(), $privateKeyPem, $jwtSecret);
    $result = $service->login($username, $encryptedPassword);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $result,
        'message' => 'Autenticación exitosa.',
    ], JSON_UNESCAPED_UNICODE);
} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $exception) {
    $statusCode = $exception->getCode() === 401 ? 401 : 500;

    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => $statusCode === 401 ? 'Credenciales inválidas.' : 'Error interno de autenticación.',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Error interno del servidor.',
    ], JSON_UNESCAPED_UNICODE);
}
