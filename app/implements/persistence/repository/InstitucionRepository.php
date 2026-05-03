<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';

use Core\Database;
use Entitys\Institucion;
use PDO;

class InstitucionRepository
{

  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Institucion $institucion): Institucion
  {
    if ($institucion->getId() === null) {
      $stmt = $this->db->prepare("
                INSERT INTO instituciones (ruc, razon_social, nombre, logotipo, estado)
                VALUES (:ruc, :razon_social, :nombre, :logotipo, :estado)
            ");

      $stmt->execute([
        ':ruc' => $institucion->getRuc(),
        ':razon_social' => $institucion->getRazonSocial(),
        ':nombre' => $institucion->getNombre(),
        ':logotipo' => $institucion->getLogotipo(),
        ':estado' => $institucion->getEstado()->getId()
      ]);

      $institucion->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("
                UPDATE instituciones 
                SET ruc = :ruc, razon_social = :razon_social, nombre = :nombre, logotipo = :logotipo, estado = :estado
                WHERE id = :id
            ");

      $stmt->execute([
        ':id' => $institucion->getId(),
        ':ruc' => $institucion->getRuc(),
        ':razon_social' => $institucion->getRazonSocial(),
        ':nombre' => $institucion->getNombre(),
        ':logotipo' => $institucion->getLogotipo(),
        ':estado' => $institucion->getEstado()->getId()
      ]);
    }

    return $institucion;
  }

  public function findById(int $id): ?Institucion
  {
    $stmt = $this->db->prepare("SELECT * FROM instituciones WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $data = $stmt->fetch();

    if ($data) {
      return new Institucion(
        $data['id'],
        $data['ruc'],
        $data['razon_social'],
        $data['nombre'],
        $data['logotipo'],
        $data['estado']
      );
    }

    return null;
  }
}
