<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/CategoriaDeportiva.php';

use Core\Database;
use Entitys\CategoriaDeportiva;
use PDO;

class CategoriaDeportivaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(CategoriaDeportiva $categoria): CategoriaDeportiva
  {
    if ($categoria->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO categorias_deportivas (nombre, nemonico, deporte, edad_minima, edad_maxima, descripcion, institucion, estado) VALUES (:nombre, :nemonico, :deporte, :edad_minima, :edad_maxima, :descripcion, :institucion, :estado)");
      $stmt->execute($this->params($categoria));
      $categoria->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE categorias_deportivas SET nombre = :nombre, nemonico = :nemonico, deporte = :deporte, edad_minima = :edad_minima, edad_maxima = :edad_maxima, descripcion = :descripcion, institucion = :institucion, estado = :estado WHERE id = :id");
      $params = $this->params($categoria);
      $params[':id'] = $categoria->getId();
      $stmt->execute($params);
    }
    return $categoria;
  }

  public function findById(int $id): ?CategoriaDeportiva
  {
    $stmt = $this->db->prepare('SELECT * FROM categorias_deportivas WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM categorias_deportivas ORDER BY nombre ASC');
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(CategoriaDeportiva $categoria): array
  {
    return [
      ':nombre' => $categoria->getNombre(),
      ':nemonico' => $categoria->getNemonico(),
      ':deporte' => $categoria->getDeporte()?->getId(),
      ':edad_minima' => $categoria->getEdadMinima(),
      ':edad_maxima' => $categoria->getEdadMaxima(),
      ':descripcion' => $categoria->getDescripcion(),
      ':institucion' => $categoria->getInstitucion()?->getId(),
      ':estado' => $categoria->getEstado()?->getId(),
    ];
  }

  private function map(array $row): CategoriaDeportiva
  {
    return new CategoriaDeportiva($row['id'], $row['nombre'], $row['nemonico'], $row['deporte'], $row['edad_minima'] === null ? null : (int) $row['edad_minima'], $row['edad_maxima'] === null ? null : (int) $row['edad_maxima'], $row['descripcion'], $row['institucion'], $row['estado']);
  }
}
