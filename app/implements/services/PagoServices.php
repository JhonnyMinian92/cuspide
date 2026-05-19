<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/PagoServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/PagoRepository.php';
require_once __DIR__ . '/../persistence/repository/PagoDetalleRepository.php';
require_once __DIR__ . '/../persistence/repository/VentaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/FormaPagoRepository.php';
require_once __DIR__ . '/../persistence/entitys/Pago.php';
require_once __DIR__ . '/../persistence/entitys/PagoDetalle.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Interfaces\PagoServiceInterface;
use Repositories\PagoRepository;
use Repositories\PagoDetalleRepository;
use Repositories\VentaRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Repositories\FormaPagoRepository;
use Entitys\Pago;
use Entitys\PagoDetalle;
use RuntimeException;
use Utils\Token;

final class PagoServices implements PagoServiceInterface
{
    private string $jwtSecret;
    private PagoRepository        $pagoRepo;
    private PagoDetalleRepository $pagoDetalleRepo;
    private VentaRepository       $ventaRepo;
    private PersonaRepository     $personaRepo;
    private FormaPagoRepository   $formaPagoRepo;

    public function __construct()
    {
        $config = require __DIR__ . '/../../properties/security.php';
        $this->jwtSecret = (string) ($config['jwt_secret'] ?? '');
        if ($this->jwtSecret === '') {
            throw new RuntimeException('No existe configuración de seguridad para JWT.', 500);
        }
        $this->pagoRepo        = new PagoRepository();
        $this->pagoDetalleRepo = new PagoDetalleRepository();
        $this->ventaRepo       = new VentaRepository();
        $this->personaRepo     = new PersonaRepository();
        $this->formaPagoRepo   = new FormaPagoRepository();
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->jwtSecret);
        $userId  = (int) ($payload['sub'] ?? 0);

        $resource = $this->resolveResource($requestUri, $queryParams, 'pagos');

        return match (true) {
            $method === 'GET'  && $resource === 'pending'        => $this->fetchPendientes($queryParams, $userId),
            $method === 'GET'  && $resource === 'history'        => $this->fetchHistorial($queryParams),
            $method === 'GET'  && $resource === 'formas'         => $this->fetchFormasPago(),
            $method === 'GET'  && $resource === 'detail'         => $this->fetchDetalle($queryParams),
            $method === 'POST' && $resource === 'register'       => $this->registrarPago($userId),
            $method === 'POST' && $resource === 'anular'         => $this->anularPago($userId),
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
        if (!empty($queryParams['persona_id'])) {
            return (int) $queryParams['persona_id'];
        }

        if (!empty($queryParams['identificacion'])) {
            $persona = $this->personaRepo->findByIdentificacion((string) $queryParams['identificacion']);
            if ($persona === null) {
                throw new RuntimeException('No se encontró persona con esa identificación.', 404);
            }
            return (int) $persona->getId();
        }

        throw new RuntimeException('Debe proporcionar persona_id o identificacion.', 400);
    }

    // -------------------------------------------------------------------------
    // fetchPendientes
    // -------------------------------------------------------------------------

    private function fetchPendientes(array $queryParams, int $userId): array
    {
        $institucion = (int) ($queryParams['institucion'] ?? 0);
        if ($institucion <= 0) {
            $institucion = $this->getInstitucionByUserId($userId);
        }

        $rows = $this->ventaRepo->findPendientesByInstitucion($institucion);

        return array_map(fn($r) => [
            'id'             => (int)   $r['id'],
            'numero'         => $r['numero'],
            'fecha'          => $r['fecha'],
            'total'          => (float) $r['total'],
            'saldo'          => (float) $r['saldo'],
            'tipo'           => $r['tipo'],
            'identificacion' => $r['identificacion'],
            'nombre'         => $r['nombre'],
        ], $rows);
    }

    // -------------------------------------------------------------------------
    // fetchHistorial
    // -------------------------------------------------------------------------

    private function fetchHistorial(array $queryParams): array
    {
        $personaId = $this->resolvePersonaId($queryParams);

        $pagos   = $this->pagoRepo->findByPersona($personaId);
        $ventas  = $this->ventaRepo->findByPersona($personaId);

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

    private function registrarPago(int $userId): array
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

        if ($persona <= 0 || $formaPago <= 0 || $monto <= 0) {
            throw new RuntimeException('Faltan campos obligatorios: persona, forma_pago, monto.', 400);
        }

        if ($institucion <= 0) {
            $institucion = $this->getInstitucionByUserId($userId);
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // Find oldest pending ventas for this persona (FIFO)
            $pendientes = $this->ventaRepo->findPendientesByPersona($persona);

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

                // Update venta saldo via repository
                $this->ventaRepo->updateSaldo($venId, $nuevoSaldo, $nuevoSaldo <= 0);

                if ($nuevoSaldo <= 0) {
                    $ventasSaldadas[] = $venId;
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

            $pagoEntity = $this->pagoRepo->save($pagoEntity);
            $pagoId     = (int) $pagoEntity->getId();

            // Insert PagoDetalle for each venta paid via repository
            foreach ($detalles as $det) {
                $pagoDetalle = new PagoDetalle(null, $pagoId, null, $det['venta_id'], (float) $det['aplicado']);
                $this->pagoDetalleRepo->save($pagoDetalle);
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

    // -------------------------------------------------------------------------
    // fetchFormasPago
    // -------------------------------------------------------------------------

    private function fetchFormasPago(): array
    {
        $formas = $this->formaPagoRepo->findAll();
        return array_map(fn($f) => [
            'id'          => (int) $f->getId(),
            'nombre'      => $f->getNombre(),
            'nemonico'    => $f->getNemonico(),
            'descripcion' => $f->getDescripcion(),
        ], $formas);
    }

    // -------------------------------------------------------------------------
    // fetchDetalle
    // -------------------------------------------------------------------------

    private function fetchDetalle(array $queryParams): array
    {
        $pagoId = (int) ($queryParams['id'] ?? 0);
        if ($pagoId <= 0) {
            throw new RuntimeException('Debe proporcionar el id del pago.', 400);
        }

        $pago = $this->pagoRepo->findById($pagoId);
        if ($pago === null) {
            throw new RuntimeException('Pago no encontrado.', 404);
        }

        $detalles = $this->pagoDetalleRepo->findByPago($pagoId);
        $db       = Database::getInstance();

        $detallesData = array_map(function ($d) use ($db) {
            $ventaNumero = null;
            if ($d->getVenta() !== null) {
                $stmt = $db->prepare('SELECT numero FROM ventas WHERE id = :id');
                $stmt->execute([':id' => $d->getVenta()->getId()]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $ventaNumero = $row ? $row['numero'] : null;
            }
            return [
                'id'             => (int) $d->getId(),
                'venta_id'       => $d->getVenta() !== null ? (int) $d->getVenta()->getId() : null,
                'venta_numero'   => $ventaNumero,
                'cuota_id'       => $d->getCuotaMatricula() !== null ? (int) $d->getCuotaMatricula()->getId() : null,
                'valor'          => (float) $d->getValor(),
            ];
        }, $detalles);

        $formaNombre = null;
        if ($pago->getFormaPago() !== null) {
            $forma = $this->formaPagoRepo->findById((int) $pago->getFormaPago()->getId());
            $formaNombre = $forma ? $forma->getNombre() : null;
        }

        $estadoNombre = match ((int) ($pago->getEstado()?->getId() ?? 1)) {
            1 => 'ACTIVO',
            4 => 'ANULADO',
            default => 'OTRO',
        };

        return [
            'id'          => (int) $pago->getId(),
            'numero'      => $pago->getNumero(),
            'fecha'       => $pago->getFecha(),
            'monto'       => (float) $pago->getMonto(),
            'referencia'  => $pago->getReferencia(),
            'observacion' => $pago->getObservacion(),
            'forma_pago'  => $formaNombre,
            'estado'      => $estadoNombre,
            'estado_id'   => (int) ($pago->getEstado()?->getId() ?? 1),
            'detalles'    => $detallesData,
        ];
    }

    // -------------------------------------------------------------------------
    // anularPago
    // -------------------------------------------------------------------------

    private function anularPago(int $userId): array
    {
        $body = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($body)) {
            throw new RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        $pagoId = (int) ($body['pago_id'] ?? 0);
        if ($pagoId <= 0) {
            throw new RuntimeException('Debe proporcionar pago_id.', 400);
        }

        $pago = $this->pagoRepo->findById($pagoId);
        if ($pago === null) {
            throw new RuntimeException('Pago no encontrado.', 404);
        }

        $estadoId = (int) ($pago->getEstado()?->getId() ?? 0);
        if ($estadoId === 4) {
            throw new RuntimeException('Este pago ya está anulado.', 400);
        }
        if ($estadoId !== 1) {
            throw new RuntimeException('Solo se pueden anular pagos en estado ACTIVO.', 400);
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            $detalles = $this->pagoDetalleRepo->findByPago($pagoId);

            foreach ($detalles as $det) {
                if ($det->getVenta() === null) {
                    continue;
                }
                $ventaId = (int) $det->getVenta()->getId();
                $valor   = (float) $det->getValor();

                // Restore venta saldo
                $stmt = $db->prepare('SELECT saldo, estado FROM ventas WHERE id = :id');
                $stmt->execute([':id' => $ventaId]);
                $ventaRow = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($ventaRow) {
                    $nuevoSaldo = (float) $ventaRow['saldo'] + $valor;
                    // Reactivate venta if it was marked as paid (estado=2)
                    $nuevoEstado = (int) $ventaRow['estado'] === 2 ? 1 : (int) $ventaRow['estado'];
                    $stmt2 = $db->prepare('UPDATE ventas SET saldo = :saldo, estado = :estado WHERE id = :id');
                    $stmt2->execute([':saldo' => $nuevoSaldo, ':estado' => $nuevoEstado, ':id' => $ventaId]);
                }
            }

            // Mark pago as ANULADO (estado 4)
            $pago->setEstado(4);
            $this->pagoRepo->save($pago);

            $db->commit();

            return [
                'numero'  => $pago->getNumero(),
                'mensaje' => 'Pago ' . $pago->getNumero() . ' anulado correctamente. Saldos restaurados en las facturas correspondientes.',
            ];

        } catch (\Throwable $e) {
            $db->rollBack();
            throw new RuntimeException('Error al anular pago: ' . $e->getMessage(), 500);
        }
    }

    private function getInstitucionByUserId(int $userId): int
    {
        $persona = $this->personaRepo->findByUserId($userId);
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
