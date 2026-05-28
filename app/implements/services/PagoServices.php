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
            $method === 'GET'  && $resource === 'recent'         => $this->fetchRecientes($userId, $queryParams),
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

        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT v.id, v.numero, v.fecha, v.total, v.saldo, v.tipo,
                    p.identificacion,
                    TRIM(CONCAT(p.primer_nombre, ' ', p.primer_apellido)) AS nombre,
                    'regular' AS tipo_origen
             FROM ventas v
             JOIN personas p ON p.id = v.persona
             WHERE v.saldo > 0 AND v.estado = 1 AND v.institucion = :inst1
             UNION ALL
             SELECT ve.id, ve.numero, ve.fecha, ve.total, ve.saldo, 'EXTERNA' AS tipo,
                    COALESCE(ve.cliente_identificacion, '') AS identificacion,
                    ve.cliente_nombre AS nombre,
                    'externa' AS tipo_origen
             FROM ventas_externas ve
             WHERE ve.saldo > 0 AND ve.estado = 1 AND ve.institucion = :inst2
             ORDER BY fecha ASC"
        );
        $stmt->execute([':inst1' => $institucion, ':inst2' => $institucion]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return array_map(fn($r) => [
            'id'             => (int)   $r['id'],
            'numero'         => $r['numero'],
            'fecha'          => $r['fecha'],
            'total'          => (float) $r['total'],
            'saldo'          => (float) $r['saldo'],
            'tipo'           => $r['tipo'],
            'identificacion' => $r['identificacion'],
            'nombre'         => $r['nombre'],
            'tipo_origen'    => $r['tipo_origen'],
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
            'observacion' => $p->getObservacion(),
            'estado'     => $p->getEstado() !== null ? $p->getEstado()->getId() : null,
        ], $pagos);

        $db = Database::getInstance();
        $stmtCuotaSig = $db->prepare(
            'SELECT cm.saldo FROM cuotas_matricula cm
             JOIN plan_pago_matricula ppm ON cm.plan_pago = ppm.id
             JOIN ventas v ON ppm.matricula = v.matricula
             WHERE v.id = :vid AND cm.saldo > 0
             ORDER BY cm.fecha_vencimiento ASC LIMIT 1'
        );

        $ventasData = [];
        foreach ($ventas as $v) {
            $cuotaSiguiente = null;
            if ($v->getTipo() === 'MATRICULA' && (float) $v->getSaldo() > 0) {
                $stmtCuotaSig->execute([':vid' => $v->getId()]);
                $cuotaRow = $stmtCuotaSig->fetch(\PDO::FETCH_ASSOC);
                $cuotaSiguiente = $cuotaRow ? (float) $cuotaRow['saldo'] : null;
            }
            $ventasData[] = [
                'id'              => $v->getId(),
                'numero'          => $v->getNumero(),
                'fecha'           => $v->getFecha(),
                'total'           => $v->getTotal(),
                'saldo'           => $v->getSaldo(),
                'tipo'            => $v->getTipo(),
                'estado'          => $v->getEstado() !== null ? $v->getEstado()->getId() : null,
                'cuota_siguiente' => $cuotaSiguiente,
            ];
        }

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

            $stmtPlanPago = $db->prepare(
                'SELECT id FROM plan_pago_matricula WHERE matricula = :mid ORDER BY id ASC LIMIT 1'
            );
            $stmtCuotasPend = $db->prepare(
                'SELECT id, saldo FROM cuotas_matricula
                 WHERE plan_pago = :pid AND saldo > 0
                 ORDER BY fecha_vencimiento ASC, numero ASC'
            );
            $stmtUpdCuota = $db->prepare('UPDATE cuotas_matricula SET saldo = :s WHERE id = :id');

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

                // Actualizar saldos de cuotas si esta venta tiene plan de pago
                $venMatriculaId = isset($ven['matricula']) ? (int) $ven['matricula'] : 0;
                if ($venMatriculaId > 0) {
                    $stmtPlanPago->execute([':mid' => $venMatriculaId]);
                    $planRow = $stmtPlanPago->fetch(\PDO::FETCH_ASSOC);
                    if ($planRow) {
                        $restanteCuotas = $aplicado;
                        $stmtCuotasPend->execute([':pid' => (int) $planRow['id']]);
                        foreach ($stmtCuotasPend->fetchAll(\PDO::FETCH_ASSOC) as $cuota) {
                            if ($restanteCuotas <= 0) break;
                            $cuotaSaldo   = (float) $cuota['saldo'];
                            $aplicarCuota = min($restanteCuotas, $cuotaSaldo);
                            $stmtUpdCuota->execute([
                                ':s'  => round($cuotaSaldo - $aplicarCuota, 2),
                                ':id' => $cuota['id'],
                            ]);
                            $restanteCuotas -= $aplicarCuota;
                        }
                    }
                }
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
                'pago_id'         => $pagoId,
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
            'id'                => (int) $pago->getId(),
            'numero'            => $pago->getNumero(),
            'fecha'             => $pago->getFecha(),
            'monto'             => (float) $pago->getMonto(),
            'referencia'        => $pago->getReferencia(),
            'observacion'       => $pago->getObservacion(),
            'forma_pago'        => $formaNombre,
            'estado'            => $estadoNombre,
            'estado_id'         => (int) ($pago->getEstado()?->getId() ?? 1),
            'motivo_anulacion'  => $pago->getMotivoAnulacion(),
            'detalles'          => $detallesData,
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

        $motivoAnulacion = isset($body['motivo_anulacion'])
            ? substr(trim((string) $body['motivo_anulacion']), 0, 255)
            : null;
        if ($motivoAnulacion === '') {
            $motivoAnulacion = null;
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

            $stmtRestPlan  = $db->prepare('SELECT id FROM plan_pago_matricula WHERE matricula = :mid ORDER BY id ASC LIMIT 1');
            $stmtRestCuota = $db->prepare(
                'SELECT id, saldo, valor FROM cuotas_matricula
                 WHERE plan_pago = :pid AND saldo < valor
                 ORDER BY fecha_vencimiento DESC, numero DESC'
            );
            $stmtRestUpd   = $db->prepare('UPDATE cuotas_matricula SET saldo = :s WHERE id = :id');

            foreach ($detalles as $det) {
                if ($det->getVenta() === null) {
                    continue;
                }
                $ventaId = (int) $det->getVenta()->getId();
                $valor   = (float) $det->getValor();

                // Restore venta saldo
                $stmt = $db->prepare('SELECT saldo, estado, matricula FROM ventas WHERE id = :id');
                $stmt->execute([':id' => $ventaId]);
                $ventaRow = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($ventaRow) {
                    $nuevoSaldo = (float) $ventaRow['saldo'] + $valor;
                    // Reactivate venta if it was marked as paid (estado=2)
                    $nuevoEstado = (int) $ventaRow['estado'] === 2 ? 1 : (int) $ventaRow['estado'];
                    $stmt2 = $db->prepare('UPDATE ventas SET saldo = :saldo, estado = :estado WHERE id = :id');
                    $stmt2->execute([':saldo' => $nuevoSaldo, ':estado' => $nuevoEstado, ':id' => $ventaId]);

                    // Restaurar saldos de cuotas (las más recientes primero)
                    $matriculaId = isset($ventaRow['matricula']) ? (int) $ventaRow['matricula'] : 0;
                    if ($matriculaId > 0) {
                        $stmtRestPlan->execute([':mid' => $matriculaId]);
                        $planRow = $stmtRestPlan->fetch(\PDO::FETCH_ASSOC);
                        if ($planRow) {
                            $aRestaurar = $valor;
                            $stmtRestCuota->execute([':pid' => (int) $planRow['id']]);
                            foreach ($stmtRestCuota->fetchAll(\PDO::FETCH_ASSOC) as $cuota) {
                                if ($aRestaurar <= 0) break;
                                $cuotaSaldo  = (float) $cuota['saldo'];
                                $cuotaValor  = (float) $cuota['valor'];
                                $espacio     = $cuotaValor - $cuotaSaldo;
                                $restaurar   = min($aRestaurar, $espacio);
                                $stmtRestUpd->execute([
                                    ':s'  => round($cuotaSaldo + $restaurar, 2),
                                    ':id' => $cuota['id'],
                                ]);
                                $aRestaurar -= $restaurar;
                            }
                        }
                    }
                }
            }

            // Mark pago as ANULADO (estado 4)
            $pago->setEstado(4);
            $pago->setMotivoAnulacion($motivoAnulacion);
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

    // -------------------------------------------------------------------------
    // fetchRecientes
    // -------------------------------------------------------------------------

    private function fetchRecientes(int $userId, array $queryParams): array
    {
        $institucion = $this->getInstitucionByUserId($userId);
        $db = Database::getInstance();

        $desde = !empty($queryParams['desde']) ? $queryParams['desde'] : null;
        $hasta = !empty($queryParams['hasta']) ? $queryParams['hasta'] : null;
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 20;

        // ── Pagos regulares ──────────────────────────────────────────────────
        $sql = "SELECT p.id, p.numero, p.fecha, CAST(p.monto AS DECIMAL(12,2)) AS monto,
                    p.referencia, p.observacion,
                    p.estado AS estado_id,
                    fp.nombre AS forma_pago,
                    TRIM(CONCAT(
                        COALESCE(per.primer_nombre, ''), ' ',
                        COALESCE(per.segundo_nombre, ''), ' ',
                        COALESCE(per.primer_apellido, ''), ' ',
                        COALESCE(per.segundo_apellido, '')
                    )) AS nombre_cliente,
                    per.identificacion
             FROM pagos p
             JOIN personas per ON per.id = p.persona
             LEFT JOIN formas_pago fp ON fp.id = p.forma_pago
             WHERE p.institucion = :inst";

        if ($desde) $sql .= " AND DATE(p.fecha) >= :desde";
        if ($hasta) $sql .= " AND DATE(p.fecha) <= :hasta";
        $sql .= " ORDER BY p.fecha DESC, p.id DESC";
        if ($limit > 0) $sql .= " LIMIT :limit";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':inst', $institucion, \PDO::PARAM_INT);
        if ($desde) $stmt->bindValue(':desde', $desde);
        if ($hasta) $stmt->bindValue(':hasta', $hasta);
        if ($limit > 0) $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $regular = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // ── Ventas externas cobradas ─────────────────────────────────────────
        $sqlExt = "SELECT ve.id, ve.numero, ve.fecha,
                       CAST(ve.total AS DECIMAL(12,2)) AS monto,
                       ve.referencia, NULL AS observacion,
                       ve.estado AS estado_id,
                       fp.nombre AS forma_pago,
                       ve.cliente_nombre AS nombre_cliente,
                       COALESCE(ve.cliente_identificacion, '') AS identificacion
                   FROM ventas_externas ve
                   LEFT JOIN formas_pago fp ON fp.id = ve.forma_pago
                   WHERE ve.institucion = :inst
                     AND ve.saldo = 0
                     AND ve.estado = 1
                     AND ve.forma_pago IS NOT NULL";

        if ($desde) $sqlExt .= " AND DATE(ve.fecha) >= :desde";
        if ($hasta) $sqlExt .= " AND DATE(ve.fecha) <= :hasta";
        $sqlExt .= " ORDER BY ve.fecha DESC, ve.id DESC";
        if ($limit > 0) $sqlExt .= " LIMIT :limit";

        $stmtExt = $db->prepare($sqlExt);
        $stmtExt->bindValue(':inst', $institucion, \PDO::PARAM_INT);
        if ($desde) $stmtExt->bindValue(':desde', $desde);
        if ($hasta) $stmtExt->bindValue(':hasta', $hasta);
        if ($limit > 0) $stmtExt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmtExt->execute();
        $externas = $stmtExt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // ── Mapear, fusionar, ordenar y limitar ──────────────────────────────
        $map = function (array $r, string $origen): array {
            $estadoId = (int) $r['estado_id'];
            // Normalizar: ventas_externas usa estado=2 para anulado, pagos usa 4
            if ($origen === 'externa' && $estadoId === 2) $estadoId = 4;
            return [
                'id'             => (int)   $r['id'],
                'numero'         => $r['numero'],
                'fecha'          => $r['fecha'],
                'monto'          => (float) $r['monto'],
                'referencia'     => $r['referencia'],
                'observacion'    => $r['observacion'] ?? null,
                'estado_id'      => $estadoId,
                'forma_pago'     => $r['forma_pago'],
                'nombre_cliente' => trim((string) $r['nombre_cliente']),
                'identificacion' => $r['identificacion'],
                'tipo_origen'    => $origen,
            ];
        };

        $merged = array_merge(
            array_map(fn($r) => $map($r, 'regular'), $regular),
            array_map(fn($r) => $map($r, 'externa'), $externas)
        );

        usort($merged, function ($a, $b) {
            $cmp = strcmp($b['fecha'], $a['fecha']);
            return $cmp !== 0 ? $cmp : $b['id'] - $a['id'];
        });

        return $limit > 0 ? array_slice($merged, 0, $limit) : $merged;
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