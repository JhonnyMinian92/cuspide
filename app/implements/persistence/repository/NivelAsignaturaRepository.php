<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/NivelAsignatura.php';

use Core\Database;
use Entitys\NivelAsignatura;
use PDO;

class NivelAsignaturaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(NivelAsignatura $nivelAsignatura): NivelAsignatura
  {
    if ($nivelAsignatura->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO nivel_asignatura (nemonico, nombre) VALUES (:nemonico, :nombre)");
      $stmt->execute([
        ':nemonico' => $nivelAsignatura->getNemonico(),
        ':nombre' => $nivelAsignatura->getNombre(),
      ]);
      $nivelAsignatura->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE nivel_asignatura SET nemonico = :nemonico, nombre = :nombre WHERE id = :id");
      $stmt->execute([
        ':id' => $nivelAsignatura->getId(),
        ':nemonico' => $nivelAsignatura->getNemonico(),
        ':nombre' => $nivelAsignatura->getNombre(),
      ]);
    }

    return $nivelAsignatura;
  }

  public function findById(int $id): ?NivelAsignatura
  {
    $stmt = $this->db->prepare('SELECT * FROM nivel_asignatura WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new NivelAsignatura($row['id'], $row['nemonico'], $row['nombre']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM nivel_asignatura ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new NivelAsignatura($row['id'], $row['nemonico'], $row['nombre']), $rows ?: []);
  }
}
