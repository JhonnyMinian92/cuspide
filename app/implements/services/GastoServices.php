<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/GastoServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/GastoRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/entitys/Gasto.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\GastoServiceInterface;
use Repositories\GastoRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Entitys\Gasto;
use RuntimeException;
use Utils\Token;

final class GastoServices implements GastoServiceInterface
{
    private string $jwtSecret;

    public function __construct()
    {
        $config = require __DIR__ . '/../../properties/security.php';
        $this->jwtSecret = (string) ($config['jwt_secret'] ?? '');
        if ($this->jwtSecret === '') {
            throw new RuntimeException('No existe configuración de seguridad para JWT.', 500);
        }
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $userId = (int) (Token::validateBearerToken($authHeader, $this->jwtSecret)['sub'] ?? 0);
        $instId = $this->getInstitucionByUserId($userId);

        $resource = strtolower(trim((string) ($queryParams['api'] ?? '')));

        return match (true) {
            $method === 'GET'                         && $resource === 'list' => $this->fetchGastos($instId, $queryParams),
            in_array($method, ['POST', 'PUT'], true)  && $resource === 'save' => $this->saveGasto(isset($queryParams['id']) ? (int) $queryParams['id'] : null, $instId, $userId),
            $method === 'DELETE'                      && $resource === 'delete' => $this->deleteGasto((int) ($queryParams['id'] ?? 0), $instId),
            default => throw new RuntimeException('Ruta de API no soportada: ' . $method . ' ' . $resource, 404),
        };
    }

    private function fetchGastos(int $instId, array $queryParams): array
    {
        $repo = new GastoRepository();
        $filters = [
            'limit'        => (int) ($queryParams['limit'] ?? 20),
            'page'         => (int) ($queryParams['page'] ?? 1),
            'fecha_inicio' => !empty($queryParams['fecha_inicio']) ? (string) $queryParams['fecha_inicio'] : null,
            'fecha_fin'    => !empty($queryParams['fecha_fin']) ? (string) $queryParams['fecha_fin'] : null,
        ];

        $result = $repo->findByInstitucion($instId, $filters);
        $items = $result['items'];
        $totalItems = $result['total'];

        return [
            'items' => array_map(fn($r) => [
                'id'           => (int) $r['id'],
                'numero'       => $r['numero'],
                'fecha'        => $r['fecha'],
                'descripcion'  => $r['descripcion'],
                'tipo_gasto'   => $r['tipo_gasto'],
                'monto'        => (float) $r['monto'],
                'referencia'   => $r['referencia'] ?? null,
                'observacion'  => $r['observacion'] ?? null,
                'persona_id'   => isset($r['persona']) ? (int) $r['persona'] : null,
                'nombre_persona' => $r['nombre_persona'] ?? null,
                'estado'       => (int) $r['estado'],
            ], $items),
            'totalItems'  => $totalItems,
            'totalPages'  => (int) ceil($totalItems / $filters['limit']),
            'currentPage' => $filters['page'],
        ];
    }

    private function saveGasto(?int $id, int $instId, int $userId): array
    {
        $body = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($body)) {
            throw new RuntimeException('Cuerpo de la solicitud inválido o vacío.', 400);
        }

        $numero      = (string) ($body['numero']      ?? '');
        $fecha       = (string) ($body['fecha']       ?? '');
        $descripcion = (string) ($body['descripcion'] ?? '');
        $tipoGasto   = (string) ($body['tipo_gasto']  ?? '');
        $monto       = (float)  ($body['monto']       ?? 0);
        $referencia  = isset($body['referencia'])  && $body['referencia']  !== '' ? (string) $body['referencia']  : null;
        $observacion = isset($body['observacion']) && $body['observacion'] !== '' ? (string) $body['observacion'] : null;
        $personaId   = isset($body['persona_id']) && $body['persona_id'] > 0 ? (int) $body['persona_id'] : null;
        $persona = (new PersonaRepository())->findByUserId($userId);
        if ($persona === null) {
            throw new RuntimeException('No se encontró persona asociada al usuario para registrar el gasto.', 404);
        }
        $personaId = (int) $persona->getId();
        $estado      = (int) ($body['estado'] ?? 1);

        if ($fecha === '' || $descripcion === '' || $tipoGasto === '') {
            throw new RuntimeException('Faltan campos obligatorios: fecha, descripcion, tipo_gasto.', 400);
        }

        if ($numero === '') {
            $numero = 'G-' . date('YmdHis');
        }

        $repo   = new GastoRepository();
        $entity = new Gasto($id ?: null, $numero, $fecha, $descripcion, $tipoGasto, $monto, $instId, $personaId, $referencia, $observacion, $estado);
        $saved  = $repo->save($entity);

        return [
            'id'          => $saved->getId(),
            'numero'      => $saved->getNumero(),
            'fecha'       => $saved->getFecha(),
            'descripcion' => $saved->getDescripcion(),
            'tipo_gasto'  => $saved->getTipoGasto(),
            'monto'       => $saved->getMonto(),
            'referencia'  => $saved->getReferencia(),
            'observacion' => $saved->getObservacion(),
            'estado'      => $saved->getEstado()?->getId() ?? $estado,
        ];
    }

    private function deleteGasto(int $id, int $instId): array
    {
        if ($id <= 0) {
            throw new RuntimeException('ID de gasto inválido.', 400);
        }
        $repo  = new GastoRepository();
        $gasto = $repo->findById($id);
        if ($gasto === null) {
            throw new RuntimeException('Gasto no encontrado.', 404);
        }
        if ($gasto->getInstitucion()?->getId() !== $instId) {
            throw new RuntimeException('No tiene permisos para eliminar este gasto.', 403);
        }
        $gasto->setEstado(2);
        $repo->save($gasto);
        return ['id' => $id, 'deleted' => true];
    }

    private function getInstitucionByUserId(int $userId): int
    {
        $persona = (new PersonaRepository())->findByUserId($userId);
        if ($persona === null) {
            throw new RuntimeException('No se encontró persona asociada al usuario.', 404);
        }
        $pi = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());
        if ($pi === null) {
            throw new RuntimeException('No se encontró institución asociada al usuario.', 404);
        }
        return (int) $pi->getIdInstitucion()->getId();
    }
}
