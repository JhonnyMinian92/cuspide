<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../implements/services/UserServices.php';

use Services\UserServices;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Método HTTP no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($authorization === '' && function_exists('getallheaders')) {
        $headers = getallheaders();
        $authorization = (string) ($headers['Authorization'] ?? '');
    }

    $service = new UserServices();
    $result = $service->processRequest((string) ($_SERVER['REQUEST_URI'] ?? ''), $_GET, $authorization);

    echo json_encode(['status' => 'success', 'data' => $result, 'message' => 'Consulta ejecutada correctamente.'], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $exception) {
    $statusCode = in_array($exception->getCode(), [400, 401, 404, 405, 500], true) ? $exception->getCode() : 400;
    http_response_code($statusCode);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Error interno: ' . $exception->getMessage()], JSON_UNESCAPED_UNICODE);
}
