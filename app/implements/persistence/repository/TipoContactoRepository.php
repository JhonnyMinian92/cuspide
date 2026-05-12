<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/TipoContacto.php';

use Core\Database;
use Entitys\TipoContacto;
use PDO;

class TipoContactoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(TipoContacto $tipoContacto): TipoContacto
  {
    if ($tipoContacto->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO tipos_contacto (nombre, nemonico) VALUES (:nombre, :nemonico)");
      $stmt->execute([
        ':nombre' => $tipoContacto->getNombre(),
        ':nemonico' => $tipoContacto->getNemonico()
      ]);
      $tipoContacto->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE tipos_contacto SET nombre = :nombre, nemonico = :nemonico WHERE id = :id");
      $stmt->execute([
        ':id' => $tipoContacto->getId(),
        ':nombre' => $tipoContacto->getNombre(),
        ':nemonico' => $tipoContacto->getNemonico()
      ]);
    }

    return $tipoContacto;
  }

  public function findById(int $id): ?TipoContacto
  {
    $stmt = $this->db->prepare("SELECT * FROM tipos_contacto WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new TipoContacto($row['id'], $row['nombre'], $row['nemonico']);
    }

    return null;
  }
}