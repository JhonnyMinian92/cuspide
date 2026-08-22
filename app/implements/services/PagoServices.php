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
require_once __DIR__ . '/traits/TAuthenticable.php';

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
use Services\Traits\TAuthenticable;

final class PagoServices implements PagoServiceInterface
{
    use TAuthenticable;

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
            $method === 'GET'  && $resource === 'history'        => $this->fetchHistorial($queryParams, $userId),
            $method === 'GET'  && $resource === 'formas'         => $this->fetchFormasPago(),
            $method === 'GET'  && $resource === 'detail'         => $this->fetchDetalle($queryParams, $userId),
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
        $institucion = $this->getInstitucionByUserId($userId);
        $db = Database::getInstance();

        // 1. Ventas no-MATRICULA (cada venta = 1 cuota en la vista pendientes)
        $stmt1 = $db->prepare(
            "SELECT v.id, v.numero, v.fecha, v.total, v.saldo, v.tipo,
                    p.identificacion,
                    TRIM(CONCAT(p.primer_nombre, ' ', p.primer_apellido)) AS nombre,
                    'regular' AS tipo_origen,
                    CASE
                      WHEN v.tipo = 'MENSUALIDAD' THEN DATE(DATE_ADD(v.fecha, INTERVAL 1 MONTH))
                      ELSE DATE(DATE_ADD(v.fecha, INTERVAL 30 DAY))
                    END AS fecha_vencimiento
             FROM ventas v
             JOIN personas p ON p.id = v.persona
             WHERE v.saldo > 0 AND v.estado = 1 AND v.institucion = :inst
               AND v.tipo != 'MATRICULA'"
        );
        $stmt1->execute([':inst' => $institucion]);
        $rows1 = $stmt1->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 2. MATRICULA: expandir en una fila por cuota pendiente del plan de pago
        $stmt2 = $db->prepare(
            "SELECT v.id, v.numero, v.fecha,
                    cm.valor AS total, cm.saldo AS saldo,
                    'MATRICULA' AS tipo,
                    p.identificacion,
                    TRIM(CONCAT(p.primer_nombre, ' ', p.primer_apellido)) AS nombre,
                    'regular' AS tipo_origen,
                    cm.fecha_vencimiento AS fecha_vencimiento
             FROM ventas v
             JOIN plan_pago_matricula ppm ON ppm.matricula = v.matricula
             JOIN cuotas_matricula cm     ON cm.plan_pago  = ppm.id
             JOIN personas p              ON p.id          = v.persona
             WHERE cm.saldo > 0
               AND v.estado IN (1, 2)
               AND v.institucion = :inst"
        );
        $stmt2->execute([':inst' => $institucion]);
        $rows2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 3. Matrículas sin plan de pago (fallback: mostrar como fila única)
        $stmt3 = $db->prepare(
            "SELECT v.id, v.numero, v.fecha, v.total, v.saldo, v.tipo,
                    p.identificacion,
                    TRIM(CONCAT(p.primer_nombre, ' ', p.primer_apellido)) AS nombre,
                    'regular' AS tipo_origen,
                    DATE(DATE_ADD(v.fecha, INTERVAL 30 DAY)) AS fecha_vencimiento
             FROM ventas v
             JOIN personas p ON p.id = v.persona
             WHERE v.saldo > 0 AND v.estado = 1 AND v.institucion = :inst
               AND v.tipo = 'MATRICULA'
               AND NOT EXISTS (
                 SELECT 1 FROM plan_pago_matricula ppm
                 WHERE ppm.matricula = v.matricula
               )"
        );
        $stmt3->execute([':inst' => $institucion]);
        $rows3 = $stmt3->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 4. Ventas externas
        $stmt4 = $db->prepare(
            "SELECT ve.id, ve.numero, ve.fecha, ve.total, ve.saldo, 'EXTERNA' AS tipo,
                    COALESCE(ve.cliente_identificacion, '') AS identificacion,
                    ve.cliente_nombre AS nombre,
                    'externa' AS tipo_origen,
                    DATE(DATE_ADD(ve.fecha, INTERVAL 30 DAY)) AS fecha_vencimiento
             FROM ventas_externas ve
             WHERE ve.saldo > 0 AND ve.estado = 1 AND ve.institucion = :inst"
        );
        $stmt4->execute([':inst' => $institucion]);
        $rows4 = $stmt4->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $all = array_merge($rows1, $rows2, $rows3, $rows4);
        usort($all, fn($a, $b) => strcmp((string)($a['fecha'] ?? ''), (string)($b['fecha'] ?? '')));

        return array_map(fn($r) => [
            'id'               => (int)   $r['id'],
            'numero'           => $r['numero'],
            'fecha'            => $r['fecha'],
            'total'            => (float) $r['total'],
            'saldo'            => (float) $r['saldo'],
            'tipo'             => $r['tipo'],
            'identificacion'   => $r['identificacion'],
            'nombre'           => $r['nombre'],
            'tipo_origen'      => $r['tipo_origen'],
            'fecha_vencimiento'=> $r['fecha_vencimiento'] ?? null,
        ], $all);
    }

    // -------------------------------------------------------------------------
    // fetchHistorial
    // -------------------------------------------------------------------------

    private function fetchHistorial(array $queryParams, int $userId = 0): array
    {
        $personaId = $this->resolvePersonaId($queryParams);

        if ($userId > 0) {
            $instId = $this->getInstitucionByUserId($userId);
            $db     = Database::getInstance();
            $chk    = $db->prepare(
                'SELECT COUNT(*) FROM persona_institucion WHERE id_persona = :p AND id_institucion = :i AND estado = 1'
            );
            $chk->execute([':p' => $personaId, ':i' => $instId]);
            if ((int) $chk->fetchColumn() === 0) {
                throw new RuntimeException('La persona solicitada no pertenece a tu institución.', 403);
            }
        }

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
            'SELECT cm.saldo, cm.fecha_vencimiento FROM cuotas_matricula cm
             JOIN plan_pago_matricula ppm ON cm.plan_pago = ppm.id
             JOIN ventas v ON ppm.matricula = v.matricula
             WHERE v.id = :vid AND cm.saldo > 0
             ORDER BY cm.fecha_vencimiento ASC LIMIT 1'
        );
        $stmtSumCuotas = $db->prepare(
            'SELECT COALESCE(SUM(cm.saldo), 0) AS total_pendiente, COUNT(cm.id) AS cuotas_count
             FROM cuotas_matricula cm
             JOIN plan_pago_matricula ppm ON cm.plan_pago = ppm.id
             JOIN ventas v ON ppm.matricula = v.matricula
             WHERE v.id = :vid AND cm.saldo > 0'
        );

        $ventasData = [];
        foreach ($ventas as $v) {
            $cuotaSiguiente   = null;
            $fechaVencimiento = null;
            $cuotasPendCount  = null;
            $tipo             = $v->getTipo();
            $fecha            = $v->getFecha();
            $saldoVenta       = (float) $v->getSaldo();
            $totalVenta       = $v->getTotal();

            if ($tipo === 'MATRICULA') {
                $stmtCuotaSig->execute([':vid' => $v->getId()]);
                $cuotaRow       = $stmtCuotaSig->fetch(\PDO::FETCH_ASSOC);
                $cuotaSiguiente = $cuotaRow ? (float) $cuotaRow['saldo'] : null;
                $fechaVencimiento = $cuotaRow['fecha_vencimiento'] ?? null;
                // Siempre que haya cuotas pendientes, obtener conteo y suma para mostrar al usuario
                if ($cuotaSiguiente !== null) {
                    $stmtSumCuotas->execute([':vid' => $v->getId()]);
                    $sumRow          = $stmtSumCuotas->fetch(\PDO::FETCH_ASSOC);
                    $sumPending      = (float) ($sumRow['total_pendiente'] ?? 0);
                    $cuotasPendCount = (int)   ($sumRow['cuotas_count']   ?? 0);
                    // Cobro anticipado: si la venta está saldada pero el plan tiene cuotas pendientes,
                    // exponer la suma de cuotas como saldo anticipable para que aparezca en el formulario
                    if ($saldoVenta <= 0 && $sumPending > 0) {
                        $saldoVenta = $sumPending;
                        $totalVenta = $sumPending;
                    }
                }
            } elseif ($tipo === 'MENSUALIDAD') {
                $fechaVencimiento = date('Y-m-d', strtotime($fecha . ' +1 month'));
            } else {
                $fechaVencimiento = date('Y-m-d', strtotime($fecha . ' +30 days'));
            }

            $ventasData[] = [
                'id'               => $v->getId(),
                'numero'           => $v->getNumero(),
                'fecha'            => $fecha,
                'total'            => $totalVenta,
                'saldo'            => $saldoVenta,
                'tipo'             => $tipo,
                'estado'           => $v->getEstado() !== null ? $v->getEstado()->getId() : null,
                'cuota_siguiente'  => $cuotaSiguiente,
                'cuotas_pendientes'=> $cuotasPendCount,
                'fecha_vencimiento'=> $fechaVencimiento,
            ];
        }

        // Obtener la FK matricula para cada venta MATRICULA (para agrupar mensualidades en frontend)
        $stmtFKMap = $db->prepare(
            "SELECT id, matricula FROM ventas WHERE persona = :pid AND tipo = 'MATRICULA'"
        );
        $stmtFKMap->execute([':pid' => $personaId]);
        $ventaMatriculaFKMap = [];
        foreach ($stmtFKMap->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $ventaMatriculaFKMap[(int) $row['id']] = $row['matricula'] !== null ? (int) $row['matricula'] : null;
        }
        foreach ($ventasData as &$vd) {
            $vd['matricula_id'] = ($vd['tipo'] === 'MATRICULA') ? ($ventaMatriculaFKMap[$vd['id']] ?? null) : null;
        }
        unset($vd);

        // Obtener TODAS las mensualidades del alumno (pagadas + pendientes) para el desglose por matrícula
        $stmtAllMens = $db->prepare(
            "SELECT v.id, v.numero, v.fecha, v.total, v.saldo, v.matricula AS matricula_id
             FROM ventas v
             WHERE v.persona = :pid AND v.tipo = 'MENSUALIDAD' AND v.estado IN (1, 2)
             ORDER BY v.matricula ASC, v.fecha ASC"
        );
        $stmtAllMens->execute([':pid' => $personaId]);
        $mensualidadesPorMatricula = [];
        foreach ($stmtAllMens->fetchAll(\PDO::FETCH_ASSOC) ?: [] as $m) {
            $key = $m['matricula_id'] !== null ? (int) $m['matricula_id'] : 0;
            $mensualidadesPorMatricula[$key][] = [
                'id'     => (int)   $m['id'],
                'numero' => $m['numero'],
                'fecha'  => $m['fecha'] ? substr($m['fecha'], 0, 10) : null,
                'total'  => (float) $m['total'],
                'saldo'  => (float) $m['saldo'],
                'pagada' => (float) $m['saldo'] <= 0.005,
            ];
        }

        return [
            'persona_id'                  => $personaId,
            'pagos'                       => $pagosData,
            'facturas'                    => $ventasData,
            'mensualidades_por_matricula' => $mensualidadesPorMatricula,
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

        $institucion = $this->getInstitucionByUserId($userId);
        $personaId   = (int) ($body['persona'] ?? 0);

        // --- VALIDACIÓN DE SEGURIDAD ---
        // Asegurar que la persona a la que se le registra el pago pertenece a la institución del usuario.
        $dbCheck = Database::getInstance();
        $stmtCheck = $dbCheck->prepare('SELECT COUNT(*) FROM persona_institucion WHERE id_persona = :pid AND id_institucion = :iid AND estado = 1');
        $stmtCheck->execute([':pid' => $personaId, ':iid' => $institucion]);
        if ((int) $stmtCheck->fetchColumn() === 0) {
            throw new RuntimeException('La persona especificada no pertenece a su institución.', 403);
        }

        $persona     = (int)    ($body['persona']     ?? 0);
        $ventaId     = isset($body['venta'])      ? (int) $body['venta']       : null;
        $matriculaId = isset($body['matricula'])   ? (int) $body['matricula']   : null;
        $formaPago   = (int)    ($body['forma_pago']  ?? 0);
        $monto       = (float)  ($body['monto']        ?? 0);
        $referencia  = (string) ($body['referencia']   ?? '');
        $observacion = (string) ($body['observacion']  ?? '');

        if ($persona <= 0 || $formaPago <= 0 || $monto <= 0) {
            throw new RuntimeException('Faltan campos obligatorios: persona, forma_pago, monto.', 400);
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

            // Cobro anticipado de cuotas de matrícula: si queda monto y hay cuotas del plan sin venta generada
            if ($montoRestante > 0) {
                $stmtMatAnticipo = $db->prepare(
                    "SELECT DISTINCT v.id AS venta_id, v.matricula AS matricula_id
                     FROM ventas v
                     JOIN plan_pago_matricula ppm ON ppm.matricula = v.matricula
                     JOIN cuotas_matricula cm ON cm.plan_pago = ppm.id
                     WHERE v.persona = :persona AND v.tipo = 'MATRICULA' AND cm.saldo > 0
                     ORDER BY v.fecha ASC"
                );
                $stmtMatAnticipo->execute([':persona' => $persona]);
                foreach ($stmtMatAnticipo->fetchAll(\PDO::FETCH_ASSOC) ?: [] as $mat) {
                    if ($montoRestante <= 0) break;
                    $matId = (int) $mat['matricula_id'];
                    $stmtPlanPago->execute([':mid' => $matId]);
                    $planRow = $stmtPlanPago->fetch(\PDO::FETCH_ASSOC);
                    if (!$planRow) continue;
                    $restanteCuotas = $montoRestante;
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
                        $detalles[]     = ['venta_id' => (int) $mat['venta_id'], 'aplicado' => $aplicarCuota];
                    }
                    $montoRestante = $restanteCuotas;
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

    private function fetchDetalle(array $queryParams, int $userId): array
    {
        $userInstitucionId = $this->getInstitucionByUserId($userId);
        $pagoId = (int) ($queryParams['id'] ?? 0);
        if ($pagoId <= 0) {
            throw new RuntimeException('Debe proporcionar el id del pago.', 400);
        }

        $pago = $this->pagoRepo->findById($pagoId);
        if ($pago === null) {
            throw new RuntimeException('Pago no encontrado.', 404);
        }

        // --- VALIDACIÓN DE SEGURIDAD ---
        if ($pago->getInstitucion()?->getId() !== $userInstitucionId) {
            throw new RuntimeException('Este pago no pertenece a su institución.', 403);
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

        // --- VALIDACIÓN DE SEGURIDAD ---
        $userInstitucionId = $this->getInstitucionByUserId($userId);
        if ($pago->getInstitucion()?->getId() !== $userInstitucionId) {
            throw new RuntimeException('Este pago no pertenece a su institución y no puede ser anulado.', 403);
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
                    per.identificacion,
                    (SELECT GROUP_CONCAT(c.nombre SEPARATOR '; ')
                     FROM clases_matricula cm
                     JOIN clases c ON c.id = cm.clase
                     WHERE cm.matricula = COALESCE(p.matricula, v.matricula)
                       AND cm.estado = 1) AS clases_nombres,
                    (SELECT GROUP_CONCAT(a.nombre SEPARATOR '; ')
                     FROM actividades_matricula am
                     JOIN actividades a ON a.id = am.actividad
                     WHERE am.matricula = COALESCE(p.matricula, v.matricula)
                       AND am.estado = 1) AS actividades_nombres
             FROM pagos p
             JOIN personas per ON per.id = p.persona
             LEFT JOIN formas_pago fp ON fp.id = p.forma_pago
             LEFT JOIN ventas v ON v.id = p.venta
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
                       COALESCE(ve.cliente_identificacion, '') AS identificacion,
                       NULL AS clases_nombres,
                       NULL AS actividades_nombres
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
                'id'                 => (int)   $r['id'],
                'numero'             => $r['numero'],
                'fecha'              => $r['fecha'],
                'monto'              => (float) $r['monto'],
                'referencia'         => $r['referencia'],
                'observacion'        => $r['observacion'] ?? null,
                'estado_id'          => $estadoId,
                'forma_pago'         => $r['forma_pago'],
                'nombre_cliente'     => trim((string) $r['nombre_cliente']),
                'identificacion'     => $r['identificacion'],
                'tipo_origen'        => $origen,
                'clases_nombres'     => $r['clases_nombres']     ?? null,
                'actividades_nombres'=> $r['actividades_nombres'] ?? null,
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
}