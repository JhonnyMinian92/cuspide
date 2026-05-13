<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/PagoServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/PagoRepository.php';
require_once __DIR__ . '/../persistence/repository/VentaRepository.php';
require_once __DIR__ . '/../persistence/entitys/Pago.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\PagoServiceInterface;
use Repositories\PagoRepository;
use Repositories\VentaRepository;
use Entitys\Pago;
use RuntimeException;
use Utils\Token;

final class PagoServices implements PagoServiceInterface
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

        $resource = $this->resolveResource($requestUri, $queryParams, 'pagos');

        return match (true) {
            $method === 'GET'  && $resource === 'pending'   => $this->fetchPendientes($queryParams),
            $method === 'GET'  && $resource === 'history'   => $this->fetchHistorial($queryParams),
            $method === 'POST' && $resource === 'register'  => $this->registrarPago(),
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
    // fetchPendientes
    // -------------------------------------------------------------------------

    private function fetchPendientes(array $queryParams): array
    {
        $institucion = (int) ($queryParams['institucion'] ?? 0);
        if ($institucion <= 0) {
            throw new RuntimeException('Debe proporcionar el parámetro institucion.', 400);
        }

        $db  = \Core\Database::getInstance();
        $sql = "SELECT v.id, v.numero, v.fecha, v.total, v.saldo, v.tipo,
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
            'id'              => (int)   $r['id'],
            'numero'          => $r['numero'],
            'fecha'           => $r['fecha'],
            'total'           => (float) $r['total'],
            'saldo'           => (float) $r['saldo'],
            'tipo'            => $r['tipo'],
            'identificacion'  => $r['identificacion'],
            'nombre'          => $r['nombre'],
        ], $rows);
    }

    // -------------------------------------------------------------------------
    // fetchHistorial
    // -------------------------------------------------------------------------

    private function fetchHistorial(array $queryParams): array
    {
        $personaId = $this->resolvePersonaId($queryParams);

        $pagoRepo = new PagoRepository();
        $ventaRepo = new VentaRepository();

        $pagos   = $pagoRepo->findByPersona($personaId);
        $ventas  = $ventaRepo->findByPersona($personaId);

        $pagosData = array_map(fn($p) => [
            'id'         => $p->getId(),
            'numero'     => $p->getNumero(),
            'fecha'      => $p->getFecha(),
            'monto'      => $p->getMonto(),
            'forma_pago' => $p->getFormaPago() !== null ? $p->getFormaPago()->getId() : null,
            'referencia' => $p->getReferencia(),
            'observacion'=> $p->getObservacion(),
            'estado'     => $p->getEstado() !== null ? $p->getEstado()->getId() : null,
        ], $pagos);

        $ventasData = array_map(fn($v) => [
            'id'       => $v->getId(),
            'numero'   => $v->getNumero(),
            'fecha'    => $v->getFecha(),
            'total'    => $v->getTotal(),
            'saldo'    => $v->getSaldo(),
            'tipo'     => $v->getTipo(),
            'estado'   => $v->getEstado() !== null ? $v->getEstado()->getId() : null,
        ], $ventas);

        return [
            'persona_id' => $personaId,
            'pagos'      => $pagosData,
            'facturas'   => $ventasData,
        ];
    }

    // -------------------------------------------------------------------------
    // registrarPago
    // -------------------------------------------------------------------------

    private function registrarPago(): array
    {
        $body = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($body)) {
            throw new RuntimeException('Cuerpo de la solicitud inválido o vacío.', 400);
        }

        $persona     = (int)    ($body['persona']     ?? 0);
        $ventaId     = isset($body['venta'])      ? (int) $body['venta']       : null;
        $matriculaId = isset($body['matricula'])   ? (int) $body['matricula']   : null;
        $formaPago   = (int)    ($body['forma_pago']  ?? 0);
        $institucion = (int)    ($body['institucion']  ?? 0);
        $monto       = (float)  ($body['monto']        ?? 0);
        $referencia  = (string) ($body['referencia']   ?? '');
        $observacion = (string) ($body['observacion']  ?? '');

        if ($persona <= 0 || $formaPago <= 0 || $institucion <= 0 || $monto <= 0) {
            throw new RuntimeException('Faltan campos obligatorios: persona, forma_pago, institucion, monto.', 400);
        }

        $db = \Core\Database::getInstance();

        try {
            $db->beginTransaction();

            // Find oldest pending ventas for this persona (FIFO)
            $sql = 'SELECT id, saldo FROM ventas WHERE persona = :persona AND saldo > 0 AND estado = 1 ORDER BY fecha ASC';
            $stmt = $db->prepare($sql);
            $stmt->execute([':persona' => $persona]);
            $pendientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $numeroPago     = 'PAG-' . date('Ymd') . '-' . uniqid('', false);
            $fechaPago      = date('Y-m-d H:i:s');
            $montoRestante  = $monto;
            $ventasSaldadas = [];
            $detalles       = [];

            foreach ($pendientes as $ven) {
                if ($montoRestante <= 0) {
                    break;
                }

                $venId      = (int)   $ven['id'];
                $saldoVenta = (float) $ven['saldo'];
                $aplicado   = min($montoRestante, $saldoVenta);
                $nuevoSaldo = $saldoVenta - $aplicado;

                // Update venta saldo
                if ($nuevoSaldo <= 0) {
                    $stmtUpd = $db->prepare('UPDATE ventas SET saldo = 0, estado = 2 WHERE id = :id');
                    $stmtUpd->execute([':id' => $venId]);
                    $ventasSaldadas[] = $venId;
                } else {
                    $stmtUpd = $db->prepare('UPDATE ventas SET saldo = :saldo WHERE id = :id');
                    $stmtUpd->execute([':saldo' => $nuevoSaldo, ':id' => $venId]);
                }

                $montoRestante -= $aplicado;
                $detalles[]     = ['venta_id' => $venId, 'aplicado' => $aplicado];
            }

            $montoAplicado = $monto - max(0.0, $montoRestante);

            // Insert Pago record
            // Use ventaId from body if provided; otherwise use the first venta paid
            $firstVentaId = $ventaId ?? ($detalles[0]['venta_id'] ?? null);

            $pagoEntity = new Pago(
                null,
                $numeroPago,
                $fechaPago,
                $persona,
                $matriculaId,
                $firstVentaId,
                $formaPago,
                $institucion,
                $monto,
                $referencia !== '' ? $referencia : null,
                $observacion,
                1
            );

            $pagoRepo   = new PagoRepository();
            $pagoEntity = $pagoRepo->save($pagoEntity);
            $pagoId     = (int) $pagoEntity->getId();

            // Insert PagoDetalle for each venta paid
            foreach ($detalles as $det) {
                $stmtDet = $db->prepare(
                    'INSERT INTO pago_detalle (pago, venta, monto_aplicado) VALUES (:pago, :venta, :monto_aplicado)'
                );
                $stmtDet->execute([
                    ':pago'           => $pagoId,
                    ':venta'          => $det['venta_id'],
                    ':monto_aplicado' => $det['aplicado'],
                ]);
            }

            $db->commit();

            return [
                'numero_pago'     => $numeroPago,
                'monto_aplicado'  => $montoAplicado,
                'ventas_saldadas' => $ventasSaldadas,
                'saldo_restante'  => max(0.0, $montoRestante),
            ];

        } catch (\Throwable $e) {
            $db->rollBack();
            throw new RuntimeException('Error al registrar pago: ' . $e->getMessage(), 500);
        }
    }
}
