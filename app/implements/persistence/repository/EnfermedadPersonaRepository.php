<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/EnfermedadPersona.php';

use Core\Database;
use Entitys\EnfermedadPersona;
use PDO;

class EnfermedadPersonaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(EnfermedadPersona $enfermedadPersona): EnfermedadPersona
  {
    if ($enfermedadPersona->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO enfermedad_persona (enfermedad, medicacion, descripcion) VALUES (:enfermedad, :medicacion, :descripcion)");
      $stmt->execute([
        ':enfermedad' => $enfermedadPersona->getEnfermedad()?->getId(),
        ':medicacion' => $enfermedadPersona->getMedicacion(),
        ':descripcion' => $enfermedadPersona->getDescripcion(),
      ]);
      $enfermedadPersona->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE enfermedad_persona SET enfermedad = :enfermedad, medicacion = :medicacion, descripcion = :descripcion WHERE id = :id");
      $stmt->execute([
        ':id' => $enfermedadPersona->getId(),
        ':enfermedad' => $enfermedadPersona->getEnfermedad()?->getId(),
        ':medicacion' => $enfermedadPersona->getMedicacion(),
        ':descripcion' => $enfermedadPersona->getDescripcion(),
      ]);
    }

    return $enfermedadPersona;
  }

  public function findById(int $id): ?EnfermedadPersona
  {
    $stmt = $this->db->prepare('SELECT * FROM enfermedad_persona WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new EnfermedadPersona($row['id'], $row['enfermedad'], $row['medicacion'], $row['descripcion']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM enfermedad_persona ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new EnfermedadPersona($row['id'], $row['enfermedad'], $row['medicacion'], $row['descripcion']), $rows ?: []);
  }
}
