<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Horario.php';

use Core\Database;
use Entitys\Horario;
use PDO;

class HorarioRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Horario $horario): Horario
  {
    if ($horario->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO horarios (nombre, institucion, clase, actividad, categoria_deportiva, entrenador, dia_semana, hora_inicio, hora_fin, fecha_inicio, fecha_fin, cupo_maximo, ubicacion, estado) VALUES (:nombre, :institucion, :clase, :actividad, :categoria_deportiva, :entrenador, :dia_semana, :hora_inicio, :hora_fin, :fecha_inicio, :fecha_fin, :cupo_maximo, :ubicacion, :estado)");
      $stmt->execute($this->params($horario));
      $horario->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE horarios SET nombre = :nombre, institucion = :institucion, clase = :clase, actividad = :actividad, categoria_deportiva = :categoria_deportiva, entrenador = :entrenador, dia_semana = :dia_semana, hora_inicio = :hora_inicio, hora_fin = :hora_fin, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, cupo_maximo = :cupo_maximo, ubicacion = :ubicacion, estado = :estado WHERE id = :id");
      $params = $this->params($horario);
      $params[':id'] = $horario->getId();
      $stmt->execute($params);
    }
    return $horario;
  }

  public function findById(int $id): ?Horario
  {
    $stmt = $this->db->prepare('SELECT * FROM horarios WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM horarios ORDER BY dia_semana ASC, hora_inicio ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_map(fn($row) => $this->map($row), $rows ?: []);
  }

  public function findByClase(int $claseId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM horarios WHERE clase = :clase ORDER BY dia_semana ASC, hora_inicio ASC');
    $stmt->execute([':clase' => $claseId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  public function findByActividad(int $actividadId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM horarios WHERE actividad = :actividad ORDER BY dia_semana ASC, hora_inicio ASC');
    $stmt->execute([':actividad' => $actividadId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  public function findByInstitucion(int $institucionId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM horarios WHERE institucion = :institucion ORDER BY dia_semana ASC, hora_inicio ASC');
    $stmt->execute([':institucion' => $institucionId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(Horario $horario): array
  {
    return [
      ':nombre' => $horario->getNombre(),
      ':institucion' => $horario->getInstitucion()?->getId(),
      ':clase' => $horario->getClase()?->getId(),
      ':actividad' => $horario->getActividad()?->getId(),
      ':categoria_deportiva' => $horario->getCategoriaDeportiva()?->getId(),
      ':entrenador' => $horario->getEntrenador()?->getId(),
      ':dia_semana' => $horario->getDiaSemana(),
      ':hora_inicio' => $horario->getHoraInicio(),
      ':hora_fin' => $horario->getHoraFin(),
      ':fecha_inicio' => $horario->getFechaInicio(),
      ':fecha_fin' => $horario->getFechaFin(),
      ':cupo_maximo' => $horario->getCupoMaximo(),
      ':ubicacion' => $horario->getUbicacion(),
      ':estado' => $horario->getEstado()?->getId(),
    ];
  }

  private function map(array $row): Horario
  {
    return new Horario($row['id'], $row['nombre'], $row['institucion'], $row['clase'] ?? null, $row['actividad'] ?? null, $row['categoria_deportiva'] ?? null, $row['entrenador'] ?? null, (int) $row['dia_semana'], $row['hora_inicio'], $row['hora_fin'], $row['fecha_inicio'] ?? null, $row['fecha_fin'] ?? null, (int) $row['cupo_maximo'], $row['ubicacion'], $row['estado']);
  }
}
