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

  public function findById(int $id): ?Nivel
  {
    $stmt = $this->db->prepare('SELECT * FROM nivel WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM nivel ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_map(fn($row) => $this->map($row), $rows ?: []);
  }

  private function map(array $row): Nivel
  {
    return new Nivel((int)$row['id'], $row['nombre']);
  }
}
