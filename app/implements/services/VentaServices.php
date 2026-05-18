<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/VentaRepository.php';
require_once __DIR__ . '/../persistence/repository/VentaDetalleRepository.php';
require_once __DIR__ . '/../persistence/repository/PagoRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/InventarioRepository.php';
require_once __DIR__ . '/../persistence/repository/MovimientoInventarioRepository.php';
require_once __DIR__ . '/../persistence/repository/ProductoRepository.php';
require_once __DIR__ . '/../persistence/repository/FormaPagoRepository.php';
require_once __DIR__ . '/../persistence/entitys/FormaPago.php';
require_once __DIR__ . '/../persistence/entitys/Venta.php';
require_once __DIR__ . '/../persistence/entitys/VentaDetalle.php';
require_once __DIR__ . '/../persistence/entitys/Pago.php';
require_once __DIR__ . '/../persistence/entitys/MovimientoInventario.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Repositories\VentaRepository;
use Repositories\VentaDetalleRepository;
use Repositories\PagoRepository;
use Repositories\PersonaRepository;
use Repositories\InventarioRepository;
use Repositories\MovimientoInventarioRepository;
use Repositories\ProductoRepository;
use Repositories\FormaPagoRepository;
use Entitys\FormaPago;
use Entitys\Venta;
use Entitys\VentaDetalle;
use Entitys\Pago;
use Entitys\MovimientoInventario;
use Utils\Token;

class VentaServices
{
    private VentaRepository               $ventaRepo;
    private VentaDetalleRepository        $detalleRepo;
    private PagoRepository                $pagoRepo;
    private PersonaRepository             $personaRepo;
    private InventarioRepository          $inventarioRepo;
    private MovimientoInventarioRepository $movimientoRepo;
    private ProductoRepository            $productoRepo;
    private FormaPagoRepository           $formaPagoRepo;
    private array                         $security;

    public function __construct()
    {
        $this->ventaRepo      = new VentaRepository();
        $this->detalleRepo    = new VentaDetalleRepository();
        $this->pagoRepo       = new PagoRepository();
        $this->personaRepo    = new PersonaRepository();
        $this->inventarioRepo = new InventarioRepository();
        $this->movimientoRepo = new MovimientoInventarioRepository();
        $this->productoRepo   = new ProductoRepository();
        $this->formaPagoRepo  = new FormaPagoRepository();
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
                return $this->fetchVentas($queryParams);

            case $method === 'GET' && $resource === 'byclient':
                return $this->fetchByClient($queryParams);

            case $method === 'GET' && $resource === 'detail':
                if (empty($queryParams['id'])) {
                    throw new \RuntimeException('El parámetro id es requerido.', 400);
                }
                return $this->fetchDetail((int)$queryParams['id']);

            case $method === 'POST' && $resource === 'create':
                return $this->createVenta();

            case $method === 'GET' && $resource === 'formas_pago':
                return $this->fetchFormasPago();

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchVentas
    // -------------------------------------------------------------------------
    private function fetchVentas(array $queryParams): array
    {
        if (empty($queryParams['institucion'])) {
            throw new \RuntimeException('El parámetro institucion es requerido.', 400);
        }

        $filters = [
            'institucion' => (int)$queryParams['institucion'],
            'limit'       => isset($queryParams['limit']) ? max(1, (int)$queryParams['limit']) : 20,
            'page'        => isset($queryParams['page']) ? max(1, (int)$queryParams['page']) : 1,
            'desde'       => !empty($queryParams['desde']) ? $queryParams['desde'] : null,
            'hasta'       => !empty($queryParams['hasta']) ? $queryParams['hasta'] : null,
            'q'           => !empty($queryParams['q']) ? $queryParams['q'] : null,
        ];

        $result = $this->ventaRepo->findByInstitucion($filters['institucion'], $filters);

        return [
            'items' => array_map(fn($row) => [
                'id'             => (int)$row['id'],
                'numero'         => $row['numero'],
                'fecha'          => $row['fecha'],
                'tipo'           => $row['tipo'],
                'total'          => (float)$row['total'],
                'saldo'          => (float)$row['saldo'],
                'estado'         => (int)$row['estado'],
                'identificacion' => $row['identificacion'],
                'nombre'         => $row['nombre'],
            ], $result['items']),
            'totalItems'  => (int) $result['total'],
            'totalPages'  => (int) ceil($result['total'] / $filters['limit']),
            'currentPage' => $filters['page'],
        ];
    }

    // -------------------------------------------------------------------------
    // fetchByClient
    // -------------------------------------------------------------------------
    private function fetchByClient(array $queryParams): array
    {
        $identificacion = trim($queryParams['identificacion'] ?? '');
        if ($identificacion === '') {
            throw new \RuntimeException('El parámetro identificacion es requerido.', 400);
        }

        $persona = $this->personaRepo->findByIdentificacion($identificacion);
        if (!$persona) {
            throw new \RuntimeException('Persona no encontrada.', 404);
        }

        $ventas = $this->ventaRepo->findByPersona((int)$persona->getId());

        $result = [];
        foreach ($ventas as $venta) {
            $result[] = [
                'id'       => (int)$venta->getId(),
                'numero'   => $venta->getNumero(),
                'fecha'    => $venta->getFecha(),
                'tipo'     => $venta->getTipo(),
                'subtotal' => $venta->getSubtotal(),
                'descuento' => $venta->getDescuento(),
                'impuesto' => $venta->getImpuesto(),
                'total'    => $venta->getTotal(),
                'saldo'    => $venta->getSaldo(),
                'estado'   => $venta->getEstado()?->getId(),
            ];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // fetchDetail
    // -------------------------------------------------------------------------
    private function fetchDetail(int $id): array
    {
        $venta = $this->ventaRepo->findById($id);
        if (!$venta) {
            throw new \RuntimeException('Venta no encontrada.', 404);
        }

        // Fetch detail lines via repository
        $detalles = $this->detalleRepo->findByVenta($id);

        // Fetch pagos applied to this venta via repository
        $pagosRows = $this->pagoRepo->findByVenta($id);

        $detallesFormatted = [];
        foreach ($detalles as $d) {
            $detallesFormatted[] = [
                'id'              => (int)$d->getId(),
                'descripcion'     => null,
                'cantidad'        => (float)$d->getCantidad(),
                'precio_unitario' => (float)$d->getPrecioUnitario(),
                'descuento'       => (float)$d->getDescuento(),
                'subtotal'        => (float)$d->getTotal(),
            ];
        }

        $pagosFormatted = [];
        foreach ($pagosRows as $p) {
            $pagosFormatted[] = [
                'numero'    => $p['numero'],
                'fecha'     => $p['fecha'],
                'monto'     => (float)$p['monto'],
                'forma_pago' => $p['forma_pago'],
            ];
        }

        return [
            'venta'           => [
                'id'        => (int)$venta->getId(),
                'numero'    => $venta->getNumero(),
                'fecha'     => $venta->getFecha(),
                'tipo'      => $venta->getTipo(),
                'subtotal'  => $venta->getSubtotal(),
                'descuento' => $venta->getDescuento(),
                'impuesto'  => $venta->getImpuesto(),
                'total'     => $venta->getTotal(),
                'saldo'     => $venta->getSaldo(),
                'estado'    => $venta->getEstado()?->getId(),
            ],
            'detalles'        => $detallesFormatted,
            'pagos_aplicados' => $pagosFormatted,
        ];
    }

    // -------------------------------------------------------------------------
    // createVenta
    // -------------------------------------------------------------------------
    private function createVenta(): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['persona', 'institucion', 'tipo', 'items'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $items = $data['items'];
        if (!is_array($items) || count($items) === 0) {
            throw new \RuntimeException('Se requiere al menos un item.', 400);
        }

        $personaId   = (int)$data['persona'];
        $matriculaId = isset($data['matricula']) && $data['matricula'] !== '' ? (int)$data['matricula'] : null;
        $institucion = (int)$data['institucion'];
        $tipo        = trim($data['tipo']);
        $descuento   = (float)($data['descuento'] ?? 0);
        $formaPago   = isset($data['forma_pago']) && $data['forma_pago'] !== '' ? (int)$data['forma_pago'] : null;
        $referencia  = $data['referencia'] ?? null;

        // Calculate totals
        $subtotal = 0.0;
        foreach ($items as $item) {
            $qty   = (float)($item['cantidad']       ?? 0);
            $price = (float)($item['precio_unitario'] ?? 0);
            $subtotal += $qty * $price;
        }

        $impuesto = 0.0; // extend here if 12% IVA is needed
        $total    = round($subtotal - $descuento + $impuesto, 2);
        $saldo    = ($formaPago !== null) ? 0.0 : $total;
        $estado   = 1;

        $numero = 'VTA-' . date('Ymd') . '-' . uniqid();
        $fecha  = date('Y-m-d H:i:s');

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // 1. Insert venta
            $venta = new Venta(
                null,
                $numero,
                $fecha,
                $personaId,
                $matriculaId,
                $institucion,
                $tipo,
                $subtotal,
                $descuento,
                $impuesto,
                $total,
                $saldo,
                $estado
            );
            $ventaSaved = $this->ventaRepo->save($venta);
            $ventaId    = (int)$ventaSaved->getId();

            // 2. Insert venta_detalle + stock updates
            foreach ($items as $item) {
                $productoId    = (int)($item['producto_id']     ?? 0);
                $qty           = (float)($item['cantidad']       ?? 0);
                $price         = (float)($item['precio_unitario'] ?? 0);
                $itemDescuento = (float)($item['descuento']      ?? 0);
                $itemSubtotal  = round(($qty * $price) - $itemDescuento, 2);

                // Save venta_detalle via repository
                $ventaDetalle = new VentaDetalle(
                    null,
                    $ventaId,
                    $productoId > 0 ? $productoId : 0,
                    null,
                    $qty,
                    $price,
                    $itemDescuento,
                    $itemSubtotal
                );
                $this->detalleRepo->save($ventaDetalle);

                if ($productoId <= 0) {
                    continue;
                }

                // Check if producto controla_stock
                $prod = $this->productoRepo->findById($productoId);
                if ($prod && (bool)$prod->getControlaStock()) {
                    // 3. Update inventario (reduce stock) via repository
                    $inv = $this->inventarioRepo->findByProductoAndInstitucion($productoId, $institucion);
                    if ($inv !== null) {
                        $stockAnterior = $inv->getStockActual();
                        $stockNuevo    = $stockAnterior - $qty;
                        $inv->setStockActual($stockNuevo);
                        $inv->setFechaActualizacion($fecha);
                        $this->inventarioRepo->save($inv);
                    } else {
                        $stockAnterior = 0.0;
                        $stockNuevo    = -$qty;
                    }

                    // 4. Insert movimiento_inventario via repository
                    $movimiento = new MovimientoInventario(
                        null,
                        $productoId,
                        null,
                        $institucion,
                        'SALIDA',
                        $qty,
                        $stockAnterior,
                        $stockNuevo,
                        $numero,
                        $fecha,
                        'Venta ' . $numero
                    );
                    $this->movimientoRepo->save($movimiento);
                }
            }

            // 5. If forma_pago provided, insert pago immediately via repository
            if ($formaPago !== null) {
                $numeroPago = 'PAG-' . date('Ymd') . '-' . uniqid();
                $pagoEntity = new Pago(
                    null,
                    $numeroPago,
                    $fecha,
                    $personaId,
                    $matriculaId,
                    $ventaId,
                    $formaPago,
                    $institucion,
                    $total,
                    $referencia,
                    'Pago de venta ' . $numero,
                    1
                );
                $this->pagoRepo->save($pagoEntity);
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new \RuntimeException('Error al crear la venta: ' . $e->getMessage(), 500);
        }

        return [
            'id'     => $ventaId,
            'numero' => $numero,
            'total'  => $total,
            'saldo'  => $saldo,
        ];
    }

    // -------------------------------------------------------------------------
    // fetchFormasPago
    // -------------------------------------------------------------------------
    private function fetchFormasPago(): array
    {
        $formas = $this->formaPagoRepo->findAll();
        return array_map(fn($f) => ['id' => (int) $f->getId(), 'nombre' => $f->getNombre()], $formas);
    }
}
