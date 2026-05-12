<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Operadora.php';

use Core\Database;
use Entitys\Operadora;
use PDO;

class OperadoraRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Operadora $operadora): Operadora
  {
    if ($operadora->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO operadoras (nombre, nemonico) VALUES (:nombre, :nemonico)");
      $stmt->execute([
        ':nombre' => $operadora->getNombre(),
        ':nemonico' => $operadora->getNemonico()
      ]);
      $operadora->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE operadoras SET nombre = :nombre, nemonico = :nemonico WHERE id = :id");
      $stmt->execute([
        ':id' => $operadora->getId(),
        ':nombre' => $operadora->getNombre(),
        ':nemonico' => $operadora->getNemonico()
      ]);
    }

    return $operadora;
  }

  public function findById(int $id): ?Operadora
  {
    $stmt = $this->db->prepare("SELECT * FROM operadoras WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new Operadora($row['id'], $row['nombre'], $row['nemonico']);
    }

    return null;
  }
}