<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/ProductoServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/ProductoRepository.php';
require_once __DIR__ . '/../persistence/entitys/Producto.php';
require_once __DIR__ . '/../persistence/repository/ProductoCategoriaRepository.php';
require_once __DIR__ . '/../persistence/entitys/ProductoCategoria.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\ProductoRepository;
use Repositories\ProductoCategoriaRepository;
use Entitys\Producto;
use Entitys\ProductoCategoria;
use Utils\Token;
use Interfaces\ProductoServiceInterface;

class ProductoServices implements ProductoServiceInterface
{
    private ProductoRepository $productoRepo;
    private ProductoCategoriaRepository $categRepo;
    private array $security;

    public function __construct()
    {
        $this->productoRepo = new ProductoRepository();
        $this->categRepo    = new ProductoCategoriaRepository();
        $this->security     = require __DIR__ . '/../../properties/security.php';
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
                return $this->fetchProductos($queryParams);

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                $id = isset($queryParams['id']) ? (int)$queryParams['id'] : null;
                return $this->saveProducto($id);

            case $method === 'PUT' && $resource === 'disable':
                if (empty($queryParams['id'])) {
                    throw new \RuntimeException('El parámetro id es requerido.', 400);
                }
                return $this->disableProducto((int)$queryParams['id']);

            case $method === 'GET' && $resource === 'categories':
                return $this->fetchCategorias();

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchProductos
    // -------------------------------------------------------------------------
    private function fetchProductos(array $queryParams): array
    {
        if (empty($queryParams['institucion'])) {
            throw new \RuntimeException('El parámetro institucion es requerido.', 400);
        }

        $institucion = (int)$queryParams['institucion'];
        $categoriaId = !empty($queryParams['categoria']) ? (int)$queryParams['categoria'] : null;

        $rows = $this->productoRepo->findWithStockByInstitucion($institucion, $categoriaId);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'               => (int)$row['id'],
                'codigo'           => $row['codigo'],
                'nombre'           => $row['nombre'],
                'descripcion'      => $row['descripcion'],
                'precio'           => (float)$row['precio'],
                'costo'            => (float)$row['costo'],
                'controla_stock'   => (bool)$row['controla_stock'],
                'estado'           => (int)$row['estado'],
                'categoria_nombre' => $row['categoria_nombre'] ?? null,
                'stock'            => (float)$row['stock'],
            ];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // saveProducto
    // -------------------------------------------------------------------------
    private function saveProducto(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['codigo', 'nombre', 'descripcion', 'categoria', 'precio', 'costo', 'institucion'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $bodyId = isset($data['id']) ? (int)$data['id'] : 0;
        $finalId = ($id !== null && $id > 0) ? $id : ($bodyId > 0 ? $bodyId : null);

        $estado       = isset($data['estado']) ? (int)$data['estado'] : 1;
        $controlaStock = isset($data['controla_stock']) ? (bool)$data['controla_stock'] : false;

        $producto = new Producto(
            $finalId,
            trim($data['codigo']),
            trim($data['nombre']),
            trim($data['descripcion']),
            (int)$data['categoria'],
            (float)$data['precio'],
            (float)$data['costo'],
            $controlaStock,
            (int)$data['institucion'],
            $estado
        );

        $saved = $this->productoRepo->save($producto);

        return [
            'id'             => (int)$saved->getId(),
            'codigo'         => $saved->getCodigo(),
            'nombre'         => $saved->getNombre(),
            'descripcion'    => $saved->getDescripcion(),
            'precio'         => $saved->getPrecio(),
            'costo'          => $saved->getCosto(),
            'controla_stock' => $saved->getControlaStock(),
            'estado'         => $estado,
        ];
    }

    // -------------------------------------------------------------------------
    // disableProducto
    // -------------------------------------------------------------------------
    private function disableProducto(int $id): array
    {
        $produto = $this->productoRepo->findById($id);
        if (!$produto) {
            throw new \RuntimeException('Producto no encontrado.', 404);
        }
        $produto->setEstado(2);
        $this->productoRepo->save($produto);
        return ['id' => $id, 'estado' => 'INACTIVO'];
    }

    // -------------------------------------------------------------------------
    // fetchCategorias
    // -------------------------------------------------------------------------
    private function fetchCategorias(): array
    {
        $categorias = $this->categRepo->findAllActive();
        $result     = [];
        foreach ($categorias as $cat) {
            $result[] = [
                'id'          => (int)$cat->getId(),
                'nombre'      => $cat->getNombre(),
                'descripcion' => $cat->getDescripcion(),
            ];
        }
        return $result;
    }
}
