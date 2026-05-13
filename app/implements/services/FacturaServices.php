<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/FacturaServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/VentaRepository.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\FacturaServiceInterface;
use Repositories\VentaRepository;
use RuntimeException;
use Utils\Token;

final class FacturaServices implements FacturaServiceInterface
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
        $payload = Token::validateBearerToken($authHeader, $this->jwtSecret);

        $resource = $this->resolveResource($requestUri, $queryParams, 'facturas');

        return match (true) {
            $method === 'GET' && $resource === 'list'      => $this->fetchFacturas($queryParams),
            $method === 'GET' && $resource === 'byclient'  => $this->fetchByClient($queryParams),
            $method === 'GET' && $resource === 'pending'   => $this->fetchPendientes($queryParams),
            $method === 'GET' && $resource === 'detail'    => $this->fetchDetail((int) ($queryParams['id'] ?? 0)),
            $method === 'PUT' && $resource === 'cancel'    => $this->cancelFactura((int) ($queryParams['id'] ?? 0)),
            default => throw new RuntimeException('Ruta de API no soportada: ' . $method . ' ' . $resource, 404),
        };
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resolveResource(string $requestUri, array $queryParams, string $controllerSegment): string
    {
        $tipo = strtolower(trim((string) ($queryParams['api'] ?? '')));
        if ($tipo !== '') {
            return $tipo;
        }

        $path     = parse_url($requestUri, PHP_URL_PATH) ?? '';
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        $idx = array_search($controllerSegment, $segments, true);
        if ($idx !== false) {
            $candidate = $segments[$idx + 1] ?? null;
            if ($candidate !== null && strtolower($candidate) !== 'index.php') {
                return strtolower($candidate);
            }
        }

        if ($segments !== []) {
            $last = strtolower((string) $segments[array_key_last($segments)]);
            if ($last !== 'index.php') {
                return $last;
            }
        }

        return '';
    }

    private function resolvePersonaId(array $queryParams): int
    {
        $db = \Core\Database::getInstance();

        if (!empty($queryParams['persona_id'])) {
            return (int) $queryParams['persona_id'];
        }

        if (!empty($queryParams['identificacion'])) {
            $stmt = $db->prepare('SELECT id FROM personas WHERE identificacion = :identificacion LIMIT 1');
            $stmt->execute([':identificacion' => (string) $queryParams['identificacion']]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException('No se encontró persona con esa identificación.', 404);
            }
            return (int) $row['id'];
        }

        throw new RuntimeException('Debe proporcionar persona_id o identificacion.', 400);
    }

    // -------------------------------------------------------------------------
    // fetchFacturas
    // -------------------------------------------------------------------------

    private function fetchFacturas(array $queryParams): array
    {
        $institucion = (int) ($queryParams['institucion'] ?? 0);
        if ($institucion <= 0) {
            throw new RuntimeException('Debe proporcionar el parámetro institucion.', 400);
        }

        $limit  = max(1, (int) ($queryParams['limit']  ?? 100));
        $offset = max(0, (int) ($queryParams['offset'] ?? 0));

        $db  = \Core\Database::getInstance();
        $sql = "SELECT v.id, v.numero, v.fecha, v.total, v.saldo, v.tipo, v.estado,
                       p.identificacion,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre
                FROM ventas v
                JOIN personas p ON p.id = v.persona
                WHERE v.institucion = :institucion
                ORDER BY v.fecha DESC
                LIMIT :lim OFFSET :off";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':institucion', $institucion, \PDO::PARAM_INT);
        $stmt->bindValue(':lim',         $limit,       \PDO::PARAM_INT);
        $stmt->bindValue(':off',         $offset,      \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($r) => [
            'id'             => (int)   $r['id'],
            'numero'         => $r['numero'],
            'fecha'          => $r['fecha'],
            'total'          => (float) $r['total'],
            'saldo'          => (float) $r['saldo'],
            'tipo'           => $r['tipo'],
            'estado'         => (int)   $r['estado'],
            'identificacion' => $r['identificacion'],
            'nombre'         => $r['nombre'],
        ], $rows);
    }

    // -------------------------------------------------------------------------
    // fetchByClient
    // -------------------------------------------------------------------------

    private function fetchByClient(array $queryParams): array
    {
        $personaId  = $this->resolvePersonaId($queryParams);
        $ventaRepo  = new VentaRepository();
        $ventas     = $ventaRepo->findByPersona($personaId);

        return array_map(fn($v) => [
            'id'     => $v->getId(),
            'numero' => $v->getNumero(),
            'fecha'  => $v->getFecha(),
            'total'  => $v->getTotal(),
            'saldo'  => $v->getSaldo(),
            'tipo'   => $v->getTipo(),
            'estado' => $v->getEstado() !== null ? $v->getEstado()->getId() : null,
            'pagada' => ((float) $v->getSaldo()) <= 0,
        ], $ventas);
    }

    // -------------------------------------------------------------------------
    // cancelFactura
    // -------------------------------------------------------------------------

    private function cancelFactura(int $id): array
    {
        if ($id <= 0) {
            throw new RuntimeException('ID de factura inválido.', 400);
        }

        $db   = \Core\Database::getInstance();
        $stmt = $db->prepare('UPDATE ventas SET estado = 2 WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return [
            'id'    => $id,
            'estado'=> 'ANULADO',
        ];
    }

    // -------------------------------------------------------------------------
    // fetchDetail
    // -------------------------------------------------------------------------

    private function fetchDetail(int $id): array
    {
        if ($id <= 0) {
            throw new RuntimeException('ID de factura inválido.', 400);
        }

        $db = \Core\Database::getInstance();
        $stmt = $db->prepare('SELECT vd.* FROM venta_detalle vd WHERE vd.venta = :id ORDER BY vd.id ASC');
        $stmt->execute([':id' => $id]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'descripcion' => $row['descripcion'] ?? null,
            'producto' => $row['descripcion'] ?? null,
            'cantidad' => (float) $row['cantidad'],
            'precio_unitario' => (float) $row['precio_unitario'],
            'descuento' => (float) $row['descuento'],
            'subtotal' => (float) ($row['subtotal'] ?? $row['total'] ?? 0),
            'total' => (float) ($row['total'] ?? $row['subtotal'] ?? 0),
        ], $rows);
    }

    // -------------------------------------------------------------------------
    // fetchPendientes
    // -------------------------------------------------------------------------

    private function fetchPendientes(array $queryParams): array
    {
        $institucion = (int) ($queryParams['institucion'] ?? 0);
        if ($institucion <= 0) {
            throw new RuntimeException('Debe proporcionar el parámetro institucion.', 400);
        }

        $db  = \Core\Database::getInstance();
        $sql = "SELECT v.id, v.numero, v.fecha, v.total, v.saldo, v.tipo, v.estado,
                       p.identificacion,
                       CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS nombre
                FROM ventas v
                JOIN personas p ON p.id = v.persona
                WHERE v.saldo > 0
                  AND v.estado = 1
                  AND v.institucion = :institucion
                ORDER BY v.fecha ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':institucion' => $institucion]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($r) => [
            'id'             => (int)   $r['id'],
            'numero'         => $r['numero'],
            'fecha'          => $r['fecha'],
            'total'          => (float) $r['total'],
            'saldo'          => (float) $r['saldo'],
            'tipo'           => $r['tipo'],
            'estado'         => (int)   $r['estado'],
            'identificacion' => $r['identificacion'],
            'nombre'         => $r['nombre'],
        ], $rows);
    }
}
