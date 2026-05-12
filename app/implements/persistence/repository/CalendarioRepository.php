<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Calendario.php';

use Core\Database;
use Entitys\Calendario;
use PDO;

class CalendarioRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Calendario $calendario): Calendario
  {
    if ($calendario->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO calendarios (titulo, tipo, institucion, horario, clase, actividad, plan_entrenamiento, entrenador, fecha_inicio, fecha_fin, ubicacion, cupo_maximo, observacion, estado) VALUES (:titulo, :tipo, :institucion, :horario, :clase, :actividad, :plan_entrenamiento, :entrenador, :fecha_inicio, :fecha_fin, :ubicacion, :cupo_maximo, :observacion, :estado)");
      $stmt->execute($this->params($calendario));
      $calendario->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE calendarios SET titulo = :titulo, tipo = :tipo, institucion = :institucion, horario = :horario, clase = :clase, actividad = :actividad, plan_entrenamiento = :plan_entrenamiento, entrenador = :entrenador, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, ubicacion = :ubicacion, cupo_maximo = :cupo_maximo, observacion = :observacion, estado = :estado WHERE id = :id");
      $params = $this->params($calendario);
      $params[':id'] = $calendario->getId();
      $stmt->execute($params);
    }
    return $calendario;
  }

  public function findById(int $id): ?Calendario
  {
    $stmt = $this->db->prepare('SELECT * FROM calendarios WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByRango(string $fechaInicio, string $fechaFin, ?int $institucionId = null): array
  {
    $sql = 'SELECT * FROM calendarios WHERE fecha_inicio < :fecha_fin AND fecha_fin > :fecha_inicio';
    $params = [':fecha_inicio' => $fechaInicio, ':fecha_fin' => $fechaFin];
    if ($institucionId !== null) {
      $sql .= ' AND institucion = :institucion';
      $params[':institucion'] = $institucionId;
    }
    $sql .= ' ORDER BY fecha_inicio ASC';
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM calendarios ORDER BY fecha_inicio DESC');
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(Calendario $calendario): array
  {
    return [
      ':titulo' => $calendario->getTitulo(),
      ':tipo' => $calendario->getTipo(),
      ':institucion' => $calendario->getInstitucion()?->getId(),
      ':horario' => $calendario->getHorario()?->getId(),
      ':clase' => $calendario->getClase()?->getId(),
      ':actividad' => $calendario->getActividad()?->getId(),
      ':plan_entrenamiento' => $calendario->getPlanEntrenamiento()?->getId(),
      ':entrenador' => $calendario->getEntrenador()?->getId(),
      ':fecha_inicio' => $calendario->getFechaInicio(),
      ':fecha_fin' => $calendario->getFechaFin(),
      ':ubicacion' => $calendario->getUbicacion(),
      ':cupo_maximo' => $calendario->getCupoMaximo(),
      ':observacion' => $calendario->getObservacion(),
      ':estado' => $calendario->getEstado()?->getId(),
    ];
  }

  private function map(array $row): Calendario
  {
    return new Calendario($row['id'], $row['titulo'], $row['tipo'], $row['institucion'], $row['horario'] ?? null, $row['clase'] ?? null, $row['actividad'] ?? null, $row['plan_entrenamiento'] ?? null, $row['entrenador'] ?? null, $row['fecha_inicio'], $row['fecha_fin'], $row['ubicacion'], (int) $row['cupo_maximo'], $row['observacion'] ?? null, $row['estado']);
  }
}
