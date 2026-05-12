<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/PerfilMedico.php';

use Core\Database;
use Entitys\PerfilMedico;
use PDO;

class PerfilMedicoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(PerfilMedico $perfilMedico): PerfilMedico
  {
    if ($perfilMedico->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO perfil_medico (fecha, persona, estado) VALUES (:fecha, :persona, :estado)");
      $stmt->execute([
        ':fecha' => $perfilMedico->getFecha(),
        ':persona' => $perfilMedico->getPersona()->getId(),
        ':estado' => $perfilMedico->getEstado()->getId()
      ]);
      $perfilMedico->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE perfil_medico SET fecha = :fecha, persona = :persona, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $perfilMedico->getId(),
        ':fecha' => $perfilMedico->getFecha(),
        ':persona' => $perfilMedico->getPersona()->getId(),
        ':estado' => $perfilMedico->getEstado()->getId()
      ]);
    }

    return $perfilMedico;
  }

  public function findById($id): ?PerfilMedico
  {
    $stmt = $this->db->prepare('SELECT * FROM perfil_medico WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new PerfilMedico($row['id'], $row['fecha'], $row['persona'], $row['estado']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM perfil_medico ORDER BY fecha DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new PerfilMedico($row['id'], $row['fecha'], $row['persona'], $row['estado']), $rows ?: []);
  }
}
