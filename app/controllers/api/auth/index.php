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
    echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Método HTTP no permitido para este endpoint.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $encryptedPayload = trim((string) file_get_contents('php://input'));
    $encryptedPayload = trim($encryptedPayload, "\"\n\r\t ");

    if ($encryptedPayload === '') {
        throw new RuntimeException('El body cifrado es obligatorio.', 400);
    }

    $service = new AuthServices(new UserRepository());
    $result = $service->login($encryptedPayload);

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $result, 'message' => 'Autenticación exitosa.'], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $exception) {
    $statusCode = in_array($exception->getCode(), [400, 401, 500], true) ? $exception->getCode() : 400;
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => $statusCode === 401 ? 'Credenciales inválidas.' : ($statusCode === 500 ? 'Error interno de autenticación.' : $exception->getMessage()),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Error interno del servidor.'], JSON_UNESCAPED_UNICODE);
}
