<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Ciudad.php';

use Core\Database;
use Entitys\Ciudad;
use PDO;

class CiudadRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Ciudad $ciudad): Ciudad
  {
    if ($ciudad->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO ciudades (nemonico, nombre, provincia) VALUES (:nemonico, :nombre, :provincia)");
      $stmt->execute([
        ':nemonico' => $ciudad->getNemonico(),
        ':nombre' => $ciudad->getNombre(),
        ':provincia' => $ciudad->getProvincia()
      ]);
      $ciudad->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE ciudades SET nemonico = :nemonico, nombre = :nombre, provincia = :provincia WHERE id = :id");
      $stmt->execute([
        ':id' => $ciudad->getId(),
        ':nemonico' => $ciudad->getNemonico(),
        ':nombre' => $ciudad->getNombre(),
        ':provincia' => $ciudad->getProvincia()
      ]);
    }

    return $ciudad;
  }

  public function findById(int $id): ?Ciudad
  {
    $stmt = $this->db->prepare("SELECT * FROM ciudades WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new Ciudad($row['id'], $row['nemonico'], $row['nombre'], $row['provincia']);
    }

    return null;
  }
}