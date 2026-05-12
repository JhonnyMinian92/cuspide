<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Estado.php';

use Core\Database;
use Entitys\Estado;
use PDO;

class EstadoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Estado $estado): Estado
  {
    if ($estado->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO estados (nombre) VALUES (:nombre)");
      $stmt->execute([
        ':nombre' => $estado->getNombre()
      ]);
      $estado->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE estados SET nombre = :nombre WHERE id = :id");
      $stmt->execute([
        ':id' => $estado->getId(),
        ':nombre' => $estado->getNombre()
      ]);
    }

    return $estado;
  }

  public function findById(int $id): ?Estado
  {
    $stmt = $this->db->prepare("SELECT * FROM estados WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new Estado($row['id'], $row['nombre']);
    }

    return null;
  } 
}