<?php

namespace Entitys;

require_once __DIR__ . '/../repository/PermisoRepository.php';
require_once __DIR__ . '/TipoPermiso.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/EstadoPermiso.php';

class Permiso
{
  private ?int $id;
  private int $tipo;
  private int $persona;
  private int $estado;
  private string $fechaInicio;
  private string $fechaFin;
  private string $descripcion;

  public function __construct(?int $id, mixed $tipo, mixed $persona, mixed $estado, string $fechaInicio, string $fechaFin, string $descripcion)
  {
    $this->id = $id;
    $this->tipo = $this->extractId($tipo);
    $this->persona = $this->extractId($persona);
    $this->estado = $this->extractId($estado);
    $this->fechaInicio = $fechaInicio;
    $this->fechaFin = $fechaFin;
    $this->descripcion = $descripcion;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getTipo(): ?TipoPermiso
  {
    return $this->tipo === null ? null : TipoPermiso::from($this->tipo);
  }

  public function getPersona(): ?Persona
  {
    return $this->persona === null ? null : Persona::from($this->persona);
  }

  public function getEstado(): ?EstadoPermiso
  {
    return $this->estado === null ? null : EstadoPermiso::from($this->estado);
  }

  public function getFechaInicio(): string
  {
    return $this->fechaInicio;
  }

  public function getFechaFin(): string
  {
    return $this->fechaFin;
  }

  public function getDescripcion(): string
  {
    return $this->descripcion;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setTipo(mixed $tipo): void
  {
    $this->tipo = $this->extractId($tipo);
  }

  public function setPersona(mixed $persona): void
  {
    $this->persona = $this->extractId($persona);
  }

  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  public function setFechaInicio(string $fechaInicio): void
  {
    $this->fechaInicio = $fechaInicio;
  }

  public function setFechaFin(string $fechaFin): void
  {
    $this->fechaFin = $fechaFin;
  }

  public function setDescripcion(string $descripcion): void
  {
    $this->descripcion = $descripcion;
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
      $repositorio = new \Repositories\PermisoRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, 0, 0, '', '', '');
  }
}
