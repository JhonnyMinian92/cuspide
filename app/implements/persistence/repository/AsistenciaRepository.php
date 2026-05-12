<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Asistencia.php';

use Core\Database;
use Entitys\Asistencia;
use PDO;

class AsistenciaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Asistencia $asistencia): Asistencia
  {
    if ($asistencia->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO asistencias (fecha, matricula, clase, actividad, calendario, horario, plan_entrenamiento_sesion, hora_entrada, hora_salida, observacion, estado) VALUES (:fecha, :matricula, :clase, :actividad, :calendario, :horario, :plan_entrenamiento_sesion, :hora_entrada, :hora_salida, :observacion, :estado)");
      $stmt->execute([
        ':fecha' => $asistencia->getFecha(),
        ':matricula' => $asistencia->getMatricula()?->getId(),
        ':clase' => $asistencia->getClase()?->getId(),
        ':actividad' => $asistencia->getActividad()?->getId(),
        ':calendario' => $asistencia->getCalendario()?->getId(),
        ':horario' => $asistencia->getHorario()?->getId(),
        ':plan_entrenamiento_sesion' => $asistencia->getPlanEntrenamientoSesion()?->getId(),
        ':hora_entrada' => $asistencia->getHoraEntrada(),
        ':hora_salida' => $asistencia->getHoraSalida(),
        ':observacion' => $asistencia->getObservacion(),
        ':estado' => $asistencia->getEstado()?->getId(),
      ]);
      $asistencia->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE asistencias SET fecha = :fecha, matricula = :matricula, clase = :clase, actividad = :actividad, calendario = :calendario, horario = :horario, plan_entrenamiento_sesion = :plan_entrenamiento_sesion, hora_entrada = :hora_entrada, hora_salida = :hora_salida, observacion = :observacion, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $asistencia->getId(),
        ':fecha' => $asistencia->getFecha(),
        ':matricula' => $asistencia->getMatricula()?->getId(),
        ':clase' => $asistencia->getClase()?->getId(),
        ':actividad' => $asistencia->getActividad()?->getId(),
        ':calendario' => $asistencia->getCalendario()?->getId(),
        ':horario' => $asistencia->getHorario()?->getId(),
        ':plan_entrenamiento_sesion' => $asistencia->getPlanEntrenamientoSesion()?->getId(),
        ':hora_entrada' => $asistencia->getHoraEntrada(),
        ':hora_salida' => $asistencia->getHoraSalida(),
        ':observacion' => $asistencia->getObservacion(),
        ':estado' => $asistencia->getEstado()?->getId(),
      ]);
    }
    return $asistencia;
  }

  public function findById(int $id): ?Asistencia
  {
    $stmt = $this->db->prepare('SELECT * FROM asistencias WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? new Asistencia($row['id'], $row['fecha'], $row['matricula'], $row['clase'], $row['actividad'], $row['observacion'] ?? '', $row['estado'], $row['calendario'] ?? null, $row['horario'] ?? null, $row['plan_entrenamiento_sesion'] ?? null, $row['hora_entrada'] ?? null, $row['hora_salida'] ?? null) : null;
  }
}
