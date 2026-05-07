<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Actividad.php';

use Core\Database;
use Entitys\Actividad;
use PDO;

class ActividadRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Actividad $actividad): Actividad
  {
    if ($actividad->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO actividades (deporte, precio, nivel, observacion, institucion) VALUES (:deporte, :precio, :nivel, :observacion, :institucion)");
      $stmt->execute([
        ':deporte' => $actividad->getDeporte()?->getId(),
        ':precio' => $actividad->getPrecio(),
        ':nivel' => $actividad->getNivel()?->getId(),
        ':observacion' => $actividad->getObservacion(),
        ':institucion' => $actividad->getInstitucion()?->getId(),
      ]);
      $actividad->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE actividades SET deporte = :deporte, precio = :precio, nivel = :nivel, observacion = :observacion, institucion = :institucion WHERE id = :id");
      $stmt->execute([
        ':id' => $actividad->getId(),
        ':deporte' => $actividad->getDeporte()?->getId(),
        ':precio' => $actividad->getPrecio(),
        ':nivel' => $actividad->getNivel()?->getId(),
        ':observacion' => $actividad->getObservacion(),
        ':institucion' => $actividad->getInstitucion()?->getId(),
      ]);
    }

    return $actividad;
  }

  public function findById(int $id): ?Actividad
  {
    $stmt = $this->db->prepare('SELECT * FROM actividades WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Actividad($row['id'], $row['deporte'], $row['precio'], $row['nivel'], $row['observacion'], $row['institucion']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM actividades ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Actividad($row['id'], $row['deporte'], $row['precio'], $row['nivel'], $row['observacion'], $row['institucion']), $rows ?: []);
  }
}
