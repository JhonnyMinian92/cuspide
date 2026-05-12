<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/TipoEnfermedad.php';

use Core\Database;
use Entitys\TipoEnfermedad;
use PDO;

class TipoEnfermedadRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(TipoEnfermedad $tipoEnfermedad): TipoEnfermedad
  {
    if ($tipoEnfermedad->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO tipo_enfermedad (nemonico, nombre) VALUES (:nemonico, :nombre)");
      $stmt->execute([
        ':nemonico' => $tipoEnfermedad->getNemonico(),
        ':nombre' => $tipoEnfermedad->getNombre(),
      ]);
      $tipoEnfermedad->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE tipo_enfermedad SET nemonico = :nemonico, nombre = :nombre WHERE id = :id");
      $stmt->execute([
        ':id' => $tipoEnfermedad->getId(),
        ':nemonico' => $tipoEnfermedad->getNemonico(),
        ':nombre' => $tipoEnfermedad->getNombre(),
      ]);
    }

    return $tipoEnfermedad;
  }

  public function findById(int $id): ?TipoEnfermedad
  {
    $stmt = $this->db->prepare('SELECT * FROM tipo_enfermedad WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new TipoEnfermedad($row['id'], $row['nemonico'], $row['nombre']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM tipo_enfermedad ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new TipoEnfermedad($row['id'], $row['nemonico'], $row['nombre']), $rows ?: []);
  }
}
