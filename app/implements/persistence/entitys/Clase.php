<?php

namespace Entitys;

require_once __DIR__ . '/../repository/ClaseRepository.php';
require_once __DIR__ . '/Asignatura.php';
require_once __DIR__ . '/NivelAsignatura.php';
require_once __DIR__ . '/Institucion.php';

class Clase
{
  private ?int $id;
  private int $asignatura;
  private float $precio;
  private int $nivel;
  private string $observacion;
  private int $institucion;

  public function __construct(?int $id, mixed $asignatura, float $precio, mixed $nivel, string $observacion, mixed $institucion)
  {
    $this->id = $id;
    $this->asignatura = $this->extractId($asignatura);
    $this->precio = $precio;
    $this->nivel = $this->extractId($nivel);
    $this->observacion = $observacion;
    $this->institucion = $this->extractId($institucion);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getAsignatura(): ?Asignatura
  {
    return $this->asignatura === null ? null : Asignatura::from($this->asignatura);
  }

  public function getPrecio(): float
  {
    return $this->precio;
  }

  public function getNivel(): ?NivelAsignatura
  {
    return $this->nivel === null ? null : NivelAsignatura::from($this->nivel);
  }

  public function getObservacion(): string
  {
    return $this->observacion;
  }

  public function getInstitucion(): ?Institucion
  {
    return $this->institucion === null ? null : Institucion::from($this->institucion);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setAsignatura(mixed $asignatura): void
  {
    $this->asignatura = $this->extractId($asignatura);
  }

  public function setPrecio(float $precio): void
  {
    $this->precio = $precio;
  }

  public function setNivel(mixed $nivel): void
  {
    $this->nivel = $this->extractId($nivel);
  }

  public function setObservacion(string $observacion): void
  {
    $this->observacion = $observacion;
  }

  public function setInstitucion(mixed $institucion): void
  {
    $this->institucion = $this->extractId($institucion);
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
      $repositorio = new \Repositories\ClaseRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, 0.0, 0, '', 0);
  }
}
