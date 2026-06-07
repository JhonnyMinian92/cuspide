<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/InventarioServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/InventarioRepository.php';
require_once __DIR__ . '/../persistence/repository/MovimientoInventarioRepository.php';
require_once __DIR__ . '/../persistence/entitys/Inventario.php';
require_once __DIR__ . '/../persistence/entitys/MovimientoInventario.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\InventarioRepository;
use Repositories\MovimientoInventarioRepository;
use Entitys\Inventario;
use Entitys\MovimientoInventario;
use Utils\Token;
use Interfaces\InventarioServiceInterface;

class InventarioServices implements InventarioServiceInterface
{
    private InventarioRepository          $inventarioRepo;
    private MovimientoInventarioRepository $movimientoRepo;
    private array $security;

    public function __construct()
    {
        $this->inventarioRepo = new InventarioRepository();
        $this->movimientoRepo = new MovimientoInventarioRepository();
        $this->security       = require __DIR__ . '/../../properties/security.php';
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

        $rows = $this->inventarioRepo->findWithAlertByInstitucion($institucion);

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

        $movimientos = $this->movimientoRepo->findByProducto($productoId);
        $movimientos = array_slice($movimientos, 0, $limit);

        $result = [];
        foreach ($movimientos as $m) {
            $result[] = [
                'id'              => (int)$m->getId(),
                'tipo_movimiento' => $m->getTipo(),
                'cantidad'        => (float)$m->getCantidad(),
                'fecha'           => $m->getFecha(),
                'referencia'      => $m->getReferencia(),
                'observacion'     => $m->getObservacion(),
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

        // Step 1: Get current inventory via repository
        $inv = $this->inventarioRepo->findByProductoAndInstitucion($productoId, $institucion, $varianteId);

        // Step 2: Compute new stock
        if ($inv !== null) {
            $stockAnterior = $inv->getStockActual();
            $stockNuevo    = match ($tipoMov) {
                'ENTRADA' => $stockAnterior + $cantidad,
                'SALIDA'  => $stockAnterior - $cantidad,
                'AJUSTE'  => $cantidad,
                default   => $stockAnterior,
            };
            // Step 3: Update existing inventory record
            $inv->setStockActual($stockNuevo);
            $inv->setFechaActualizacion($fecha);
            $this->inventarioRepo->save($inv);
        } else {
            $stockAnterior = 0.0;
            $stockNuevo    = match ($tipoMov) {
                'ENTRADA' => $cantidad,
                'SALIDA'  => -$cantidad,
                'AJUSTE'  => $cantidad,
                default   => 0.0,
            };
            // Step 3: Insert new inventory record
            $newInv = new Inventario(null, $productoId, $varianteId, $institucion, $stockNuevo, 0.0, $fecha);
            $this->inventarioRepo->save($newInv);
        }

        // Step 4: Save movement record via repository
        $mov = new MovimientoInventario(
            null,
            $productoId,
            $varianteId,
            $institucion,
            $tipoMov,
            $cantidad,
            $stockAnterior,
            $stockNuevo,
            $referencia,
            $fecha,
            $observacion
        );
        $this->movimientoRepo->save($mov);

        return [
            'producto'    => $productoId,
            'stock_nuevo' => round($stockNuevo, 4),
        ];
    }
}