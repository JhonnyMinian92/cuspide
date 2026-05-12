<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/TipoPersona.php';

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
      $stmt = $this->db->prepare("INSERT INTO tipo_persona (nombre, nemonico) VALUES (:nombre, :nemonico)");
      $stmt->execute([
        ':nombre' => $tipoPersona->getNombre(),
        ':nemonico' => $tipoPersona->getNemonico()
      ]);
      $tipoPersona->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE tipo_persona SET nombre = :nombre, nemonico = :nemonico WHERE id = :id");
      $stmt->execute([
        ':id' => $tipoPersona->getId(),
        ':nombre' => $tipoPersona->getNombre(),
        ':nemonico' => $tipoPersona->getNemonico()
      ]);
    }

    return $tipoPersona;
  }

  public function findById(int $id): ?TipoPersona
  {
    $stmt = $this->db->prepare("SELECT * FROM tipo_persona WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new TipoPersona($row['id'], $row['nombre'], $row['nemonico']);
    }

    return null;
  }
}