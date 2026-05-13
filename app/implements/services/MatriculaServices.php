<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/MatriculaServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/MatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/ClaseMatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/ActividadMatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/VentaRepository.php';
require_once __DIR__ . '/../persistence/entitys/Matricula.php';
require_once __DIR__ . '/../persistence/entitys/ClaseMatricula.php';
require_once __DIR__ . '/../persistence/entitys/ActividadMatricula.php';
require_once __DIR__ . '/../persistence/entitys/Venta.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\MatriculaServiceInterface;
use Repositories\MatriculaRepository;
use Repositories\ClaseMatriculaRepository;
use Repositories\ActividadMatriculaRepository;
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
        $db = \Core\Database::getInstance();

        $personaId = null;

        if (!empty($queryParams['persona_id'])) {
            $personaId = (int) $queryParams['persona_id'];
        } elseif (!empty($queryParams['identificacion'])) {
            $stmt = $db->prepare('SELECT id FROM personas WHERE identificacion = :identificacion LIMIT 1');
            $stmt->execute([':identificacion' => (string) $queryParams['identificacion']]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException('No se encontró persona con esa identificación.', 404);
            }
            $personaId = (int) $row['id'];
        }

        if ($personaId === null || $personaId <= 0) {
            throw new RuntimeException('Debe proporcionar persona_id o identificacion.', 400);
        }

        $sql = "SELECT m.id, m.fecha_registro, m.fecha_inicio, m.fecha_fin,
                       mem.titulo AS membresia,
                       i.nombre   AS institucion,
                       e.nombre   AS estado
                FROM matricula m
                LEFT JOIN membresias mem ON mem.id = m.membresia
                JOIN instituciones   i   ON i.id   = m.institucion
                JOIN estados         e   ON e.id   = m.estado
                WHERE m.persona = :persona_id
                ORDER BY m.fecha_registro DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':persona_id' => $personaId]);
        $matriculas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $claseRepo     = new ClaseMatriculaRepository();
        $actividadRepo = new ActividadMatriculaRepository();

        $result = [];
        foreach ($matriculas as $mat) {
            $matriculaId = (int) $mat['id'];

            // Fetch clases via direct query (findByMatricula uses direct DB)
            $stmtC = $db->prepare(
                'SELECT cm.id, cm.clase, cm.modalidad, cm.horario, cm.fecha_inicio, cm.fecha_fin, cm.estado
                 FROM clases_matricula cm
                 WHERE cm.matricula = :matricula_id'
            );
            $stmtC->execute([':matricula_id' => $matriculaId]);
            $clasesRaw = $stmtC->fetchAll(\PDO::FETCH_ASSOC);

            // Fetch actividades via direct query
            $stmtA = $db->prepare(
                'SELECT am.id, am.actividad, am.modalidad, am.categoria_deportiva, am.horario, am.fecha_inicio, am.fecha_fin, am.estado
                 FROM actividades_matricula am
                 WHERE am.matricula = :matricula_id'
            );
            $stmtA->execute([':matricula_id' => $matriculaId]);
            $actividadesRaw = $stmtA->fetchAll(\PDO::FETCH_ASSOC);

            $clases = array_map(fn($c) => [
                'id'           => (int) $c['id'],
                'clase'        => (int) $c['clase'],
                'modalidad'    => (int) $c['modalidad'],
                'horario'      => $c['horario'] !== null ? (int) $c['horario'] : null,
                'fecha_inicio' => $c['fecha_inicio'],
                'fecha_fin'    => $c['fecha_fin'],
                'estado'       => (int) $c['estado'],
            ], $clasesRaw);

            $actividades = array_map(fn($a) => [
                'id'                 => (int) $a['id'],
                'actividad'          => (int) $a['actividad'],
                'modalidad'          => (int) $a['modalidad'],
                'categoria_deportiva'=> $a['categoria_deportiva'] !== null ? (int) $a['categoria_deportiva'] : null,
                'horario'            => $a['horario'] !== null ? (int) $a['horario'] : null,
                'fecha_inicio'       => $a['fecha_inicio'],
                'fecha_fin'          => $a['fecha_fin'],
                'estado'             => (int) $a['estado'],
            ], $actividadesRaw);

            $result[] = [
                'id'            => $matriculaId,
                'fecha_registro'=> $mat['fecha_registro'],
                'fecha_inicio'  => $mat['fecha_inicio'],
                'fecha_fin'     => $mat['fecha_fin'],
                'membresia'     => $mat['membresia'],
                'institucion'   => $mat['institucion'],
                'estado'        => $mat['estado'],
                'clases'        => $clases,
                'actividades'   => $actividades,
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
            $matriculaRepo   = new MatriculaRepository();
            $matriculaEntity = $matriculaRepo->save($matriculaEntity);
            $matriculaId     = (int) $matriculaEntity->getId();

            $subtotal    = 0.0;
            $ventaItems  = [];

            // 2. Insert ClaseMatricula for each clase
            foreach ($clases as $claseItem) {
                $claseId        = (int) ($claseItem['clase_id']    ?? 0);
                $modalidad      = (int) ($claseItem['modalidad']   ?? 0);
                $horario        = isset($claseItem['horario'])     ? (int) $claseItem['horario'] : null;
                $cFechaInicio   = (string) ($claseItem['fecha_inicio'] ?? $fechaInicio);
                $cFechaFin      = (string) ($claseItem['fecha_fin']    ?? $fechaFin);

                if ($claseId <= 0) {
                    continue;
                }

                $claseMatEntity = new ClaseMatricula(null, $claseId, $matriculaId, $modalidad, $cFechaInicio, $cFechaFin, 1, $horario);
                (new ClaseMatriculaRepository())->save($claseMatEntity);

                // Fetch clase price
                $stmtPrice = $db->prepare('SELECT nombre, precio FROM clases WHERE id = :id LIMIT 1');
                $stmtPrice->execute([':id' => $claseId]);
                $claseRow  = $stmtPrice->fetch(\PDO::FETCH_ASSOC);
                $precio    = $claseRow ? (float) ($claseRow['precio'] ?? 0) : 0.0;
                $nombre    = $claseRow ? (string) ($claseRow['nombre'] ?? 'Clase') : 'Clase';

                $subtotal += $precio;
                $ventaItems[] = [
                    'descripcion'     => $nombre,
                    'cantidad'        => 1,
                    'precio_unitario' => $precio,
                    'descuento'       => 0.0,
                    'subtotal'        => $precio,
                ];
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
                (new ActividadMatriculaRepository())->save($actMatEntity);

                // Fetch actividad price
                $stmtPrice = $db->prepare('SELECT nombre, precio FROM actividades WHERE id = :id LIMIT 1');
                $stmtPrice->execute([':id' => $actividadId]);
                $actRow  = $stmtPrice->fetch(\PDO::FETCH_ASSOC);
                $precio  = $actRow ? (float) ($actRow['precio'] ?? 0) : 0.0;
                $nombre  = $actRow ? (string) ($actRow['nombre'] ?? 'Actividad') : 'Actividad';

                $subtotal += $precio;
                $ventaItems[] = [
                    'descripcion'     => $nombre,
                    'cantidad'        => 1,
                    'precio_unitario' => $precio,
                    'descuento'       => 0.0,
                    'subtotal'        => $precio,
                ];
            }

            // 4. Calculate totals
            $impuesto = 0.0;
            $total    = max(0.0, $subtotal - $descuento + $impuesto);
            $saldo    = $total;

            // 5. Generate numero_factura
            $numeroFactura = 'FAC-' . date('Ymd') . '-' . str_pad((string) $matriculaId, 6, '0', STR_PAD_LEFT);

            // 6. Insert Venta
            $fechaVenta   = date('Y-m-d H:i:s');
            $ventaEntity  = new Venta(null, $numeroFactura, $fechaVenta, $persona, $matriculaId, $institucion, 'MATRICULA', $subtotal, $descuento, $impuesto, $total, $saldo, 1);
            $ventaRepo    = new VentaRepository();
            $ventaEntity  = $ventaRepo->save($ventaEntity);
            $ventaId      = (int) $ventaEntity->getId();

            // 7. Insert VentaDetalle for each item
            foreach ($ventaItems as $item) {
                $stmtDet = $db->prepare(
                    'INSERT INTO venta_detalle (venta, descripcion, cantidad, precio_unitario, descuento, subtotal)
                     VALUES (:venta, :descripcion, :cantidad, :precio_unitario, :descuento, :subtotal)'
                );
                $stmtDet->execute([
                    ':venta'           => $ventaId,
                    ':descripcion'     => $item['descripcion'],
                    ':cantidad'        => $item['cantidad'],
                    ':precio_unitario' => $item['precio_unitario'],
                    ':descuento'       => $item['descuento'],
                    ':subtotal'        => $item['subtotal'],
                ]);
            }

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

        $db = \Core\Database::getInstance();

        $stmt = $db->prepare('UPDATE matricula SET estado = 2 WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $stmt2 = $db->prepare('UPDATE clases_matricula SET estado = 2 WHERE matricula = :id');
        $stmt2->execute([':id' => $id]);

        $stmt3 = $db->prepare('UPDATE actividades_matricula SET estado = 2 WHERE matricula = :id');
        $stmt3->execute([':id' => $id]);

        return [
            'id'    => $id,
            'estado'=> 'ANULADO',
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

        $db = \Core\Database::getInstance();

        $tipo = strtolower(trim($tipo));

        if ($tipo === 'clase') {
            $stmt = $db->prepare('UPDATE clases_matricula SET estado = 2 WHERE id = :id');
            $stmt->execute([':id' => $id]);
        } elseif ($tipo === 'actividad') {
            $stmt = $db->prepare('UPDATE actividades_matricula SET estado = 2 WHERE id = :id');
            $stmt->execute([':id' => $id]);
        } else {
            throw new RuntimeException('Tipo de ítem no soportado. Use "clase" o "actividad".', 400);
        }

        return [
            'id'    => $id,
            'tipo'  => $tipo,
            'estado'=> 'ANULADO',
        ];
    }
}
