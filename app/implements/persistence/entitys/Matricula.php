<?php

namespace Entitys;

require_once __DIR__ . '/../repository/MatriculaRepository.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Membresia.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Estado.php';

class Matricula
{
  private ?int $id;
  private string $fecha_registro;
  private int $persona;
  private string $fecha_inicio;
  private string $fecha_fin;
  private ?int $membresia;
  private int $institucion;
  private int $estado;

  public function __construct(?int $id, string $fecha_registro, mixed $persona, string $fecha_inicio, string $fecha_fin, mixed $membresia, mixed $institucion, mixed $estado)
  {
    $this->id = $id;
    $this->fecha_registro = $fecha_registro;
    $this->persona = $this->extractId($persona);
    $this->fecha_inicio = $fecha_inicio;
    $this->fecha_fin = $fecha_fin;
    $this->membresia = $this->extractId($membresia);
    $this->institucion = $this->extractId($institucion);
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getFechaRegistro(): string
  {
    return $this->fecha_registro;
  }

  public function getPersona(): ?Persona
  {
    return $this->persona === null ? null : Persona::from($this->persona);
  }

  public function getFechaInicio(): string
  {
    return $this->fecha_inicio;
  }

  public function getFechaFin(): string
  {
    return $this->fecha_fin;
  }

  public function getMembresia(): ?Membresia
  {
    return $this->membresia === null ? null : Membresia::from($this->membresia);
  }

  public function getInstitucion(): ?Institucion
  {
    return $this->institucion === null ? null : Institucion::from($this->institucion);
  }

  public function getEstado(): ?Estado
  {
    return $this->estado === null ? null : Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setFechaRegistro(string $fecha_registro): void
  {
    $this->fecha_registro = $fecha_registro;
  }

  public function setPersona(mixed $persona): void
  {
    $this->persona = $this->extractId($persona);
  }

  public function setFechaInicio(string $fecha_inicio): void
  {
    $this->fecha_inicio = $fecha_inicio;
  }

  public function setFechaFin(string $fecha_fin): void
  {
    $this->fecha_fin = $fecha_fin;
  }

  public function setMembresia(mixed $membresia): void
  {
    $this->membresia = $this->extractId($membresia);
  }

  public function setInstitucion(mixed $institucion): void
  {
    $this->institucion = $this->extractId($institucion);
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
      $repositorio = new \Repositories\MatriculaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', 0, '', '', null, 0, 0);
  }
}
