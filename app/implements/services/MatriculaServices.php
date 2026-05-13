<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/MatriculaServiceInterface.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/MatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/ClaseMatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/ActividadMatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/VentaRepository.php';
require_once __DIR__ . '/../persistence/entitys/Matricula.php';
require_once __DIR__ . '/../persistence/entitys/ClaseMatricula.php';
require_once __DIR__ . '/../persistence/entitys/ActividadMatricula.php';
require_once __DIR__ . '/../persistence/entitys/Venta.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\MatriculaServiceInterface;
use Repositories\MatriculaRepository;
use Repositories\ClaseMatriculaRepository;
use Repositories\ActividadMatriculaRepository;
use Repositories\PersonaRepository;
use Repositories\VentaRepository;
use Entitys\Matricula;
use Entitys\ClaseMatricula;
use Entitys\ActividadMatricula;
use Entitys\Venta;
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
        $payload = Token::validateBearerToken($authHeader, $this->jwtSecret);

        $resource = $this->resolveResource($requestUri, $queryParams, 'matriculas');

        return match (true) {
            $method === 'GET'  && $resource === 'list'       => $this->fetchMatriculas($queryParams),
            $method === 'POST' && $resource === 'create'     => $this->createMatricula(),
            $method === 'PUT'  && $resource === 'cancel'     => $this->cancelMatricula((int) ($queryParams['id'] ?? 0)),
            $method === 'PUT'  && $resource === 'removeitem' => $this->removeItem((int) ($queryParams['id'] ?? 0), (string) ($queryParams['tipo'] ?? '')),
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

        $matriculas = $this->matriculaRepo->findByPersonaId($personaId);

        $result = [];
        foreach ($matriculas as $mat) {
            $matriculaId = (int) $mat['id'];

            $claseEntities    = $this->claseMatRepo->findByMatriculaId($matriculaId);
            $actividadEntities = $this->actMatRepo->findByMatriculaId($matriculaId);

            $clases = array_map(fn(ClaseMatricula $c) => [
                'id'           => (int) $c->getId(),
                'clase'        => (int) $c->getClase()->getId(),
                'modalidad'    => (int) $c->getModalidad()?->getId(),
                'horario'      => $c->getHorario() ? (int) $c->getHorario()->getId() : null,
                'fecha_inicio' => $c->getFechaInicio(),
                'fecha_fin'    => $c->getFechaFin(),
                'estado'       => (int) $c->getEstado()?->getId(),
            ], $claseEntities);

            $actividades = array_map(fn(ActividadMatricula $a) => [
                'id'                  => (int) $a->getId(),
                'actividad'           => (int) $a->getActividad()->getId(),
                'modalidad'           => (int) $a->getModalidad()?->getId(),
                'categoria_deportiva' => $a->getCategoriaDeportiva() ? (int) $a->getCategoriaDeportiva()->getId() : null,
                'horario'             => $a->getHorario() ? (int) $a->getHorario()->getId() : null,
                'fecha_inicio'        => $a->getFechaInicio(),
                'fecha_fin'           => $a->getFechaFin(),
                'estado'              => (int) $a->getEstado()?->getId(),
            ], $actividadEntities);

            $result[] = [
                'id'             => $matriculaId,
                'fecha_registro' => $mat['fecha_registro'],
                'fecha_inicio'   => $mat['fecha_inicio'],
                'fecha_fin'      => $mat['fecha_fin'],
                'membresia'      => $mat['membresia'],
                'institucion'    => $mat['institucion'],
                'estado'         => $mat['estado'],
                'clases'         => $clases,
                'actividades'    => $actividades,
            ];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // createMatricula
    // -------------------------------------------------------------------------

    private function createMatricula(): array
    {
        $body = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($body)) {
            throw new RuntimeException('Cuerpo de la solicitud inválido o vacío.', 400);
        }

        $persona     = (int) ($body['persona']     ?? 0);
        $fechaInicio = (string) ($body['fecha_inicio'] ?? '');
        $fechaFin    = (string) ($body['fecha_fin']    ?? '');
        $membresia   = isset($body['membresia']) ? (int) $body['membresia'] : null;
        $institucion = (int) ($body['institucion'] ?? 0);
        $clases      = is_array($body['clases'] ?? null)     ? $body['clases']      : [];
        $actividades = is_array($body['actividades'] ?? null) ? $body['actividades'] : [];
        $descuento   = (float) ($body['descuento'] ?? 0);

        if ($persona <= 0 || $fechaInicio === '' || $fechaFin === '' || $institucion <= 0) {
            throw new RuntimeException('Faltan campos obligatorios: persona, fecha_inicio, fecha_fin, institucion.', 400);
        }

        $db = \Core\Database::getInstance();

        try {
            $db->beginTransaction();

            // 1. Insert Matricula
            $fechaRegistro = date('Y-m-d H:i:s');
            $matriculaEntity = new Matricula(null, $fechaRegistro, $persona, $fechaInicio, $fechaFin, $membresia, $institucion, 1);
            $matriculaEntity = $this->matriculaRepo->save($matriculaEntity);
            $matriculaId     = (int) $matriculaEntity->getId();

            $subtotal   = 0.0;

            // 2. Insert ClaseMatricula for each clase
            foreach ($clases as $claseItem) {
                $claseId      = (int) ($claseItem['clase_id']    ?? 0);
                $modalidad    = (int) ($claseItem['modalidad']   ?? 0);
                $horario      = isset($claseItem['horario'])     ? (int) $claseItem['horario'] : null;
                $cFechaInicio = (string) ($claseItem['fecha_inicio'] ?? $fechaInicio);
                $cFechaFin    = (string) ($claseItem['fecha_fin']    ?? $fechaFin);

                if ($claseId <= 0) {
                    continue;
                }

                $claseMatEntity = new ClaseMatricula(null, $claseId, $matriculaId, $modalidad, $cFechaInicio, $cFechaFin, 1, $horario);
                $this->claseMatRepo->save($claseMatEntity);

                // Fetch clase price
                $stmtPrice = $db->prepare('SELECT nombre FROM clases WHERE id = :id LIMIT 1');
                $stmtPrice->execute([':id' => $claseId]);
                $claseRow = $stmtPrice->fetch(\PDO::FETCH_ASSOC);
                $precio   = $claseRow ? (float) ($claseRow['precio'] ?? 0) : 0.0;

                $subtotal += $precio;
            }

            // 3. Insert ActividadMatricula for each actividad
            foreach ($actividades as $actItem) {
                $actividadId  = (int) ($actItem['actividad_id']       ?? 0);
                $modalidad    = (int) ($actItem['modalidad']           ?? 0);
                $horario      = isset($actItem['horario'])             ? (int) $actItem['horario'] : null;
                $catDeportiva = isset($actItem['categoria_deportiva']) ? (int) $actItem['categoria_deportiva'] : null;
                $aFechaInicio = (string) ($actItem['fecha_inicio']     ?? $fechaInicio);
                $aFechaFin    = (string) ($actItem['fecha_fin']        ?? $fechaFin);

                if ($actividadId <= 0) {
                    continue;
                }

                $actMatEntity = new ActividadMatricula(null, $actividadId, $matriculaId, $modalidad, $aFechaInicio, $aFechaFin, 1, $catDeportiva, $horario);
                $this->actMatRepo->save($actMatEntity);

                // Fetch actividad price
                $stmtPrice = $db->prepare('SELECT nombre, precio FROM actividades WHERE id = :id LIMIT 1');
                $stmtPrice->execute([':id' => $actividadId]);
                $actRow = $stmtPrice->fetch(\PDO::FETCH_ASSOC);
                $precio = $actRow ? (float) ($actRow['precio'] ?? 0) : 0.0;

                $subtotal += $precio;
            }

            // 4. Calculate totals
            $impuesto = 0.0;
            $total    = max(0.0, $subtotal - $descuento + $impuesto);
            $saldo    = $total;

            // 5. Generate numero_factura
            $numeroFactura = 'FAC-' . date('Ymd') . '-' . str_pad((string) $matriculaId, 6, '0', STR_PAD_LEFT);

            // 6. Insert Venta
            $fechaVenta  = date('Y-m-d H:i:s');
            $ventaEntity = new Venta(null, $numeroFactura, $fechaVenta, $persona, $matriculaId, $institucion, 'MATRICULA', $subtotal, $descuento, $impuesto, $total, $saldo, 1);
            $ventaRepo   = new VentaRepository();
            $ventaRepo->save($ventaEntity);

            $db->commit();

            return [
                'matricula_id'   => $matriculaId,
                'numero_factura' => $numeroFactura,
                'subtotal'       => $subtotal,
                'descuento'      => $descuento,
                'impuesto'       => $impuesto,
                'total'          => $total,
            ];

        } catch (\Throwable $e) {
            $db->rollBack();
            throw new RuntimeException('Error al crear matrícula: ' . $e->getMessage(), 500);
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

        $this->matriculaRepo->disable($id);
        $this->claseMatRepo->cancelByMatriculaId($id);
        $this->actMatRepo->cancelByMatriculaId($id);

        return [
            'id'     => $id,
            'estado' => 'ANULADO',
        ];
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

        return [
            'id'     => $id,
            'tipo'   => $tipo,
            'estado' => 'ANULADO',
        ];
    }
}