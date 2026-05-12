<?php

namespace Entitys;

require_once __DIR__ . '/../repository/PlanEntrenamientoEjercicioRepository.php';
require_once __DIR__ . '/PlanEntrenamientoSesion.php';
require_once __DIR__ . '/Estado.php';

class PlanEntrenamientoEjercicio
{
  private ?int $id;
  private int $sesion;
  private int $orden;
  private string $nombre;
  private string $descripcion;
  private int $series;
  private string $repeticiones;
  private int $duracion_segundos;
  private int $descanso_segundos;
  private string $intensidad;
  private int $estado;

  public function __construct(?int $id, mixed $sesion, int $orden, string $nombre, string $descripcion, int $series, string $repeticiones, int $duracion_segundos, int $descanso_segundos, string $intensidad, mixed $estado)
  {
    $this->id = $id;
    $this->sesion = $this->extractId($sesion);
    $this->orden = $orden;
    $this->nombre = $nombre;
    $this->descripcion = $descripcion;
    $this->series = $series;
    $this->repeticiones = $repeticiones;
    $this->duracion_segundos = $duracion_segundos;
    $this->descanso_segundos = $descanso_segundos;
    $this->intensidad = $intensidad;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getSesion(): ?PlanEntrenamientoSesion { return $this->sesion === null ? null : PlanEntrenamientoSesion::from($this->sesion); }
  public function getOrden(): int { return $this->orden; }
  public function getNombre(): string { return $this->nombre; }
  public function getDescripcion(): string { return $this->descripcion; }
  public function getSeries(): int { return $this->series; }
  public function getRepeticiones(): string { return $this->repeticiones; }
  public function getDuracionSegundos(): int { return $this->duracion_segundos; }
  public function getDescansoSegundos(): int { return $this->descanso_segundos; }
  public function getIntensidad(): string { return $this->intensidad; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setSesion(mixed $sesion): void { $this->sesion = $this->extractId($sesion); }
  public function setOrden(int $orden): void { $this->orden = $orden; }
  public function setNombre(string $nombre): void { $this->nombre = $nombre; }
  public function setDescripcion(string $descripcion): void { $this->descripcion = $descripcion; }
  public function setSeries(int $series): void { $this->series = $series; }
  public function setRepeticiones(string $repeticiones): void { $this->repeticiones = $repeticiones; }
  public function setDuracionSegundos(int $duracion_segundos): void { $this->duracion_segundos = $duracion_segundos; }
  public function setDescansoSegundos(int $descanso_segundos): void { $this->descanso_segundos = $descanso_segundos; }
  public function setIntensidad(string $intensidad): void { $this->intensidad = $intensidad; }
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
      $repositorio = new \Repositories\PlanEntrenamientoEjercicioRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, 0, 0, '', '', 0, '', 0, 0, '', 0);
  }
}
