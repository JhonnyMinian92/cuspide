<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../implements/services/PermisoServices.php';

use Services\PermisoServices;

header('Content-Type: application/json; charset=utf-8');

$method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$allowed = ['GET', 'POST'];

if (!in_array($method, $allowed, true)) {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Método HTTP no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($authorization === '' && function_exists('getallheaders')) {
        $headers       = getallheaders();
        $authorization = (string) ($headers['Authorization'] ?? $headers['authorization'] ?? '');
    }

    $service = new PermisoServices();
    $result  = $service->processRequest(
        $method,
        (string) ($_SERVER['REQUEST_URI'] ?? ''),
        $_GET,
        $authorization
    );

    echo json_encode(['status' => 'success', 'data' => $result, 'message' => 'OK.'], JSON_UNESCAPED_UNICODE);

} catch (\RuntimeException $e) {
    $code = in_array($e->getCode(), [400, 401, 404, 405, 500], true) ? $e->getCode() : 400;
    http_response_code($code);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Error interno: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
