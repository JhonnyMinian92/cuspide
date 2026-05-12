<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/TipoAlergia.php';

use Core\Database;
use Entitys\TipoAlergia;
use PDO;

class TipoAlergiaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(TipoAlergia $tipoAlergia): TipoAlergia
  {
    if ($tipoAlergia->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO tipo_alergias (nemonico, nombre) VALUES (:nemonico, :nombre)");
      $stmt->execute([
        ':nemonico' => $tipoAlergia->getNemonico(),
        ':nombre' => $tipoAlergia->getNombre(),
      ]);
      $tipoAlergia->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE tipo_alergias SET nemonico = :nemonico, nombre = :nombre WHERE id = :id");
      $stmt->execute([
        ':id' => $tipoAlergia->getId(),
        ':nemonico' => $tipoAlergia->getNemonico(),
        ':nombre' => $tipoAlergia->getNombre(),
      ]);
    }

    return $tipoAlergia;
  }

  public function findById(int $id): ?TipoAlergia
  {
    $stmt = $this->db->prepare('SELECT * FROM tipo_alergias WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new TipoAlergia($row['id'], $row['nemonico'], $row['nombre']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM tipo_alergias ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new TipoAlergia($row['id'], $row['nemonico'], $row['nombre']), $rows ?: []);
  }
}
