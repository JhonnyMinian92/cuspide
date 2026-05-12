<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/ActividadMatricula.php';

use Core\Database;
use Entitys\ActividadMatricula;
use PDO;

class ActividadMatriculaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(ActividadMatricula $actividadMatricula): ActividadMatricula
  {
    if ($actividadMatricula->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO actividades_matricula (actividad, matricula, modalidad, categoria_deportiva, horario, fecha_inicio, fecha_fin, estado) VALUES (:actividad, :matricula, :modalidad, :categoria_deportiva, :horario, :fecha_inicio, :fecha_fin, :estado)");
      $stmt->execute([
        ':actividad' => $actividadMatricula->getActividad()?->getId(),
        ':matricula' => $actividadMatricula->getMatricula()?->getId(),
        ':modalidad' => $actividadMatricula->getModalidad()?->getId(),
        ':categoria_deportiva' => $actividadMatricula->getCategoriaDeportiva()?->getId(),
        ':horario' => $actividadMatricula->getHorario()?->getId(),
        ':fecha_inicio' => $actividadMatricula->getFechaInicio(),
        ':fecha_fin' => $actividadMatricula->getFechaFin(),
        ':estado' => $actividadMatricula->getEstado()?->getId(),
      ]);
      $actividadMatricula->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE actividades_matricula SET actividad = :actividad, matricula = :matricula, modalidad = :modalidad, categoria_deportiva = :categoria_deportiva, horario = :horario, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $actividadMatricula->getId(),
        ':actividad' => $actividadMatricula->getActividad()?->getId(),
        ':matricula' => $actividadMatricula->getMatricula()?->getId(),
        ':modalidad' => $actividadMatricula->getModalidad()?->getId(),
        ':categoria_deportiva' => $actividadMatricula->getCategoriaDeportiva()?->getId(),
        ':horario' => $actividadMatricula->getHorario()?->getId(),
        ':fecha_inicio' => $actividadMatricula->getFechaInicio(),
        ':fecha_fin' => $actividadMatricula->getFechaFin(),
        ':estado' => $actividadMatricula->getEstado()?->getId(),
      ]);
    }

    return $actividadMatricula;
  }

  public function findById(int $id): ?ActividadMatricula
  {
    $stmt = $this->db->prepare('SELECT * FROM actividades_matricula WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new ActividadMatricula($row['id'], $row['actividad'], $row['matricula'], $row['modalidad'], $row['fecha_inicio'], $row['fecha_fin'], $row['estado'], $row['categoria_deportiva'] ?? null, $row['horario'] ?? null) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM actividades_matricula ORDER BY fecha_inicio DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new ActividadMatricula($row['id'], $row['actividad'], $row['matricula'], $row['modalidad'], $row['fecha_inicio'], $row['fecha_fin'], $row['estado'], $row['categoria_deportiva'] ?? null, $row['horario'] ?? null), $rows ?: []);
  }
}
