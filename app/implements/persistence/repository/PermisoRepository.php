<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Permiso.php';

use Core\Database;
use Entitys\Permiso;
use PDO;

class PermisoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Permiso $permiso): Permiso
  {
    if ($permiso->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO permisos (tipo, persona, estado, fechaInicio, fechaFin, descripcion) VALUES (:tipo, :persona, :estado, :fechaInicio, :fechaFin, :descripcion)");
      $stmt->execute([
        ':tipo' => $permiso->getTipo()?->getId(),
        ':persona' => $permiso->getPersona()?->getId(),
        ':estado' => $permiso->getEstado()?->getId(),
        ':fechaInicio' => $permiso->getFechaInicio(),
        ':fechaFin' => $permiso->getFechaFin(),
        ':descripcion' => $permiso->getDescripcion(),
      ]);
      $permiso->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE permisos SET tipo = :tipo, persona = :persona, estado = :estado, fechaInicio = :fechaInicio, fechaFin = :fechaFin, descripcion = :descripcion WHERE id = :id");
      $stmt->execute([
        ':id' => $permiso->getId(),
        ':tipo' => $permiso->getTipo()?->getId(),
        ':persona' => $permiso->getPersona()?->getId(),
        ':estado' => $permiso->getEstado()?->getId(),
        ':fechaInicio' => $permiso->getFechaInicio(),
        ':fechaFin' => $permiso->getFechaFin(),
        ':descripcion' => $permiso->getDescripcion(),
      ]);
    }

    return $permiso;
  }

  public function findById(int $id): ?Permiso
  {
    $stmt = $this->db->prepare('SELECT * FROM permisos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Permiso($row['id'], $row['tipo'], $row['persona'], $row['estado'], $row['fechaInicio'], $row['fechaFin'], $row['descripcion']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM permisos ORDER BY fechaInicio DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Permiso($row['id'], $row['tipo'], $row['persona'], $row['estado'], $row['fechaInicio'], $row['fechaFin'], $row['descripcion']), $rows ?: []);
  }
}
