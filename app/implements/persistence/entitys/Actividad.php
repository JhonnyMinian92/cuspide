<?php

namespace Entitys;

require_once __DIR__ . '/../repository/ActividadRepository.php';
require_once __DIR__ . '/Deporte.php';
require_once __DIR__ . '/NivelAsignatura.php';
require_once __DIR__ . '/Institucion.php';

class Actividad
{
  private ?int $id;
  private int $deporte;
  private float $precio;
  private int $nivel;
  private string $observacion;
  private int $institucion;

  public function __construct(?int $id, mixed $deporte, float $precio, mixed $nivel, string $observacion, mixed $institucion)
  {
    $this->id = $id;
    $this->deporte = $this->extractId($deporte);
    $this->precio = $precio;
    $this->nivel = $this->extractId($nivel);
    $this->observacion = $observacion;
    $this->institucion = $this->extractId($institucion);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getDeporte(): ?Deporte
  {
    return $this->deporte === null ? null : Deporte::from($this->deporte);
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

  public function setDeporte(mixed $deporte): void
  {
    $this->deporte = $this->extractId($deporte);
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
      $repositorio = new \Repositories\ActividadRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, 0.0, 0, '', 0);
  }
}
