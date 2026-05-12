<?php

namespace Entitys;

require_once __DIR__ . '/../repository/PlanEntrenamientoSesionRepository.php';
require_once __DIR__ . '/PlanEntrenamiento.php';
require_once __DIR__ . '/Calendario.php';
require_once __DIR__ . '/Horario.php';
require_once __DIR__ . '/Estado.php';

class PlanEntrenamientoSesion
{
  private ?int $id;
  private int $plan_entrenamiento;
  private ?int $calendario;
  private ?int $horario;
  private int $numero_semana;
  private ?int $dia_semana;
  private ?string $fecha_programada;
  private string $titulo;
  private string $objetivo;
  private int $duracion_minutos;
  private string $intensidad;
  private string $contenido;
  private ?string $observacion;
  private int $estado;

  public function __construct(?int $id, mixed $plan_entrenamiento, mixed $calendario, mixed $horario, int $numero_semana, ?int $dia_semana, ?string $fecha_programada, string $titulo, string $objetivo, int $duracion_minutos, string $intensidad, string $contenido, ?string $observacion, mixed $estado)
  {
    $this->id = $id;
    $this->plan_entrenamiento = $this->extractId($plan_entrenamiento);
    $this->calendario = $this->extractId($calendario);
    $this->horario = $this->extractId($horario);
    $this->numero_semana = $numero_semana;
    $this->dia_semana = $dia_semana;
    $this->fecha_programada = $fecha_programada;
    $this->titulo = $titulo;
    $this->objetivo = $objetivo;
    $this->duracion_minutos = $duracion_minutos;
    $this->intensidad = $intensidad;
    $this->contenido = $contenido;
    $this->observacion = $observacion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getPlanEntrenamiento(): ?PlanEntrenamiento { return $this->plan_entrenamiento === null ? null : PlanEntrenamiento::from($this->plan_entrenamiento); }
  public function getCalendario(): ?Calendario { return $this->calendario === null ? null : Calendario::from($this->calendario); }
  public function getHorario(): ?Horario { return $this->horario === null ? null : Horario::from($this->horario); }
  public function getNumeroSemana(): int { return $this->numero_semana; }
  public function getDiaSemana(): ?int { return $this->dia_semana; }
  public function getFechaProgramada(): ?string { return $this->fecha_programada; }
  public function getTitulo(): string { return $this->titulo; }
  public function getObjetivo(): string { return $this->objetivo; }
  public function getDuracionMinutos(): int { return $this->duracion_minutos; }
  public function getIntensidad(): string { return $this->intensidad; }
  public function getContenido(): string { return $this->contenido; }
  public function getObservacion(): ?string { return $this->observacion; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setPlanEntrenamiento(mixed $plan_entrenamiento): void { $this->plan_entrenamiento = $this->extractId($plan_entrenamiento); }
  public function setCalendario(mixed $calendario): void { $this->calendario = $this->extractId($calendario); }
  public function setHorario(mixed $horario): void { $this->horario = $this->extractId($horario); }
  public function setNumeroSemana(int $numero_semana): void { $this->numero_semana = $numero_semana; }
  public function setDiaSemana(?int $dia_semana): void { $this->dia_semana = $dia_semana; }
  public function setFechaProgramada(?string $fecha_programada): void { $this->fecha_programada = $fecha_programada; }
  public function setTitulo(string $titulo): void { $this->titulo = $titulo; }
  public function setObjetivo(string $objetivo): void { $this->objetivo = $objetivo; }
  public function setDuracionMinutos(int $duracion_minutos): void { $this->duracion_minutos = $duracion_minutos; }
  public function setIntensidad(string $intensidad): void { $this->intensidad = $intensidad; }
  public function setContenido(string $contenido): void { $this->contenido = $contenido; }
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
      $repositorio = new \Repositories\PlanEntrenamientoSesionRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, 0, null, null, 1, null, null, '', '', 0, '', '', null, 0);
  }
}
