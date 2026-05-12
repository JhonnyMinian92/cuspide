<?php

namespace Entitys;

require_once __DIR__ . '/../repository/CalendarioRepository.php';
require_once __DIR__ . '/Horario.php';
require_once __DIR__ . '/Clase.php';
require_once __DIR__ . '/Actividad.php';
require_once __DIR__ . '/PlanEntrenamiento.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Estado.php';

class Calendario
{
  private ?int $id;
  private string $titulo;
  private string $tipo;
  private int $institucion;
  private ?int $horario;
  private ?int $clase;
  private ?int $actividad;
  private ?int $plan_entrenamiento;
  private ?int $entrenador;
  private string $fecha_inicio;
  private string $fecha_fin;
  private string $ubicacion;
  private int $cupo_maximo;
  private ?string $observacion;
  private int $estado;

  public function __construct(?int $id, string $titulo, string $tipo, mixed $institucion, mixed $horario, mixed $clase, mixed $actividad, mixed $plan_entrenamiento, mixed $entrenador, string $fecha_inicio, string $fecha_fin, string $ubicacion, int $cupo_maximo, ?string $observacion, mixed $estado)
  {
    $this->id = $id;
    $this->titulo = $titulo;
    $this->tipo = $tipo;
    $this->institucion = $this->extractId($institucion);
    $this->horario = $this->extractId($horario);
    $this->clase = $this->extractId($clase);
    $this->actividad = $this->extractId($actividad);
    $this->plan_entrenamiento = $this->extractId($plan_entrenamiento);
    $this->entrenador = $this->extractId($entrenador);
    $this->fecha_inicio = $fecha_inicio;
    $this->fecha_fin = $fecha_fin;
    $this->ubicacion = $ubicacion;
    $this->cupo_maximo = $cupo_maximo;
    $this->observacion = $observacion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getTitulo(): string { return $this->titulo; }
  public function getTipo(): string { return $this->tipo; }
  public function getInstitucion(): ?Institucion { return $this->institucion === null ? null : Institucion::from($this->institucion); }
  public function getHorario(): ?Horario { return $this->horario === null ? null : Horario::from($this->horario); }
  public function getClase(): ?Clase { return $this->clase === null ? null : Clase::from($this->clase); }
  public function getActividad(): ?Actividad { return $this->actividad === null ? null : Actividad::from($this->actividad); }
  public function getPlanEntrenamiento(): ?PlanEntrenamiento { return $this->plan_entrenamiento === null ? null : PlanEntrenamiento::from($this->plan_entrenamiento); }
  public function getEntrenador(): ?Persona { return $this->entrenador === null ? null : Persona::from($this->entrenador); }
  public function getFechaInicio(): string { return $this->fecha_inicio; }
  public function getFechaFin(): string { return $this->fecha_fin; }
  public function getUbicacion(): string { return $this->ubicacion; }
  public function getCupoMaximo(): int { return $this->cupo_maximo; }
  public function getObservacion(): ?string { return $this->observacion; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setTitulo(string $titulo): void { $this->titulo = $titulo; }
  public function setTipo(string $tipo): void { $this->tipo = $tipo; }
  public function setInstitucion(mixed $institucion): void { $this->institucion = $this->extractId($institucion); }
  public function setHorario(mixed $horario): void { $this->horario = $this->extractId($horario); }
  public function setClase(mixed $clase): void { $this->clase = $this->extractId($clase); }
  public function setActividad(mixed $actividad): void { $this->actividad = $this->extractId($actividad); }
  public function setPlanEntrenamiento(mixed $plan_entrenamiento): void { $this->plan_entrenamiento = $this->extractId($plan_entrenamiento); }
  public function setEntrenador(mixed $entrenador): void { $this->entrenador = $this->extractId($entrenador); }
  public function setFechaInicio(string $fecha_inicio): void { $this->fecha_inicio = $fecha_inicio; }
  public function setFechaFin(string $fecha_fin): void { $this->fecha_fin = $fecha_fin; }
  public function setUbicacion(string $ubicacion): void { $this->ubicacion = $ubicacion; }
  public function setCupoMaximo(int $cupo_maximo): void { $this->cupo_maximo = $cupo_maximo; }
  public function setObservacion(?string $observacion): void { $this->observacion = $observacion; }
  public function setEstado(mixed $estado): void { $this->estado = $this->extractId($estado); }

  private function extractId(mixed $value): ?int
  {
    if ($value === null) return null;
    if (is_object($value) && method_exists($value, 'getId')) return $value->getId();
    return (int) $value;
  }

  public static function from(int $id): ?self
  {
    try {
      $repositorio = new \Repositories\CalendarioRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, '', '', 0, null, null, null, null, null, '', '', '', 0, null, 0);
  }
}
