<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';

use Core\Database;
use Entitys\TipoPersona;
use PDO;

class TipoPersonaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(TipoPersona $tipoPersona): TipoPersona
  {
    if ($tipoPersona->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO tipos_persona (nombre) VALUES (:nombre)");
      $stmt->execute([
        ':nombre' => $tipoPersona->getNombre()
      ]);
      $tipoPersona->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE tipos_persona SET nombre = :nombre WHERE id = :id");
      $stmt->execute([
        ':id' => $tipoPersona->getId(),
        ':nombre' => $tipoPersona->getNombre()
      ]);
    }

    return $tipoPersona;
  }

  public function findById(int $id): ?TipoPersona
  {
    $stmt = $this->db->prepare("SELECT * FROM tipos_persona WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new TipoPersona($row['id'], $row['nombre']);
    }

    return null;
  }
}
