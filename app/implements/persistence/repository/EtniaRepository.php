<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Etnia.php';

use Core\Database;
use Entitys\Etnia;
use PDO;

class EtniaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Etnia $etnia): Etnia
  {
    if ($etnia->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO etnias (nombre, nemonico) VALUES (:nombre, :nemonico)");
      $stmt->execute([
        ':nombre' => $etnia->getNombre(),
        ':nemonico' => $etnia->getNemonico()
      ]);
      $etnia->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE etnias SET nombre = :nombre, nemonico = :nemonico WHERE id = :id");
      $stmt->execute([
        ':id' => $etnia->getId(),
        ':nombre' => $etnia->getNombre(),
        ':nemonico' => $etnia->getNemonico()
      ]);
    }

    return $etnia;
  }

  public function findById(int $id): ?Etnia
  {
    $stmt = $this->db->prepare("SELECT * FROM etnias WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new Etnia($row['id'], $row['nombre'], $row['nemonico']);
    }

    return null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM etnias ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_map(fn($row) => new Etnia((int)$row['id'], $row['nombre'], $row['nemonico']), $rows ?: []);
  }
}