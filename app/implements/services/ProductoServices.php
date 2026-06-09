<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/ProductoServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/ProductoRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/entitys/Producto.php';
require_once __DIR__ . '/../persistence/repository/ProductoCategoriaRepository.php';
require_once __DIR__ . '/../persistence/entitys/ProductoCategoria.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\ProductoRepository;
use Repositories\ProductoCategoriaRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Entitys\Producto;
use Entitys\ProductoCategoria;
use Core\Database;
use Utils\Token;
use Interfaces\ProductoServiceInterface;

class ProductoServices implements ProductoServiceInterface
{
    private ProductoRepository $productoRepo;
    private ProductoCategoriaRepository $categRepo;
    private PersonaRepository $personaRepo;
    private array $security;

    public function __construct()
    {
        $this->productoRepo = new ProductoRepository();
        $this->categRepo    = new ProductoCategoriaRepository();
        $this->personaRepo  = new PersonaRepository();
        $this->security     = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        $userId   = (int) ($payload['sub'] ?? 0);
        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'list':
                return $this->fetchProductos($queryParams, $userId);

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                $id = isset($queryParams['id']) ? (int)$queryParams['id'] : null;
                return $this->saveProducto($id, $userId);

            case $method === 'PUT' && $resource === 'disable':
                if (empty($queryParams['id'])) {
                    throw new \RuntimeException('El parámetro id es requerido.', 400);
                }
                return $this->disableProducto((int)$queryParams['id']);

            case $method === 'GET' && $resource === 'categories':
                return $this->fetchCategorias($queryParams);

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'saveCategory':
            case in_array($method, ['POST', 'PUT'], true) && $resource === 'savecategory':
                $id = isset($queryParams['id']) ? (int)$queryParams['id'] : null;
                return $this->saveCategoria($id);

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchProductos
    // -------------------------------------------------------------------------
    private function fetchProductos(array $queryParams, int $userId): array
    {
        $institucion = $this->getInstitucionByUserId($userId);
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
    private function saveProducto(?int $id, int $userId): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['codigo', 'nombre', 'descripcion', 'categoria', 'precio', 'costo'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $bodyId  = isset($data['id']) ? (int)$data['id'] : 0;
        $finalId = ($id !== null && $id > 0) ? $id : ($bodyId > 0 ? $bodyId : null);

        $estado        = isset($data['estado']) ? (int)$data['estado'] : 1;
        $controlaStock = isset($data['controla_stock']) ? (bool)$data['controla_stock'] : false;
        $institucion   = $this->getInstitucionByUserId($userId);

        $producto = new Producto(
            $finalId,
            trim($data['codigo']),
            trim($data['nombre']),
            trim($data['descripcion']),
            (int)$data['categoria'],
            (float)$data['precio'],
            (float)$data['costo'],
            $controlaStock,
            $institucion,
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
    private function fetchCategorias(array $queryParams = []): array
    {
        $institucion = !empty($queryParams['institucion']) ? (int)$queryParams['institucion'] : null;
        $categorias  = $institucion
            ? $this->categRepo->findActiveByInstitucion($institucion)
            : $this->categRepo->findAllActive();
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

    // -------------------------------------------------------------------------
    // saveCategoria
    // -------------------------------------------------------------------------
    private function saveCategoria(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        $nombre = trim((string)($data['nombre'] ?? ''));
        if ($nombre === '') {
            throw new \RuntimeException('El campo nombre es requerido.', 400);
        }

        $descripcion = isset($data['descripcion']) && $data['descripcion'] !== ''
            ? trim((string)$data['descripcion'])
            : null;

        $nemonico = trim((string)($data['nemonico'] ?? ''));
        if ($nemonico === '') {
            $nemonico = strtoupper((string)preg_replace('/[^A-Z0-9]+/', '_', strtoupper($nombre)));
        }

        $institucion = isset($data['institucion']) && $data['institucion'] !== '' ? (int)$data['institucion'] : null;
        $estado = isset($data['estado']) ? (int)$data['estado'] : 1;
        $bodyId = isset($data['id']) ? (int)$data['id'] : 0;
        $finalId = ($id !== null && $id > 0) ? $id : ($bodyId > 0 ? $bodyId : null);

        $categoria = new ProductoCategoria(
            $finalId,
            $nombre,
            $nemonico,
            $descripcion,
            $institucion,
            $estado
        );

        $saved = $this->categRepo->save($categoria);

        return [
            'id'          => (int)$saved->getId(),
            'nombre'      => $saved->getNombre(),
            'nemonico'    => $saved->getNemonico(),
            'descripcion' => $saved->getDescripcion(),
            'institucion' => $saved->getInstitucion()?->getId(),
            'estado'      => $saved->getEstado()?->getId() ?? $estado,
        ];
    }

    // -------------------------------------------------------------------------
    // getInstitucionByUserId
    // -------------------------------------------------------------------------
    private function getInstitucionByUserId(int $userId): int
    {
        $persona = $this->personaRepo->findByUserId($userId);
        if ($persona === null) {
            throw new \RuntimeException('No se encontró persona asociada al usuario.', 404);
        }
        $pi = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());
        if ($pi === null) {
            throw new \RuntimeException('No se encontró institución asociada al usuario.', 404);
        }
        return (int) $pi->getIdInstitucion()->getId();
    }
}
