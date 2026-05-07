<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Modalidad.php';

use Core\Database;
use Entitys\Modalidad;
use PDO;

class ModalidadRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Modalidad $modalidad): Modalidad
  {
    if ($modalidad->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO modalidades (nemonico, nombre) VALUES (:nemonico, :nombre)");
      $stmt->execute([
        ':nemonico' => $modalidad->getNemonico(),
        ':nombre' => $modalidad->getNombre(),
      ]);
      $modalidad->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE modalidades SET nemonico = :nemonico, nombre = :nombre WHERE id = :id");
      $stmt->execute([
        ':id' => $modalidad->getId(),
        ':nemonico' => $modalidad->getNemonico(),
        ':nombre' => $modalidad->getNombre(),
      ]);
    }

    return $modalidad;
  }

  public function findById(int $id): ?Modalidad
  {
    $stmt = $this->db->prepare('SELECT * FROM modalidades WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Modalidad($row['id'], $row['nemonico'], $row['nombre']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM modalidades ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Modalidad($row['id'], $row['nemonico'], $row['nombre']), $rows ?: []);
  }
}
