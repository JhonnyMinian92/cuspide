<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/InventarioServiceInterface.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Utils\Token;
use Interfaces\InventarioServiceInterface;

class InventarioServices implements InventarioServiceInterface
{
    private array $security;

    public function __construct()
    {
        $this->security = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'list':
                return $this->fetchInventario($queryParams);

            case $method === 'GET' && $resource === 'movements':
                return $this->fetchMovimientos($queryParams);

            case $method === 'POST' && $resource === 'adjust':
                return $this->ajustarInventario();

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchInventario
    // -------------------------------------------------------------------------
    private function fetchInventario(array $queryParams): array
    {
        $institucion = isset($queryParams['institucion']) ? (int)$queryParams['institucion'] : 0;
        if ($institucion <= 0) {
            throw new \RuntimeException('El parámetro institucion es requerido.', 400);
        }

        $db = Database::getInstance();

        $sql = "SELECT pr.id,
                       pr.codigo,
                       pr.nombre,
                       pr.precio,
                       pc.nombre AS categoria,
                       COALESCE(inv.stock_actual, 0) AS stock_actual,
                       COALESCE(inv.stock_minimo, 0) AS stock_minimo,
                       CASE WHEN COALESCE(inv.stock_actual, 0) <= COALESCE(inv.stock_minimo, 0)
                            THEN 1 ELSE 0
                       END AS alerta_stock
                FROM productos pr
                LEFT JOIN producto_categorias pc ON pc.id = pr.categoria
                LEFT JOIN inventario inv ON inv.producto = pr.id AND inv.institucion = :inst
                WHERE pr.estado = 1
                  AND pr.controla_stock = 1
                  AND pr.institucion = :inst2
                ORDER BY pr.nombre ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':inst',  $institucion, \PDO::PARAM_INT);
        $stmt->bindValue(':inst2', $institucion, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'          => (int)$row['id'],
                'codigo'      => $row['codigo'],
                'nombre'      => $row['nombre'],
                'precio'      => (float)$row['precio'],
                'categoria'   => $row['categoria'] ?? null,
                'stock_actual'=> (float)$row['stock_actual'],
                'stock_minimo'=> (float)$row['stock_minimo'],
                'alerta_stock'=> (bool)$row['alerta_stock'],
            ];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // fetchMovimientos
    // -------------------------------------------------------------------------
    private function fetchMovimientos(array $queryParams): array
    {
        $productoId = isset($queryParams['producto_id']) ? (int)$queryParams['producto_id'] : 0;
        if ($productoId <= 0) {
            throw new \RuntimeException('El parámetro producto_id es requerido.', 400);
        }

        $limit = isset($queryParams['limit']) ? max(1, (int)$queryParams['limit']) : 50;

        $db = Database::getInstance();

        $sql = "SELECT mi.id,
                       mi.tipo AS tipo_movimiento,
                       mi.cantidad,
                       mi.fecha,
                       mi.referencia,
                       mi.observacion
                FROM movimientos_inventario mi
                WHERE mi.producto = :producto
                ORDER BY mi.fecha DESC
                LIMIT :lim";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':producto', $productoId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim',      $limit,      \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'              => (int)$row['id'],
                'tipo_movimiento' => $row['tipo_movimiento'],
                'cantidad'        => (float)$row['cantidad'],
                'fecha'           => $row['fecha'],
                'referencia'      => $row['referencia'],
                'observacion'     => $row['observacion'],
            ];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // ajustarInventario
    // -------------------------------------------------------------------------
    private function ajustarInventario(): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['producto', 'tipo_movimiento', 'cantidad', 'institucion'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $tiposPermitidos = ['ENTRADA', 'SALIDA', 'AJUSTE'];
        $tipoMov = strtoupper(trim($data['tipo_movimiento']));
        if (!in_array($tipoMov, $tiposPermitidos, true)) {
            throw new \RuntimeException('tipo_movimiento debe ser ENTRADA, SALIDA o AJUSTE.', 400);
        }

        $productoId  = (int)$data['producto'];
        $varianteId  = isset($data['variante']) && $data['variante'] !== '' ? (int)$data['variante'] : null;
        $cantidad    = (float)$data['cantidad'];
        $institucion = (int)$data['institucion'];
        $referencia  = $data['referencia']  ?? null;
        $observacion = $data['observacion'] ?? '';
        $fecha       = date('Y-m-d H:i:s');

        if ($cantidad <= 0) {
            throw new \RuntimeException('La cantidad debe ser mayor a cero.', 400);
        }

        $db = Database::getInstance();

        // Check if inventario row exists
        $sqlCheck = 'SELECT id, stock_actual FROM inventario WHERE producto = :producto AND institucion = :inst';
        $sqlCheck .= $varianteId !== null ? ' AND variante = :variante' : ' AND (variante IS NULL OR variante = 0)';
        $stmtChk  = $db->prepare($sqlCheck);
        $params   = [':producto' => $productoId, ':inst' => $institucion];
        if ($varianteId !== null) {
            $params[':variante'] = $varianteId;
        }
        $stmtChk->execute($params);
        $invRow = $stmtChk->fetch(\PDO::FETCH_ASSOC);

        if ($invRow) {
            // Update existing row
            $invId       = (int)$invRow['id'];
            $stockActual = (float)$invRow['stock_actual'];

            $stockNuevo = match ($tipoMov) {
                'ENTRADA' => $stockActual + $cantidad,
                'SALIDA'  => $stockActual - $cantidad,
                'AJUSTE'  => $cantidad,
                default   => $stockActual,
            };

            $sqlUpd = 'UPDATE inventario SET stock_actual = :stock WHERE id = :id';
            $stmtUpd = $db->prepare($sqlUpd);
            $stmtUpd->execute([':stock' => $stockNuevo, ':id' => $invId]);
        } else {
            // Insert new inventario row
            $stockNuevo = match ($tipoMov) {
                'ENTRADA' => $cantidad,
                'SALIDA'  => -$cantidad,
                'AJUSTE'  => $cantidad,
                default   => 0,
            };

            $sqlIns = "INSERT INTO inventario
                           (producto, variante, stock_actual, stock_minimo, institucion)
                       VALUES
                           (:producto, :variante, :stock, 0, :inst)";
            $stmtIns = $db->prepare($sqlIns);
            $stmtIns->execute([
                ':producto' => $productoId,
                ':variante' => $varianteId,
                ':stock'    => $stockNuevo,
                ':inst'     => $institucion,
            ]);
        }

        $sqlMov = "INSERT INTO movimientos_inventario
                       (producto, variante, institucion, tipo, cantidad, stock_anterior, stock_nuevo, referencia, fecha, observacion)
                   VALUES
                       (:producto, :variante, :institucion, :tipo, :cantidad, :stock_anterior, :stock_nuevo, :referencia, :fecha, :observacion)";
        $stmtMov = $db->prepare($sqlMov);
        $stmtMov->execute([
            ':producto'       => $productoId,
            ':variante'       => $varianteId,
            ':institucion'    => $institucion,
            ':tipo'           => $tipoMov,
            ':cantidad'       => $cantidad,
            ':stock_anterior' => $stockActual ?? 0,
            ':stock_nuevo'    => $stockNuevo,
            ':referencia'     => $referencia,
            ':fecha'          => $fecha,
            ':observacion'    => $observacion,
        ]);

        return [
            'producto'   => $productoId,
            'stock_nuevo'=> round($stockNuevo, 4),
        ];
    }
}
