<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/TipoPermiso.php';

use Core\Database;
use Entitys\TipoPermiso;
use PDO;

class TipoPermisoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(TipoPermiso $tipoPermiso): TipoPermiso
  {
    if ($tipoPermiso->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO tipo_permiso (nemonico, nombre) VALUES (:nemonico, :nombre)");
      $stmt->execute([
        ':nemonico' => $tipoPermiso->getNemonico(),
        ':nombre' => $tipoPermiso->getNombre(),
      ]);
      $tipoPermiso->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE tipo_permiso SET nemonico = :nemonico, nombre = :nombre WHERE id = :id");
      $stmt->execute([
        ':id' => $tipoPermiso->getId(),
        ':nemonico' => $tipoPermiso->getNemonico(),
        ':nombre' => $tipoPermiso->getNombre(),
      ]);
    }

    return $tipoPermiso;
  }

  public function findById(int $id): ?TipoPermiso
  {
    $stmt = $this->db->prepare('SELECT * FROM tipo_permiso WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new TipoPermiso($row['id'], $row['nemonico'], $row['nombre']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM tipo_permiso ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new TipoPermiso($row['id'], $row['nemonico'], $row['nombre']), $rows ?: []);
  }
}
