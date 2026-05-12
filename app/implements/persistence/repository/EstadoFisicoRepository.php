<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/EstadoFisico.php';

use Core\Database;
use Entitys\EstadoFisico;
use PDO;

class EstadoFisicoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(EstadoFisico $estadoFisico): EstadoFisico
  {
    if ($estadoFisico->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO estado_fisico (perfil_medico, tipo, descripcion) VALUES (:perfil_medico, :tipo, :descripcion)");
      $stmt->execute([
        ':perfil_medico' => $estadoFisico->getPerfilMedico()?->getId(),
        ':tipo' => $estadoFisico->getTipo()?->getId(),
        ':descripcion' => $estadoFisico->getDescripcion(),
      ]);
      $estadoFisico->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE estado_fisico SET perfil_medico = :perfil_medico, tipo = :tipo, descripcion = :descripcion WHERE id = :id");
      $stmt->execute([
        ':id' => $estadoFisico->getId(),
        ':perfil_medico' => $estadoFisico->getPerfilMedico()?->getId(),
        ':tipo' => $estadoFisico->getTipo()?->getId(),
        ':descripcion' => $estadoFisico->getDescripcion(),
      ]);
    }

    return $estadoFisico;
  }

  public function findById(int $id): ?EstadoFisico
  {
    $stmt = $this->db->prepare('SELECT * FROM estado_fisico WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new EstadoFisico($row['id'], $row['perfil_medico'], $row['tipo'], $row['descripcion']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM estado_fisico ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new EstadoFisico($row['id'], $row['perfil_medico'], $row['tipo'], $row['descripcion']), $rows ?: []);
  }
}
