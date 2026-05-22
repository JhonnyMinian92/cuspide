<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/MatriculaServiceInterface.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/MatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/ClaseMatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/ActividadMatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/VentaRepository.php';
require_once __DIR__ . '/../persistence/repository/MembresiaRepository.php';
require_once __DIR__ . '/../persistence/repository/PlanPagoMatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/CuotaMatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/PagoRepository.php';
require_once __DIR__ . '/../persistence/repository/PagoDetalleRepository.php';
require_once __DIR__ . '/../persistence/repository/CalendarioRepository.php';
require_once __DIR__ . '/../persistence/repository/ClaseRepository.php';
require_once __DIR__ . '/../persistence/repository/ActividadRepository.php';
require_once __DIR__ . '/../persistence/entitys/Matricula.php';
require_once __DIR__ . '/../persistence/entitys/ClaseMatricula.php';
require_once __DIR__ . '/../persistence/entitys/ActividadMatricula.php';
require_once __DIR__ . '/../persistence/entitys/Venta.php';
require_once __DIR__ . '/../persistence/entitys/PlanPagoMatricula.php';
require_once __DIR__ . '/../persistence/entitys/CuotaMatricula.php';
require_once __DIR__ . '/../persistence/entitys/Pago.php';
require_once __DIR__ . '/../persistence/entitys/PagoDetalle.php';
require_once __DIR__ . '/../persistence/entitys/Calendario.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\MatriculaServiceInterface;
use Repositories\MatriculaRepository;
use Repositories\ClaseMatriculaRepository;
use Repositories\ActividadMatriculaRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Repositories\VentaRepository;
use Repositories\MembresiaRepository;
use Repositories\PlanPagoMatriculaRepository;
use Repositories\CuotaMatriculaRepository;
use Repositories\PagoRepository;
use Repositories\PagoDetalleRepository;
use Repositories\CalendarioRepository;
use Repositories\ClaseRepository;
use Repositories\ActividadRepository;
use Entitys\Matricula;
use Entitys\ClaseMatricula;
use Entitys\ActividadMatricula;
use Entitys\Venta;
use Entitys\PlanPagoMatricula;
use Entitys\CuotaMatricula;
use Entitys\Pago;
use Entitys\PagoDetalle;
use Entitys\Calendario;
use RuntimeException;
use Utils\Token;

final class MatriculaServices implements MatriculaServiceInterface
{
    private string $jwtSecret;
    private MatriculaRepository $matriculaRepo;
    private ClaseMatriculaRepository $claseMatRepo;
    private ActividadMatriculaRepository $actMatRepo;
    private PersonaRepository $personaRepo;

    public function __construct()
    {
        $config = require __DIR__ . '/../../properties/security.php';
        $this->jwtSecret = (string) ($config['jwt_secret'] ?? '');
        if ($this->jwtSecret === '') {
            throw new RuntimeException('No existe configuración de seguridad para JWT.', 500);
        }
        $this->matriculaRepo = new MatriculaRepository();
        $this->claseMatRepo  = new ClaseMatriculaRepository();
        $this->actMatRepo    = new ActividadMatriculaRepository();
        $this->personaRepo   = new PersonaRepository();
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $userId  = (int) (Token::validateBearerToken($authHeader, $this->jwtSecret)['sub'] ?? 0);
        $instId  = $this->getInstitucionByUserId($userId);

        $resource = $this->resolveResource($requestUri, $queryParams, 'matricula');

        return match (true) {
            $method === 'GET'  && $resource === 'students'    => $this->fetchStudents($instId, $queryParams),
            $method === 'GET'  && $resource === 'membresias'  => $this->fetchMembresiasForInst($instId),
            $method === 'GET'  && $resource === 'clases'      => $this->fetchClasesForInst($instId),
            $method === 'GET'  && $resource === 'actividades' => $this->fetchActividadesForInst($instId),
            $method === 'GET'  && $resource === 'list'        => $this->fetchMatriculas($queryParams),
            $method === 'POST' && $resource === 'create'     => $this->createMatricula($instId),
            $method === 'PUT'  && $resource === 'update'     => $this->updateMatricula($instId, (int) ($queryParams['id'] ?? 0)),
            $method === 'PUT'  && $resource === 'cancel'     => $this->cancelMatricula((int) ($queryParams['id'] ?? 0)),
            $method === 'PUT'  && $resource === 'removeitem' => $this->removeItem((int) ($queryParams['id'] ?? 0), (string) ($queryParams['tipo'] ?? '')),
            default => throw new RuntimeException('Ruta de API no soportada: ' . $method . ' ' . $resource, 404),
        };
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

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

    private function resolveResource(string $requestUri, array $queryParams, string $controllerSegment): string
    {
        $tipo = strtolower(trim((string) ($queryParams['api'] ?? '')));
        if ($tipo !== '') return $tipo;

        $path     = parse_url($requestUri, PHP_URL_PATH) ?? '';
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        $idx = array_search($controllerSegment, $segments, true);
        if ($idx !== false) {
            $candidate = $segments[$idx + 1] ?? null;
            if ($candidate !== null && strtolower($candidate) !== 'index.php') return strtolower($candidate);
        }

        if ($segments !== []) {
            $last = strtolower((string) $segments[array_key_last($segments)]);
            if ($last !== 'index.php') return $last;
        }

        return '';
    }

    // -------------------------------------------------------------------------
    // fetchStudents — institution-filtered, TIPO_EST only
    // -------------------------------------------------------------------------

    private function fetchStudents(int $instId, array $queryParams): array
    {
        $q = trim((string) ($queryParams['q'] ?? ''));
        if ($q !== '' && mb_strlen($q) >= 2) {
            return $this->personaRepo->findByQueryAndInstitucion($q, $instId, 'TIPO_EST', 50);
        }
        return $this->personaRepo->findStudentsByInstitucion($instId, 50);
    }

    // -------------------------------------------------------------------------
    // fetchMembresiasForInst
    // -------------------------------------------------------------------------

    private function fetchMembresiasForInst(int $instId): array
    {
        $entities = (new MembresiaRepository())->findByInstitucion($instId);
        return array_map(fn($m) => [
            'id'          => $m->getId(),
            'titulo'      => $m->getTitulo(),
            'precio'      => (float) $m->getPrecio(),
            'porcentaje'  => (float) $m->getPorcentaje(),
            'descripcion' => $m->getDescripcion(),
            'periodo'     => $m->getPeriodo(),
            'fecha_fin'   => $m->getFechaFin(),
        ], $entities);
    }

    // -------------------------------------------------------------------------
    // fetchClasesForInst
    // -------------------------------------------------------------------------

    private function fetchClasesForInst(int $instId): array
    {
        return (new ClaseRepository())->findByInstitucionWithStats($instId);
    }

    // -------------------------------------------------------------------------
    // fetchActividadesForInst
    // -------------------------------------------------------------------------

    private function fetchActividadesForInst(int $instId): array
    {
        return (new ActividadRepository())->findByInstitucionWithStats($instId);
    }

    // -------------------------------------------------------------------------
    // fetchMatriculas
    // -------------------------------------------------------------------------

    private function fetchMatriculas(array $queryParams): array
    {
        $personaId = null;

        if (!empty($queryParams['persona_id'])) {
            $personaId = (int) $queryParams['persona_id'];
        } elseif (!empty($queryParams['identificacion'])) {
            $persona = $this->personaRepo->findByIdentificacion((string) $queryParams['identificacion']);
            if ($persona === null) {
                throw new RuntimeException('No se encontró persona con esa identificación.', 404);
            }
            $personaId = (int) $persona->getId();
        }

        if ($personaId === null || $personaId <= 0) {
            throw new RuntimeException('Debe proporcionar persona_id o identificacion.', 400);
        }

        $db         = \Core\Database::getInstance();
        $matriculas = $this->matriculaRepo->findByPersonaId($personaId);
        $planRepo   = new PlanPagoMatriculaRepository();
        $cuotaRepo  = new CuotaMatriculaRepository();

        $result = [];
        foreach ($matriculas as $mat) {
            $matriculaId = (int) $mat['id'];

            // Clases con nombres (JOIN directo para evitar N+1 de entidades)
            $stmtC = $db->prepare(
                'SELECT cm.id, cm.clase, c.nombre AS clase_nombre, cm.horario, cm.fecha_inicio, cm.fecha_fin, cm.estado
                 FROM clases_matricula cm
                 LEFT JOIN clases c ON c.id = cm.clase
                 WHERE cm.matricula = :mid ORDER BY cm.id ASC'
            );
            $stmtC->execute([':mid' => $matriculaId]);
            $clases = array_map(fn($row) => [
                'id'           => (int) $row['id'],
                'clase'        => (int) $row['clase'],
                'clase_nombre' => $row['clase_nombre'] ?? '',
                'horario'      => $row['horario'] !== null ? (int) $row['horario'] : null,
                'fecha_inicio' => $row['fecha_inicio'],
                'fecha_fin'    => $row['fecha_fin'],
                'estado'       => (int) $row['estado'],
            ], $stmtC->fetchAll(\PDO::FETCH_ASSOC) ?: []);

            // Actividades con nombres
            $stmtA = $db->prepare(
                'SELECT am.id, am.actividad, a.nombre AS actividad_nombre, am.horario, am.fecha_inicio, am.fecha_fin, am.estado
                 FROM actividades_matricula am
                 LEFT JOIN actividades a ON a.id = am.actividad
                 WHERE am.matricula = :mid ORDER BY am.id ASC'
            );
            $stmtA->execute([':mid' => $matriculaId]);
            $actividades = array_map(fn($row) => [
                'id'               => (int) $row['id'],
                'actividad'        => (int) $row['actividad'],
                'actividad_nombre' => $row['actividad_nombre'] ?? '',
                'horario'          => $row['horario'] !== null ? (int) $row['horario'] : null,
                'fecha_inicio'     => $row['fecha_inicio'],
                'fecha_fin'        => $row['fecha_fin'],
                'estado'           => (int) $row['estado'],
            ], $stmtA->fetchAll(\PDO::FETCH_ASSOC) ?: []);

            // Plan de pago
            $planPago = null;
            $planes   = $planRepo->findByMatricula($matriculaId);
            if (!empty($planes)) {
                $plan     = $planes[0];
                $cuotas   = $cuotaRepo->findByPlanPago((int) $plan->getId());
                $planPago = [
                    'id'               => $plan->getId(),
                    'subtotal'         => $plan->getSubtotal(),
                    'descuento'        => $plan->getDescuento(),
                    'total'            => $plan->getTotal(),
                    'saldo'            => $plan->getSaldo(),
                    'fecha_generacion' => $plan->getFechaGeneracion(),
                    'cuotas'           => array_map(fn($c) => [
                        'id'                => $c->getId(),
                        'numero'            => $c->getNumero(),
                        'fecha_vencimiento' => $c->getFechaVencimiento(),
                        'valor'             => $c->getValor(),
                        'saldo'             => $c->getSaldo(),
                        'pagada'            => $c->getSaldo() <= 0,
                    ], $cuotas),
                ];
            }

            $result[] = [
                'id'             => $matriculaId,
                'fecha_registro' => $mat['fecha_registro'],
                'fecha_inicio'   => $mat['fecha_inicio'],
                'fecha_fin'      => $mat['fecha_fin'],
                'membresia'      => $mat['membresia'] ?? '—',
                'membresia_id'   => $mat['membresia_id'] ?? null,
                'institucion'    => $mat['institucion'],
                'estado'         => $mat['estado'],
                'clases'         => $clases,
                'actividades'    => $actividades,
                'plan_pago'      => $planPago,
            ];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // createMatricula — creates all related financial/scheduling records
    // -------------------------------------------------------------------------

    private function createMatricula(int $instId): array
    {
        $body = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($body)) {
            throw new RuntimeException('Cuerpo de la solicitud inválido o vacío.', 400);
        }

        $persona     = (int) ($body['persona']      ?? 0);
        $fechaInicio = (string) ($body['fecha_inicio'] ?? '');
        $fechaFin    = (string) ($body['fecha_fin']    ?? '');
        $membresiaId = (isset($body['membresia']) && $body['membresia'] !== '' && $body['membresia'] !== null)
            ? (int) $body['membresia'] : null;
        $clases      = is_array($body['clases']      ?? null) ? $body['clases']      : [];
        $actividades = is_array($body['actividades'] ?? null) ? $body['actividades'] : [];

        if ($persona <= 0 || $fechaInicio === '' || $fechaFin === '') {
            throw new RuntimeException('Faltan campos obligatorios: persona, fecha_inicio, fecha_fin.', 400);
        }

        $db = \Core\Database::getInstance();

        try {
            $db->beginTransaction();

            // 1. Matricula
            $fechaRegistro   = date('Y-m-d H:i:s');
            $matriculaEntity = new Matricula(null, $fechaRegistro, $persona, $fechaInicio, $fechaFin, $membresiaId, $instId, 1);
            $matriculaEntity = $this->matriculaRepo->save($matriculaEntity);
            $matriculaId     = (int) $matriculaEntity->getId();

            $subtotal   = 0.0;
            $calendRepo = new CalendarioRepository();

            // 2. Clases + Calendarios
            foreach ($clases as $claseItem) {
                $claseId      = (int) ($claseItem['clase_id']    ?? 0);
                $modalidad    = (int) ($claseItem['modalidad']   ?? 1);
                $horario      = isset($claseItem['horario'])     ? (int) $claseItem['horario'] : null;
                $cFechaInicio = (string) ($claseItem['fecha_inicio'] ?? $fechaInicio);
                $cFechaFin    = (string) ($claseItem['fecha_fin']    ?? $fechaFin);
                if ($claseId <= 0) continue;

                $claseMatEntity = new ClaseMatricula(null, $claseId, $matriculaId, $modalidad, $cFechaInicio, $cFechaFin, 1, $horario);
                $this->claseMatRepo->save($claseMatEntity);

                $stmtC = $db->prepare('SELECT nombre, precio, cupo_maximo FROM clases WHERE id = :id LIMIT 1');
                $stmtC->execute([':id' => $claseId]);
                $claseRow  = $stmtC->fetch(\PDO::FETCH_ASSOC);
                $subtotal += $claseRow ? (float) ($claseRow['precio'] ?? 0) : 0.0;
                $cupoMax   = $claseRow ? (int) ($claseRow['cupo_maximo'] ?? 0) : 0;
                $nombreC   = $claseRow ? (string) ($claseRow['nombre'] ?? 'Clase') : 'Clase';

                $cal = new Calendario(null, 'Clase: ' . $nombreC, 'CLASE', $instId, $horario, $claseId, null, null, null, $cFechaInicio, $cFechaFin, '', $cupoMax, 'Matrícula #' . $matriculaId, 1);
                $calendRepo->save($cal);
            }

            // 3. Actividades + Calendarios
            foreach ($actividades as $actItem) {
                $actividadId  = (int) ($actItem['actividad_id'] ?? 0);
                $modalidad    = (int) ($actItem['modalidad']   ?? 1);
                $horario      = isset($actItem['horario'])     ? (int) $actItem['horario'] : null;
                $aFechaInicio = (string) ($actItem['fecha_inicio'] ?? $fechaInicio);
                $aFechaFin    = (string) ($actItem['fecha_fin']    ?? $fechaFin);
                if ($actividadId <= 0) continue;

                $actMatEntity = new ActividadMatricula(null, $actividadId, $matriculaId, $modalidad, $aFechaInicio, $aFechaFin, 1, $horario);
                $this->actMatRepo->save($actMatEntity);

                $stmtA = $db->prepare('SELECT nombre, precio FROM actividades WHERE id = :id LIMIT 1');
                $stmtA->execute([':id' => $actividadId]);
                $actRow    = $stmtA->fetch(\PDO::FETCH_ASSOC);
                $subtotal += $actRow ? (float) ($actRow['precio'] ?? 0) : 0.0;
                $nombreA   = $actRow ? (string) ($actRow['nombre'] ?? 'Actividad') : 'Actividad';

                $cal = new Calendario(null, 'Actividad: ' . $nombreA, 'ACTIVIDAD', $instId, $horario, null, $actividadId, null, null, $aFechaInicio, $aFechaFin, '', 0, 'Matrícula #' . $matriculaId, 1);
                $calendRepo->save($cal);
            }

            // 4. Descuento desde membresía: porcentaje aplicado al subtotal de clases/actividades
            $descuento = 0.0;
            $periodo   = 'mensual'; // por defecto: cuotas mensuales sin membresía
            if ($membresiaId !== null) {
                $mem = (new MembresiaRepository())->findById($membresiaId);
                if ($mem) {
                    $descuento = round($subtotal * ((float) $mem->getPorcentaje() / 100), 2);
                    $periodo   = $mem->getPeriodo();
                }
            }

            // 5. Totals y Venta
            $impuesto      = 0.0;
            $total         = max(0.0, $subtotal - $descuento + $impuesto);
            $numeroFactura = 'FAC-' . date('Ymd') . '-' . str_pad((string) $matriculaId, 6, '0', STR_PAD_LEFT);
            $ventaEntity   = new Venta(null, $numeroFactura, date('Y-m-d H:i:s'), $persona, $matriculaId, $instId, 'MATRICULA', $subtotal, $descuento, $impuesto, $total, $total, 1);
            $ventaRepo     = new VentaRepository();
            $ventaRepo->save($ventaEntity);

            // 6. Plan de pago + cuotas (SIEMPRE, mensual por defecto; periodo de membresía si la hay)
            $cuotasData = [];
            if ($total > 0) {
                $periodicidadId = $this->resolvePeriodicidadId($db, $periodo);
                $plan     = new PlanPagoMatricula(null, $matriculaId, $periodicidadId, $subtotal, $descuento, $total, $total, date('Y-m-d'), 1);
                $planRepo = new PlanPagoMatriculaRepository();
                $planRepo->save($plan);
                $planId = (int) $plan->getId();

                // Cuotas por cada período dentro del rango de la matrícula
                $startDt    = new \DateTime($fechaInicio);
                $endDt      = new \DateTime($fechaFin);
                $interval   = new \DateInterval($this->periodoToInterval($periodo));
                $datePeriod = new \DatePeriod($startDt, $interval, $endDt);
                $numCuotas  = max(1, iterator_count($datePeriod));
                $valorBase  = round($total, 2);
                $cuotaRepo  = new CuotaMatriculaRepository();
                $currentDue = clone $startDt;

                for ($i = 0; $i < $numCuotas; $i++) {
                    $currentDue->add($interval);
                    $isLast  = ($i === $numCuotas - 1);
                    $valorC  = $valorBase;
                    $dueDate = $currentDue->format('Y-m-d');
                    $cuota   = new CuotaMatricula(null, $planId, $i + 1, $dueDate, $valorC, $valorC, 1);
                    $cuotaRepo->save($cuota);
                    $cuotasData[] = [
                        'id'                => (int) $cuota->getId(),
                        'numero'            => $i + 1,
                        'fecha_vencimiento' => $dueDate,
                        'valor'             => $valorC,
                    ];
                }
            }

            $db->commit();

            return [
                'matricula_id'    => $matriculaId,
                'numero_factura'  => $numeroFactura,
                'subtotal'        => $subtotal,
                'descuento'       => $descuento,
                'impuesto'        => $impuesto,
                'total'           => $total,
                'num_clases'      => count(array_filter($clases, fn($c) => (int)($c['clase_id'] ?? 0) > 0)),
                'num_actividades' => count(array_filter($actividades, fn($a) => (int)($a['actividad_id'] ?? 0) > 0)),
                'cuotas'          => $cuotasData,
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new RuntimeException('Error al crear matrícula: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers para createMatricula
    // -------------------------------------------------------------------------

    private function resolvePeriodicidadId(\PDO $db, string $periodo): int
    {
        $like = '%' . strtolower($periodo) . '%';
        $stmt = $db->prepare('SELECT id FROM periodicidad_pago WHERE LOWER(nemonico) LIKE :q OR LOWER(nombre) LIKE :q2 ORDER BY id ASC LIMIT 1');
        $stmt->execute([':q' => $like, ':q2' => $like]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) return (int) $row['id'];

        $stmt2 = $db->query('SELECT id FROM periodicidad_pago ORDER BY id ASC LIMIT 1');
        $row2  = $stmt2 ? $stmt2->fetch(\PDO::FETCH_ASSOC) : null;
        return $row2 ? (int) $row2['id'] : 1;
    }

    private function periodoToInterval(string $periodo): string
    {
        return match ($periodo) {
            'semanal'    => 'P1W',
            'trimestral' => 'P3M',
            'anual'      => 'P1Y',
            default      => 'P1M',
        };
    }

    private function getDefaultFormaPago(\PDO $db): int
    {
        $stmt = $db->query('SELECT id FROM formas_pago ORDER BY id ASC LIMIT 1');
        $row  = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : null;
        return $row ? (int) $row['id'] : 1;
    }

    // -------------------------------------------------------------------------
    // updateMatricula — edita membresía/fecha_fin y agrega ítems nuevos
    // -------------------------------------------------------------------------

    private function updateMatricula(int $instId, int $id): array
    {
        if ($id <= 0) {
            throw new RuntimeException('ID de matrícula inválido.', 400);
        }

        $existing = $this->matriculaRepo->findById($id);
        if ($existing === null) {
            throw new RuntimeException('Matrícula no encontrada.', 404);
        }

        $body = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($body)) {
            throw new RuntimeException('Cuerpo de la solicitud inválido o vacío.', 400);
        }

        $fechaFin    = (string) ($body['fecha_fin']  ?? $existing->getFechaFin());
        $membresiaId = (isset($body['membresia']) && $body['membresia'] !== '' && $body['membresia'] !== null)
            ? (int) $body['membresia'] : null;
        $clases      = is_array($body['clases']      ?? null) ? $body['clases']      : [];
        $actividades = is_array($body['actividades'] ?? null) ? $body['actividades'] : [];

        $fechaInicio = $existing->getFechaInicio();

        $db = \Core\Database::getInstance();

        try {
            $db->beginTransaction();

            // Actualizar matricula
            $stmtU = $db->prepare('UPDATE matricula SET membresia = :mem, fecha_fin = :fin WHERE id = :id');
            $stmtU->execute([':mem' => $membresiaId, ':fin' => $fechaFin, ':id' => $id]);

            $calendRepo   = new CalendarioRepository();
            $clasesAdded  = 0;
            $actsAdded    = 0;

            // Agregar clases nuevas (omite si ya existe activa)
            foreach ($clases as $claseItem) {
                $claseId = (int) ($claseItem['clase_id'] ?? 0);
                if ($claseId <= 0) continue;

                $chk = $db->prepare('SELECT id FROM clases_matricula WHERE clase = :c AND matricula = :m AND estado = 1 LIMIT 1');
                $chk->execute([':c' => $claseId, ':m' => $id]);
                if ($chk->fetch()) continue;

                $claseMatEntity = new ClaseMatricula(null, $claseId, $id, 1, $fechaInicio, $fechaFin, 1, null);
                $this->claseMatRepo->save($claseMatEntity);

                $stmtC = $db->prepare('SELECT nombre, cupo_maximo FROM clases WHERE id = :id LIMIT 1');
                $stmtC->execute([':id' => $claseId]);
                $claseRow = $stmtC->fetch(\PDO::FETCH_ASSOC);
                $nombreC  = $claseRow ? (string) ($claseRow['nombre'] ?? 'Clase') : 'Clase';
                $cupoMax  = $claseRow ? (int) ($claseRow['cupo_maximo'] ?? 0) : 0;

                $calendRepo->save(new Calendario(null, 'Clase: ' . $nombreC, 'CLASE', $instId, null, $claseId, null, null, null, $fechaInicio, $fechaFin, '', $cupoMax, 'Matrícula #' . $id . ' (edición)', 1));
                $clasesAdded++;
            }

            // Agregar actividades nuevas
            foreach ($actividades as $actItem) {
                $actividadId = (int) ($actItem['actividad_id'] ?? 0);
                if ($actividadId <= 0) continue;

                $chk = $db->prepare('SELECT id FROM actividades_matricula WHERE actividad = :a AND matricula = :m AND estado = 1 LIMIT 1');
                $chk->execute([':a' => $actividadId, ':m' => $id]);
                if ($chk->fetch()) continue;

                $actMatEntity = new ActividadMatricula(null, $actividadId, $id, 1, $fechaInicio, $fechaFin, 1, null);
                $this->actMatRepo->save($actMatEntity);

                $stmtA = $db->prepare('SELECT nombre FROM actividades WHERE id = :id LIMIT 1');
                $stmtA->execute([':id' => $actividadId]);
                $actRow  = $stmtA->fetch(\PDO::FETCH_ASSOC);
                $nombreA = $actRow ? (string) ($actRow['nombre'] ?? 'Actividad') : 'Actividad';

                $calendRepo->save(new Calendario(null, 'Actividad: ' . $nombreA, 'ACTIVIDAD', $instId, null, null, $actividadId, null, null, $fechaInicio, $fechaFin, '', 0, 'Matrícula #' . $id . ' (edición)', 1));
                $actsAdded++;
            }

            $db->commit();

            return [
                'id'                   => $id,
                'clases_agregadas'     => $clasesAdded,
                'actividades_agregadas' => $actsAdded,
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new RuntimeException('Error al actualizar matrícula: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------------------------
    // cancelMatricula
    // -------------------------------------------------------------------------

    private function cancelMatricula(int $id): array
    {
        if ($id <= 0) {
            throw new RuntimeException('ID de matrícula inválido.', 400);
        }

        $db = \Core\Database::getInstance();

        try {
            $db->beginTransaction();

            // 1. Anular matrícula, clases y actividades
            $this->matriculaRepo->disable($id);
            $this->claseMatRepo->cancelByMatriculaId($id);
            $this->actMatRepo->cancelByMatriculaId($id);

            // 2. Anular cuotas no pagadas del plan de pago de esta matrícula
            $stmtPlan = $db->prepare(
                'SELECT id FROM plan_pago_matricula WHERE matricula = :mid ORDER BY id ASC LIMIT 1'
            );
            $stmtPlan->execute([':mid' => $id]);
            $planRow = $stmtPlan->fetch(\PDO::FETCH_ASSOC);

            if ($planRow) {
                $planId = (int) $planRow['id'];

                // Anular cuotas pendientes (saldo > 0): estado=2 y saldo=0
                $db->prepare(
                    'UPDATE cuotas_matricula SET estado = 2, saldo = 0 WHERE plan_pago = :pid AND saldo > 0'
                )->execute([':pid' => $planId]);

                // Actualizar saldo del plan a 0 (deuda condonada por anulación)
                $db->prepare(
                    'UPDATE plan_pago_matricula SET saldo = 0 WHERE id = :pid'
                )->execute([':pid' => $planId]);
            }

            // 3. Llevar a 0 el saldo de la venta asociada (ya no se adeuda)
            $db->prepare(
                'UPDATE ventas SET saldo = 0 WHERE matricula = :mid AND saldo > 0'
            )->execute([':mid' => $id]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new RuntimeException('Error al anular matrícula: ' . $e->getMessage(), 500);
        }

        return ['id' => $id, 'estado' => 'ANULADO'];
    }

    // -------------------------------------------------------------------------
    // removeItem
    // -------------------------------------------------------------------------

    private function removeItem(int $id, string $tipo): array
    {
        if ($id <= 0) {
            throw new RuntimeException('ID de ítem inválido.', 400);
        }

        $tipo = strtolower(trim($tipo));

        if ($tipo === 'clase') {
            $this->claseMatRepo->disableById($id);
        } elseif ($tipo === 'actividad') {
            $this->actMatRepo->disableById($id);
        } else {
            throw new RuntimeException('Tipo de ítem no soportado. Use "clase" o "actividad".', 400);
        }

        return ['id' => $id, 'tipo' => $tipo, 'estado' => 'ANULADO'];
    }
}