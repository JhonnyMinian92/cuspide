<?php

declare(strict_types=1);
require_once __DIR__ . '/../../../implements/services/AuthServices.php';

use Services\AuthServices;

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

    $service = new AuthServices();
    $result = $service->login($encryptedPayload);

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $result, 'message' => 'Autenticación exitosa.'], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $exception) {
    $statusCode = in_array($exception->getCode(), [400, 401, 500], true) ? $exception->getCode() : 400;
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    $message = 'Error no capturado: ' . $exception->getMessage() . ' - ' . $exception->getFile() . ':' . $exception->getLine();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => $message], JSON_UNESCAPED_UNICODE);
}