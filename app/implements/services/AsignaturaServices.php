<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/repository/AsignaturaRepository.php';
require_once __DIR__ . '/../persistence/entitys/Asignatura.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\AsignaturaRepository;
use Entitys\Asignatura;
use Utils\Token;

class AsignaturaServices
{
    private AsignaturaRepository $asignaturaRepo;
    private array $security;

    public function __construct()
    {
        $this->asignaturaRepo = new AsignaturaRepository();
        $this->security       = require __DIR__ . '/../../properties/security.php';
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

        // Also support ?action=delete as an alias for delete
        $action = strtolower($queryParams['action'] ?? '');

        switch (true) {
            case $method === 'GET' && $resource === 'list':
                return $this->fetchAsignaturas((int)($queryParams['institucion'] ?? 0));

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                return $this->saveAsignatura(isset($queryParams['id']) ? (int)$queryParams['id'] : null);

            case $method === 'DELETE' && $resource === 'delete':
            case $method === 'PUT'    && $resource === 'delete':
            case $method === 'DELETE' && $action === 'delete':
            case $method === 'PUT'    && $action === 'delete':
                return $this->deleteAsignatura((int)($queryParams['id'] ?? 0));

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchAsignaturas
    // -------------------------------------------------------------------------
    private function fetchAsignaturas(int $institucionId): array
    {
        if ($institucionId <= 0) {
            throw new \RuntimeException('El parámetro institucion es requerido.', 400);
        }

        $rows   = $this->asignaturaRepo->findByInstitucionWithStats($institucionId);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'                 => (int)$row['id'],
                'nombre'             => $row['nombre'],
                'descripcion'        => $row['descripcion'],
                'estado'             => (int)$row['estado'],
                'total_matriculados' => (int)$row['total_matriculados'],
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // saveAsignatura
    // -------------------------------------------------------------------------
    private function saveAsignatura(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['nombre', 'descripcion', 'institucion'] as $field) {
            if (empty($data[$field]) && $data[$field] !== '0') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $nombre      = trim($data['nombre']);
        $descripcion = trim($data['descripcion']);
        $institucion = (int)$data['institucion'];
        $estado      = isset($data['estado']) ? (int)$data['estado'] : 1;

        // Resolve id: prefer URL param, then body
        $entityId = ($id !== null && $id > 0) ? $id : (isset($data['id']) && (int)$data['id'] > 0 ? (int)$data['id'] : null);

        if ($entityId !== null) {
            $existing = $this->asignaturaRepo->findById($entityId);
            if (!$existing) {
                throw new \RuntimeException('Asignatura no encontrada para actualizar.', 404);
            }
        }

        $asignatura = new Asignatura($entityId, $nombre, $descripcion, $institucion, $estado);
        $saved      = $this->asignaturaRepo->save($asignatura);

        return [
            'id'          => (int)$saved->getId(),
            'nombre'      => $saved->getNombre(),
            'descripcion' => $saved->getDescripcion(),
            'estado'      => (int)$saved->getEstado()?->getId(),
        ];
    }

    // -------------------------------------------------------------------------
    // deleteAsignatura
    // -------------------------------------------------------------------------
    private function deleteAsignatura(int $id): array
    {
        if ($id <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $existing = $this->asignaturaRepo->findById($id);
        if (!$existing) {
            throw new \RuntimeException('Asignatura no encontrada.', 404);
        }

        $this->asignaturaRepo->disable($id);

        return ['id' => $id, 'estado' => 2];
    }
}