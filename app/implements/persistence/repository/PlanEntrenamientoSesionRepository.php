<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/PlanEntrenamientoSesion.php';

use Core\Database;
use Entitys\PlanEntrenamientoSesion;
use PDO;

class PlanEntrenamientoSesionRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(PlanEntrenamientoSesion $sesion): PlanEntrenamientoSesion
  {
    if ($sesion->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO plan_entrenamiento_sesiones (plan_entrenamiento, calendario, horario, numero_semana, dia_semana, fecha_programada, titulo, objetivo, duracion_minutos, intensidad, contenido, observacion, estado) VALUES (:plan_entrenamiento, :calendario, :horario, :numero_semana, :dia_semana, :fecha_programada, :titulo, :objetivo, :duracion_minutos, :intensidad, :contenido, :observacion, :estado)");
      $stmt->execute($this->params($sesion));
      $sesion->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE plan_entrenamiento_sesiones SET plan_entrenamiento = :plan_entrenamiento, calendario = :calendario, horario = :horario, numero_semana = :numero_semana, dia_semana = :dia_semana, fecha_programada = :fecha_programada, titulo = :titulo, objetivo = :objetivo, duracion_minutos = :duracion_minutos, intensidad = :intensidad, contenido = :contenido, observacion = :observacion, estado = :estado WHERE id = :id");
      $params = $this->params($sesion);
      $params[':id'] = $sesion->getId();
      $stmt->execute($params);
    }
    return $sesion;
  }

  public function findById(int $id): ?PlanEntrenamientoSesion
  {
    $stmt = $this->db->prepare('SELECT * FROM plan_entrenamiento_sesiones WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByPlan(int $planId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM plan_entrenamiento_sesiones WHERE plan_entrenamiento = :plan ORDER BY numero_semana ASC, dia_semana ASC, id ASC');
    $stmt->execute([':plan' => $planId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(PlanEntrenamientoSesion $sesion): array
  {
    return [
      ':plan_entrenamiento' => $sesion->getPlanEntrenamiento()?->getId(),
      ':calendario' => $sesion->getCalendario()?->getId(),
      ':horario' => $sesion->getHorario()?->getId(),
      ':numero_semana' => $sesion->getNumeroSemana(),
      ':dia_semana' => $sesion->getDiaSemana(),
      ':fecha_programada' => $sesion->getFechaProgramada(),
      ':titulo' => $sesion->getTitulo(),
      ':objetivo' => $sesion->getObjetivo(),
      ':duracion_minutos' => $sesion->getDuracionMinutos(),
      ':intensidad' => $sesion->getIntensidad(),
      ':contenido' => $sesion->getContenido(),
      ':observacion' => $sesion->getObservacion(),
      ':estado' => $sesion->getEstado()?->getId(),
    ];
  }

  private function map(array $row): PlanEntrenamientoSesion
  {
    return new PlanEntrenamientoSesion($row['id'], $row['plan_entrenamiento'], $row['calendario'] ?? null, $row['horario'] ?? null, (int) $row['numero_semana'], $row['dia_semana'] === null ? null : (int) $row['dia_semana'], $row['fecha_programada'] ?? null, $row['titulo'], $row['objetivo'], (int) $row['duracion_minutos'], $row['intensidad'], $row['contenido'], $row['observacion'] ?? null, $row['estado']);
  }
}
