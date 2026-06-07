<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Deporte.php';

use Core\Database;
use Entitys\Deporte;
use PDO;

class DeporteRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Deporte $deporte): Deporte
  {
    if ($deporte->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO deportes (nombre, descripcion, institucion, estado) VALUES (:nombre, :descripcion, :institucion, :estado)");
      $stmt->execute([
        ':nombre' => $deporte->getNombre(),
        ':descripcion' => $deporte->getDescripcion(),
        ':institucion' => $deporte->getInstitucion()?->getId(),
        ':estado' => $deporte->getEstado()?->getId(),
      ]);
      $deporte->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE deportes SET nombre = :nombre, descripcion = :descripcion, institucion = :institucion, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $deporte->getId(),
        ':nombre' => $deporte->getNombre(),
        ':descripcion' => $deporte->getDescripcion(),
        ':institucion' => $deporte->getInstitucion()?->getId(),
        ':estado' => $deporte->getEstado()?->getId(),
      ]);
    }

    return $deporte;
  }

  public function findById(int $id): ?Deporte
  {
    $stmt = $this->db->prepare('SELECT * FROM deportes WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Deporte($row['id'], $row['nombre'], $row['descripcion'], $row['institucion'], $row['estado']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM deportes ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Deporte($row['id'], $row['nombre'], $row['descripcion'], $row['institucion'], $row['estado']), $rows ?: []);
  }

  public function findAllActive(): array
  {
    $stmt = $this->db->query('SELECT * FROM deportes WHERE estado = 1 ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    return array_map(fn($row) => new Deporte($row['id'], $row['nombre'], $row['descripcion'], $row['institucion'], $row['estado']), $rows ?: []);
  }
}
