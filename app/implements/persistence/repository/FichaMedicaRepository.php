<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/FichaMedica.php';

use Core\Database;
use Entitys\FichaMedica;
use PDO;

class FichaMedicaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(FichaMedica $fichaMedica): FichaMedica
  {
    if ($fichaMedica->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO ficha_medica (fecha, perfil_medico, estado) VALUES (:fecha, :perfil_medico, :estado)");
      $stmt->execute([
        ':fecha' => $fichaMedica->getFecha(),
        ':perfil_medico' => $fichaMedica->getPerfilMedico()?->getId(),
        ':estado' => $fichaMedica->getEstado()?->getId(),
      ]);
      $fichaMedica->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE ficha_medica SET fecha = :fecha, perfil_medico = :perfil_medico, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $fichaMedica->getId(),
        ':fecha' => $fichaMedica->getFecha(),
        ':perfil_medico' => $fichaMedica->getPerfilMedico()?->getId(),
        ':estado' => $fichaMedica->getEstado()?->getId(),
      ]);
    }

    return $fichaMedica;
  }

  public function findById(int $id): ?FichaMedica
  {
    $stmt = $this->db->prepare('SELECT * FROM ficha_medica WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new FichaMedica($row['id'], $row['fecha'], $row['perfil_medico'], $row['estado']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM ficha_medica ORDER BY fecha DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new FichaMedica($row['id'], $row['fecha'], $row['perfil_medico'], $row['estado']), $rows ?: []);
  }
}
