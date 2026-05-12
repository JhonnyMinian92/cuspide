<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../implements/services/CompanyServices.php';

use Services\CompanyServices;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

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

  $service = new CompanyServices();
  $result = $service->processRequest((string) ($_SERVER['REQUEST_URI'] ?? ''), $_GET, $authorization);

  echo json_encode(['status' => 'success', 'data' => $result, 'message' => 'Consulta ejecutada correctamente.'], JSON_UNESCAPED_UNICODE);
} catch (\RuntimeException $exception) {
  $statusCode = in_array($exception->getCode(), [400, 401, 403, 404, 405, 500], true) ? $exception->getCode() : 400;
  http_response_code($statusCode);
  echo json_encode(['status' => 'error', 'data' => null, 'message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $exception) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Error interno: ' . $exception->getMessage()], JSON_UNESCAPED_UNICODE);
}