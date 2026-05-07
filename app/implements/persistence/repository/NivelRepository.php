<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Nivel.php';

use Core\Database;
use Entitys\Nivel;
use PDO;

class NivelRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Nivel $nivel): Nivel
  {
    if ($nivel->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO nivel (nemonico, nombre) VALUES (:nemonico, :nombre)");
      $stmt->execute([
        ':nemonico' => $nivel->getNemonico(),
        ':nombre' => $nivel->getNombre(),
      ]);
      $nivel->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE nivel SET nemonico = :nemonico, nombre = :nombre WHERE id = :id");
      $stmt->execute([
        ':id' => $nivel->getId(),
        ':nemonico' => $nivel->getNemonico(),
        ':nombre' => $nivel->getNombre(),
      ]);
    }

    return $nivel;
  }

  public function findById(int $id): ?Nivel
  {
    $stmt = $this->db->prepare('SELECT * FROM nivel WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Nivel($row['id'], $row['nemonico'], $row['nombre']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM nivel ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Nivel($row['id'], $row['nemonico'], $row['nombre']), $rows ?: []);
  }
}
