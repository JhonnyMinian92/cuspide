<?php

namespace Entitys;

require_once __DIR__ . '/../repository/IncidenciaRepository.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Nivel.php';

class Incidencia
{
  private ?int $id;
  private string $fecha;
  private int $persona;
  private int $nivel;
  private string $descripcion;

  public function __construct(?int $id, string $fecha, mixed $persona, mixed $nivel, string $descripcion)
  {
    $this->id = $id;
    $this->fecha = $fecha;
    $this->persona = $this->extractId($persona);
    $this->nivel = $this->extractId($nivel);
    $this->descripcion = $descripcion;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getFecha(): string
  {
    return $this->fecha;
  }

  public function getPersona(): ?Persona
  {
    return $this->persona === null ? null : Persona::from($this->persona);
  }

  public function getNivel(): ?Nivel
  {
    return $this->nivel === null ? null : Nivel::from($this->nivel);
  }

  public function getDescripcion(): string
  {
    return $this->descripcion;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setFecha(string $fecha): void
  {
    $this->fecha = $fecha;
  }

  public function setPersona(mixed $persona): void
  {
    $this->persona = $this->extractId($persona);
  }

  public function setNivel(mixed $nivel): void
  {
    $this->nivel = $this->extractId($nivel);
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
      $repositorio = new \Repositories\IncidenciaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', 0, 0, '');
  }
}
