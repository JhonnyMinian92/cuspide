<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/EstadoPermiso.php';

use Core\Database;
use Entitys\EstadoPermiso;
use PDO;

class EstadoPermisoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(EstadoPermiso $estadoPermiso): EstadoPermiso
  {
    if ($estadoPermiso->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO estado_permiso (nemonico, nombre) VALUES (:nemonico, :nombre)");
      $stmt->execute([
        ':nemonico' => $estadoPermiso->getNemonico(),
        ':nombre' => $estadoPermiso->getNombre(),
      ]);
      $estadoPermiso->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE estado_permiso SET nemonico = :nemonico, nombre = :nombre WHERE id = :id");
      $stmt->execute([
        ':id' => $estadoPermiso->getId(),
        ':nemonico' => $estadoPermiso->getNemonico(),
        ':nombre' => $estadoPermiso->getNombre(),
      ]);
    }

    return $estadoPermiso;
  }

  public function findById(int $id): ?EstadoPermiso
  {
    $stmt = $this->db->prepare('SELECT * FROM estado_permiso WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new EstadoPermiso($row['id'], $row['nemonico'], $row['nombre']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM estado_permiso ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new EstadoPermiso($row['id'], $row['nemonico'], $row['nombre']), $rows ?: []);
  }
}
