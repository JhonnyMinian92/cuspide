<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Clase.php';

use Core\Database;
use Entitys\Clase;
use PDO;

class ClaseRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Clase $clase): Clase
  {
    if ($clase->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO clases (asignatura, precio, nivel, observacion, institucion) VALUES (:asignatura, :precio, :nivel, :observacion, :institucion)");
      $stmt->execute([
        ':asignatura' => $clase->getAsignatura()?->getId(),
        ':precio' => $clase->getPrecio(),
        ':nivel' => $clase->getNivel()?->getId(),
        ':observacion' => $clase->getObservacion(),
        ':institucion' => $clase->getInstitucion()?->getId(),
      ]);
      $clase->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE clases SET asignatura = :asignatura, precio = :precio, nivel = :nivel, observacion = :observacion, institucion = :institucion WHERE id = :id");
      $stmt->execute([
        ':id' => $clase->getId(),
        ':asignatura' => $clase->getAsignatura()?->getId(),
        ':precio' => $clase->getPrecio(),
        ':nivel' => $clase->getNivel()?->getId(),
        ':observacion' => $clase->getObservacion(),
        ':institucion' => $clase->getInstitucion()?->getId(),
      ]);
    }

    return $clase;
  }

  public function findById(int $id): ?Clase
  {
    $stmt = $this->db->prepare('SELECT * FROM clases WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Clase($row['id'], $row['asignatura'], $row['precio'], $row['nivel'], $row['observacion'], $row['institucion']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM clases ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Clase($row['id'], $row['asignatura'], $row['precio'], $row['nivel'], $row['observacion'], $row['institucion']), $rows ?: []);
  }
}
