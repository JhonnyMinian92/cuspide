<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Utils\Token;

class DeporteServices
{
    private array $security;

    public function __construct()
    {
        $this->security = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        // Detect resource from ?api= param first, then URL path
        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'list':
                return $this->fetchDeportes();

            case $method === 'POST' && $resource === 'save':
                return $this->saveDeporte(null);

            case $method === 'PUT' && $resource === 'save':
                return $this->saveDeporte(isset($queryParams['id']) ? (int)$queryParams['id'] : null);

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchDeportes
    // -------------------------------------------------------------------------
    private function fetchDeportes(): array
    {
        $db  = Database::getInstance();
        $sql = "SELECT id, nombre, descripcion, estado
                FROM deportes
                WHERE estado = 1
                ORDER BY nombre ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        $rows   = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'          => (int)$row['id'],
                'nombre'      => $row['nombre'],
                'descripcion' => $row['descripcion'],
                'estado'      => (int)$row['estado'],
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // saveDeporte
    // -------------------------------------------------------------------------
    private function saveDeporte(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        if (empty($data['nombre'])) {
            throw new \RuntimeException('El campo nombre es requerido.', 400);
        }

        // Resolve id: prefer URL param, then body
        $entityId = ($id !== null && $id > 0) ? $id : (isset($data['id']) && (int)$data['id'] > 0 ? (int)$data['id'] : null);

        $nombre      = trim($data['nombre']);
        $descripcion = trim($data['descripcion'] ?? '');
        $estado      = isset($data['estado']) ? (int)$data['estado'] : 1;

        $db = Database::getInstance();

        if ($entityId !== null) {
            // Verify existence
            $stmtCheck = $db->prepare('SELECT id FROM deportes WHERE id = :id');
            $stmtCheck->bindValue(':id', $entityId, \PDO::PARAM_INT);
            $stmtCheck->execute();
            if (!$stmtCheck->fetch()) {
                throw new \RuntimeException('Deporte no encontrado para actualizar.', 404);
            }

            $stmt = $db->prepare(
                'UPDATE deportes SET nombre = :nombre, descripcion = :descripcion, estado = :estado WHERE id = :id'
            );
            $stmt->bindValue(':nombre',      $nombre);
            $stmt->bindValue(':descripcion', $descripcion);
            $stmt->bindValue(':estado',      $estado, \PDO::PARAM_INT);
            $stmt->bindValue(':id',          $entityId, \PDO::PARAM_INT);
            $stmt->execute();

            $savedId = $entityId;
        } else {
            $stmt = $db->prepare(
                'INSERT INTO deportes (nombre, descripcion, estado) VALUES (:nombre, :descripcion, :estado)'
            );
            $stmt->bindValue(':nombre',      $nombre);
            $stmt->bindValue(':descripcion', $descripcion);
            $stmt->bindValue(':estado',      $estado, \PDO::PARAM_INT);
            $stmt->execute();

            $savedId = (int)$db->lastInsertId();
        }

        return [
            'id'     => $savedId,
            'nombre' => $nombre,
        ];
    }
}
