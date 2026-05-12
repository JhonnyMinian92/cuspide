<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Enfermedad.php';

use Core\Database;
use Entitys\Enfermedad;
use PDO;

class EnfermedadRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Enfermedad $enfermedad): Enfermedad
  {
    if ($enfermedad->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO enfermedades (tipo, nombre, nemonico, nivel) VALUES (:tipo, :nombre, :nemonico, :nivel)");
      $stmt->execute([
        ':tipo' => $enfermedad->getTipo()?->getId(),
        ':nombre' => $enfermedad->getNombre(),
        ':nemonico' => $enfermedad->getNemonico(),
        ':nivel' => $enfermedad->getNivel()?->getId(),
      ]);
      $enfermedad->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE enfermedades SET tipo = :tipo, nombre = :nombre, nemonico = :nemonico, nivel = :nivel WHERE id = :id");
      $stmt->execute([
        ':id' => $enfermedad->getId(),
        ':tipo' => $enfermedad->getTipo()?->getId(),
        ':nombre' => $enfermedad->getNombre(),
        ':nemonico' => $enfermedad->getNemonico(),
        ':nivel' => $enfermedad->getNivel()?->getId(),
      ]);
    }

    return $enfermedad;
  }

  public function findById(int $id): ?Enfermedad
  {
    $stmt = $this->db->prepare('SELECT * FROM enfermedades WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Enfermedad($row['id'], $row['tipo'], $row['nombre'], $row['nemonico'], $row['nivel']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM enfermedades ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Enfermedad($row['id'], $row['tipo'], $row['nombre'], $row['nemonico'], $row['nivel']), $rows ?: []);
  }
}
