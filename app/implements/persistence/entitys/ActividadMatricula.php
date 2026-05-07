<?php

namespace Entitys;

require_once __DIR__ . '/../repository/ActividadMatriculaRepository.php';
require_once __DIR__ . '/Actividad.php';
require_once __DIR__ . '/Matricula.php';
require_once __DIR__ . '/Modalidad.php';
require_once __DIR__ . '/Estado.php';

class ActividadMatricula
{
  private ?int $id;
  private int $actividad;
  private int $matricula;
  private int $modalidad;
  private string $fecha_inicio;
  private string $fecha_fin;
  private int $estado;

  public function __construct(?int $id, mixed $actividad, mixed $matricula, mixed $modalidad, string $fecha_inicio, string $fecha_fin, mixed $estado)
  {
    $this->id = $id;
    $this->actividad = $this->extractId($actividad);
    $this->matricula = $this->extractId($matricula);
    $this->modalidad = $this->extractId($modalidad);
    $this->fecha_inicio = $fecha_inicio;
    $this->fecha_fin = $fecha_fin;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getActividad(): ?Actividad
  {
    return $this->actividad === null ? null : Actividad::from($this->actividad);
  }

  public function getMatricula(): ?Matricula
  {
    return $this->matricula === null ? null : Matricula::from($this->matricula);
  }

  public function getModalidad(): ?Modalidad
  {
    return $this->modalidad === null ? null : Modalidad::from($this->modalidad);
  }

  public function getFechaInicio(): string
  {
    return $this->fecha_inicio;
  }

  public function getFechaFin(): string
  {
    return $this->fecha_fin;
  }

  public function getEstado(): ?Estado
  {
    return $this->estado === null ? null : Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setActividad(mixed $actividad): void
  {
    $this->actividad = $this->extractId($actividad);
  }

  public function setMatricula(mixed $matricula): void
  {
    $this->matricula = $this->extractId($matricula);
  }

  public function setModalidad(mixed $modalidad): void
  {
    $this->modalidad = $this->extractId($modalidad);
  }

  public function setFechaInicio(string $fecha_inicio): void
  {
    $this->fecha_inicio = $fecha_inicio;
  }

  public function setFechaFin(string $fecha_fin): void
  {
    $this->fecha_fin = $fecha_fin;
  }

  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  private function extractId(mixed $value): ?int
  {
    if ($value === null) {
      return null;
    }
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return (int) $value;
  }

  public static function from(int $id): ?self
  {
    try {
      $repositorio = new \Repositories\ActividadMatriculaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, 0, 0, '', '', 0);
  }
}
