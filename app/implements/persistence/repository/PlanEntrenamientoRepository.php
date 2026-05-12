<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/PlanEntrenamiento.php';

use Core\Database;
use Entitys\PlanEntrenamiento;
use PDO;

class PlanEntrenamientoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(PlanEntrenamiento $plan): PlanEntrenamiento
  {
    if ($plan->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO planes_entrenamiento (nombre, objetivo, descripcion, matricula, clase, actividad, categoria_deportiva, entrenador, institucion, nivel, fecha_inicio, fecha_fin, version, estado) VALUES (:nombre, :objetivo, :descripcion, :matricula, :clase, :actividad, :categoria_deportiva, :entrenador, :institucion, :nivel, :fecha_inicio, :fecha_fin, :version, :estado)");
      $stmt->execute($this->params($plan));
      $plan->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE planes_entrenamiento SET nombre = :nombre, objetivo = :objetivo, descripcion = :descripcion, matricula = :matricula, clase = :clase, actividad = :actividad, categoria_deportiva = :categoria_deportiva, entrenador = :entrenador, institucion = :institucion, nivel = :nivel, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, version = :version, estado = :estado WHERE id = :id");
      $params = $this->params($plan);
      $params[':id'] = $plan->getId();
      $stmt->execute($params);
    }
    return $plan;
  }

  public function findById(int $id): ?PlanEntrenamiento
  {
    $stmt = $this->db->prepare('SELECT * FROM planes_entrenamiento WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByMatricula(int $matriculaId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM planes_entrenamiento WHERE matricula = :matricula ORDER BY fecha_inicio DESC');
    $stmt->execute([':matricula' => $matriculaId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  public function findByClase(int $claseId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM planes_entrenamiento WHERE clase = :clase ORDER BY fecha_inicio DESC');
    $stmt->execute([':clase' => $claseId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  public function findByActividad(int $actividadId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM planes_entrenamiento WHERE actividad = :actividad ORDER BY fecha_inicio DESC');
    $stmt->execute([':actividad' => $actividadId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM planes_entrenamiento ORDER BY fecha_inicio DESC');
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(PlanEntrenamiento $plan): array
  {
    return [
      ':nombre' => $plan->getNombre(),
      ':objetivo' => $plan->getObjetivo(),
      ':descripcion' => $plan->getDescripcion(),
      ':matricula' => $plan->getMatricula()?->getId(),
      ':clase' => $plan->getClase()?->getId(),
      ':actividad' => $plan->getActividad()?->getId(),
      ':categoria_deportiva' => $plan->getCategoriaDeportiva()?->getId(),
      ':entrenador' => $plan->getEntrenador()?->getId(),
      ':institucion' => $plan->getInstitucion()?->getId(),
      ':nivel' => $plan->getNivel()?->getId(),
      ':fecha_inicio' => $plan->getFechaInicio(),
      ':fecha_fin' => $plan->getFechaFin(),
      ':version' => $plan->getVersion(),
      ':estado' => $plan->getEstado()?->getId(),
    ];
  }

  private function map(array $row): PlanEntrenamiento
  {
    return new PlanEntrenamiento($row['id'], $row['nombre'], $row['objetivo'], $row['descripcion'] ?? null, $row['matricula'] ?? null, $row['clase'] ?? null, $row['actividad'] ?? null, $row['categoria_deportiva'] ?? null, $row['entrenador'] ?? null, $row['institucion'], $row['nivel'] ?? null, $row['fecha_inicio'], $row['fecha_fin'], (int) $row['version'], $row['estado']);
  }
}
