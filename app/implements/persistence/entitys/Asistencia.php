<?php

namespace Entitys;

require_once __DIR__ . '/../repository/AsistenciaRepository.php';
require_once __DIR__ . '/Matricula.php';
require_once __DIR__ . '/Clase.php';
require_once __DIR__ . '/Actividad.php'; // Asumiendo que también generarás este basado en Clase
require_once __DIR__ . '/Calendario.php';
require_once __DIR__ . '/Horario.php';
require_once __DIR__ . '/PlanEntrenamientoSesion.php';
require_once __DIR__ . '/Estado.php'; // Puede ser un estado: Presente, Ausente, Justificado

class Asistencia
{
  private ?int $id;
  private string $fecha;
  private ?int $matricula;
  private ?int $clase;
  private ?int $actividad;
  private ?int $calendario;
  private ?int $horario;
  private ?int $plan_entrenamiento_sesion;
  private ?string $hora_entrada;
  private ?string $hora_salida;
  private string $observacion;
  private ?int $estado;

  public function __construct(?int $id, string $fecha, mixed $matricula, mixed $clase, mixed $actividad, string $observacion, mixed $estado, mixed $calendario = null, mixed $horario = null, mixed $plan_entrenamiento_sesion = null, ?string $hora_entrada = null, ?string $hora_salida = null)
  {
    $this->id = $id;
    $this->fecha = $fecha;
    $this->matricula = $this->extractId($matricula);
    $this->clase = $this->extractId($clase);
    $this->actividad = $this->extractId($actividad);
    $this->calendario = $this->extractId($calendario);
    $this->horario = $this->extractId($horario);
    $this->plan_entrenamiento_sesion = $this->extractId($plan_entrenamiento_sesion);
    $this->hora_entrada = $hora_entrada;
    $this->hora_salida = $hora_salida;
    $this->observacion = $observacion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }
  public function getFecha(): string
  {
    return $this->fecha;
  }
  public function getMatricula(): ?Matricula
  {
    return !empty($this->matricula) ? Matricula::from($this->matricula) : null;
  }
  // Usamos Clase si la asistencia es de una clase, o Actividad si es extracurrícular
  public function getClase(): ?Clase
  {
    return $this->clase === null ? null : Clase::from($this->clase);
  }

  public function getActividad(): ?Actividad
  {
    return $this->actividad === null ? null : Actividad::from($this->actividad);
  }

  public function getCalendario(): ?Calendario
  {
    return $this->calendario === null ? null : Calendario::from($this->calendario);
  }

  public function getHorario(): ?Horario
  {
    return $this->horario === null ? null : Horario::from($this->horario);
  }

  public function getPlanEntrenamientoSesion(): ?PlanEntrenamientoSesion
  {
    return $this->plan_entrenamiento_sesion === null ? null : PlanEntrenamientoSesion::from($this->plan_entrenamiento_sesion);
  }

  public function getHoraEntrada(): ?string
  {
    return $this->hora_entrada;
  }

  public function getHoraSalida(): ?string
  {
    return $this->hora_salida;
  }

  public function getObservacion(): string
  {
    return $this->observacion;
  }
  public function getEstado(): ?Estado
  {
    return !empty($this->estado) ? Estado::from($this->estado) : null;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }
  public function setFecha(string $fecha): void
  {
    $this->fecha = $fecha;
  }
  public function setMatricula(mixed $matricula): void
  {
    $this->matricula = $this->extractId($matricula);
  }
  public function setClase(mixed $clase): void
  {
    $this->clase = $this->extractId($clase);
  }
  public function setActividad(mixed $actividad): void
  {
    $this->actividad = $this->extractId($actividad);
  }

  public function setCalendario(mixed $calendario): void
  {
    $this->calendario = $this->extractId($calendario);
  }

  public function setHorario(mixed $horario): void
  {
    $this->horario = $this->extractId($horario);
  }

  public function setPlanEntrenamientoSesion(mixed $plan_entrenamiento_sesion): void
  {
    $this->plan_entrenamiento_sesion = $this->extractId($plan_entrenamiento_sesion);
  }

  public function setHoraEntrada(?string $hora_entrada): void
  {
    $this->hora_entrada = $hora_entrada;
  }

  public function setHoraSalida(?string $hora_salida): void
  {
    $this->hora_salida = $hora_salida;
  }

  public function setObservacion(string $observacion): void
  {
    $this->observacion = $observacion;
  }
  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  private function extractId(mixed $value): ?int
  {
    if ($value === null) return null;
    if (is_object($value) && method_exists($value, 'getId')) return $value->getId();
    return (int) $value;
  }

  public static function from(int $id): ?self
  {
    if ($id <= 0) {
      return null;
    }
    try {
      $repositorio = new \Repositories\AsistenciaRepository();
      return $repositorio->findById($id);
    } catch (\Exception $e) {
      // Opcional: Loggear el error $e para depuración.
      return null;
    }
  }
}
