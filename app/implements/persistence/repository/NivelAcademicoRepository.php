<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/NivelAcademico.php';

use Core\Database;
use Entitys\NivelAcademico;
use PDO;

class NivelAcademicoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function findById(int $id): ?NivelAcademico
  {
    $stmt = $this->db->prepare('SELECT * FROM nivel_academico WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM nivel_academico ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_map(fn($row) => $this->map($row), $rows ?: []);
  }

  private function map(array $row): NivelAcademico
  {
    return new NivelAcademico((int)$row['id'], $row['nombre'], $row['nemonico'] ?? null);
  }
}