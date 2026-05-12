<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Alergia.php';

use Core\Database;
use Entitys\Alergia;
use PDO;

class AlergiaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Alergia $alergia): Alergia
  {
    if ($alergia->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO alergias (tipo, nombre, descripcion, nivel, ficha_medica) VALUES (:tipo, :nombre, :descripcion, :nivel, :ficha_medica)");
      $stmt->execute([
        ':tipo' => $alergia->getTipo()?->getId(),
        ':nombre' => $alergia->getNombre(),
        ':descripcion' => $alergia->getDescripcion(),
        ':nivel' => $alergia->getNivel()?->getId(),
        ':ficha_medica' => $alergia->getFichaMedica()?->getId(),
      ]);
      $alergia->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE alergias SET tipo = :tipo, nombre = :nombre, descripcion = :descripcion, nivel = :nivel, ficha_medica = :ficha_medica WHERE id = :id");
      $stmt->execute([
        ':id' => $alergia->getId(),
        ':tipo' => $alergia->getTipo()?->getId(),
        ':nombre' => $alergia->getNombre(),
        ':descripcion' => $alergia->getDescripcion(),
        ':nivel' => $alergia->getNivel()?->getId(),
        ':ficha_medica' => $alergia->getFichaMedica()?->getId(),
      ]);
    }

    return $alergia;
  }

  public function findById(int $id): ?Alergia
  {
    $stmt = $this->db->prepare('SELECT * FROM alergias WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Alergia($row['id'], $row['tipo'], $row['nombre'], $row['descripcion'], $row['nivel'], $row['ficha_medica']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM alergias ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Alergia($row['id'], $row['tipo'], $row['nombre'], $row['descripcion'], $row['nivel'], $row['ficha_medica']), $rows ?: []);
  }
}
