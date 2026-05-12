<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Nacionalidad.php';

use Core\Database;
use Entitys\Nacionalidad;
use PDO;

class NacionalidadRepository
{

  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Nacionalidad $nacionalidad): Nacionalidad
  {
    if ($nacionalidad->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO nacionalidades (nombre, nemonico) VALUES (:nombre, :nemonico)");
      $stmt->execute([
        ':nombre' => $nacionalidad->getNombre(),
        ':nemonico' => $nacionalidad->getNemonico()
      ]);
      $nacionalidad->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE nacionalidades SET nombre = :nombre, nemonico = :nemonico WHERE id = :id");
      $stmt->execute([
        ':id' => $nacionalidad->getId(),
        ':nombre' => $nacionalidad->getNombre(),
        ':nemonico' => $nacionalidad->getNemonico()
      ]);
    }

    return $nacionalidad;
  }

  public function findById(int $id): ?Nacionalidad
  {
    $stmt = $this->db->prepare("SELECT * FROM nacionalidades WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new Nacionalidad($row['id'], $row['nombre'], $row['nemonico']);
    }

    return null;
  }
}