<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Genero.php';

use Core\Database;
use Entitys\Genero;
use PDO;

class GeneroRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Genero $genero): Genero
  {
    if ($genero->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO generos (nombre, nemonico) VALUES (:nombre, :nemonico)");
      $stmt->execute([
        ':nombre' => $genero->getNombre(),
        ':nemonico' => $genero->getNemonico()
      ]);
      $genero->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE generos SET nombre = :nombre, nemonico = :nemonico WHERE id = :id");
      $stmt->execute([
        ':id' => $genero->getId(),
        ':nombre' => $genero->getNombre(),
        ':nemonico' => $genero->getNemonico()
      ]);
    }

    return $genero;
  }

  public function findById(int $id): ?Genero
  {
    $stmt = $this->db->prepare("SELECT * FROM generos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new Genero($row['id'], $row['nombre'], $row['nemonico']);
    }

    return null;
  }
}