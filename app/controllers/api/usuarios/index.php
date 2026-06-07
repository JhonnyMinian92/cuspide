<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../implements/services/UsuarioServices.php';

use Services\UsuarioServices;

header('Content-Type: application/json; charset=utf-8');

// Allow multiple methods used by the service
$allowedMethods = ['GET', 'POST', 'PUT', 'DELETE'];
if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods, true)) {
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

    $service = new UsuarioServices();
    $result = $service->processRequest($_SERVER['REQUEST_METHOD'], (string) ($_SERVER['REQUEST_URI'] ?? ''), $_GET, $authorization);

    echo json_encode(['status' => 'success', 'data' => $result, 'message' => 'OK.'], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $exception) {
    $statusCode = in_array($exception->getCode(), [400, 401, 403, 404, 405, 409, 500], true) ? $exception->getCode() : 400;
    http_response_code($statusCode);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Error interno: ' . $exception->getMessage()], JSON_UNESCAPED_UNICODE);
}
