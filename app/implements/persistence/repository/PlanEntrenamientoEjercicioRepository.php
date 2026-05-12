<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/PlanEntrenamientoEjercicio.php';

use Core\Database;
use Entitys\PlanEntrenamientoEjercicio;
use PDO;

class PlanEntrenamientoEjercicioRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(PlanEntrenamientoEjercicio $ejercicio): PlanEntrenamientoEjercicio
  {
    if ($ejercicio->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO plan_entrenamiento_ejercicios (sesion, orden, nombre, descripcion, series, repeticiones, duracion_segundos, descanso_segundos, intensidad, estado) VALUES (:sesion, :orden, :nombre, :descripcion, :series, :repeticiones, :duracion_segundos, :descanso_segundos, :intensidad, :estado)");
      $stmt->execute($this->params($ejercicio));
      $ejercicio->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE plan_entrenamiento_ejercicios SET sesion = :sesion, orden = :orden, nombre = :nombre, descripcion = :descripcion, series = :series, repeticiones = :repeticiones, duracion_segundos = :duracion_segundos, descanso_segundos = :descanso_segundos, intensidad = :intensidad, estado = :estado WHERE id = :id");
      $params = $this->params($ejercicio);
      $params[':id'] = $ejercicio->getId();
      $stmt->execute($params);
    }
    return $ejercicio;
  }

  public function findById(int $id): ?PlanEntrenamientoEjercicio
  {
    $stmt = $this->db->prepare('SELECT * FROM plan_entrenamiento_ejercicios WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findBySesion(int $sesionId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM plan_entrenamiento_ejercicios WHERE sesion = :sesion ORDER BY orden ASC, id ASC');
    $stmt->execute([':sesion' => $sesionId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(PlanEntrenamientoEjercicio $ejercicio): array
  {
    return [
      ':sesion' => $ejercicio->getSesion()?->getId(),
      ':orden' => $ejercicio->getOrden(),
      ':nombre' => $ejercicio->getNombre(),
      ':descripcion' => $ejercicio->getDescripcion(),
      ':series' => $ejercicio->getSeries(),
      ':repeticiones' => $ejercicio->getRepeticiones(),
      ':duracion_segundos' => $ejercicio->getDuracionSegundos(),
      ':descanso_segundos' => $ejercicio->getDescansoSegundos(),
      ':intensidad' => $ejercicio->getIntensidad(),
      ':estado' => $ejercicio->getEstado()?->getId(),
    ];
  }

  private function map(array $row): PlanEntrenamientoEjercicio
  {
    return new PlanEntrenamientoEjercicio($row['id'], $row['sesion'], (int) $row['orden'], $row['nombre'], $row['descripcion'], (int) $row['series'], $row['repeticiones'], (int) $row['duracion_segundos'], (int) $row['descanso_segundos'], $row['intensidad'], $row['estado']);
  }
}
