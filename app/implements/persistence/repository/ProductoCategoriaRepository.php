<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/ProductoCategoria.php';

use Core\Database;
use Entitys\ProductoCategoria;
use PDO;

class ProductoCategoriaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(ProductoCategoria $categoria): ProductoCategoria
  {
    if ($categoria->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO producto_categorias (nombre, nemonico, descripcion, institucion, estado) VALUES (:nombre, :nemonico, :descripcion, :institucion, :estado)");
      $stmt->execute($this->params($categoria));
      $categoria->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE producto_categorias SET nombre = :nombre, nemonico = :nemonico, descripcion = :descripcion, institucion = :institucion, estado = :estado WHERE id = :id");
      $params = $this->params($categoria);
      $params[':id'] = $categoria->getId();
      $stmt->execute($params);
    }
    return $categoria;
  }

  public function findById(int $id): ?ProductoCategoria
  {
    $stmt = $this->db->prepare('SELECT * FROM producto_categorias WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM producto_categorias ORDER BY nombre ASC');
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(ProductoCategoria $categoria): array
  {
    return [
      ':nombre' => $categoria->getNombre(),
      ':nemonico' => $categoria->getNemonico(),
      ':descripcion' => $categoria->getDescripcion(),
      ':institucion' => $categoria->getInstitucion()?->getId(),
      ':estado' => $categoria->getEstado()?->getId(),
    ];
  }

  private function map(array $row): ProductoCategoria
  {
    return new ProductoCategoria($row['id'], $row['nombre'], $row['nemonico'], $row['descripcion'], $row['institucion'], $row['estado']);
  }
}
