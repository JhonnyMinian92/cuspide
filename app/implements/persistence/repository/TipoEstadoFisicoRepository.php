<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/TipoEstadoFisico.php';

use Core\Database;
use Entitys\TipoEstadoFisico;
use PDO;

class TipoEstadoFisicoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(TipoEstadoFisico $tipoEstadoFisico): TipoEstadoFisico
  {
    if ($tipoEstadoFisico->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO tipo_estado_fisico (nemonico, nombre) VALUES (:nemonico, :nombre)");
      $stmt->execute([
        ':nemonico' => $tipoEstadoFisico->getNemonico(),
        ':nombre' => $tipoEstadoFisico->getNombre(),
      ]);
      $tipoEstadoFisico->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE tipo_estado_fisico SET nemonico = :nemonico, nombre = :nombre WHERE id = :id");
      $stmt->execute([
        ':id' => $tipoEstadoFisico->getId(),
        ':nemonico' => $tipoEstadoFisico->getNemonico(),
        ':nombre' => $tipoEstadoFisico->getNombre(),
      ]);
    }

    return $tipoEstadoFisico;
  }

  public function findById(int $id): ?TipoEstadoFisico
  {
    $stmt = $this->db->prepare('SELECT * FROM tipo_estado_fisico WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new TipoEstadoFisico($row['id'], $row['nemonico'], $row['nombre']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM tipo_estado_fisico ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new TipoEstadoFisico($row['id'], $row['nemonico'], $row['nombre']), $rows ?: []);
  }
}
