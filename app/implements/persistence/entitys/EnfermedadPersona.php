<?php

namespace Entitys;

require_once __DIR__ . '/../repository/EnfermedadPersonaRepository.php';
require_once __DIR__ . '/Enfermedad.php';

class EnfermedadPersona
{
  private ?int $id;
  private int $enfermedad;
  private string $medicacion;
  private string $descripcion;

  public function __construct(?int $id, mixed $enfermedad, string $medicacion, string $descripcion)
  {
    $this->id = $id;
    $this->enfermedad = $this->extractId($enfermedad);
    $this->medicacion = $medicacion;
    $this->descripcion = $descripcion;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getEnfermedad(): ?Enfermedad
  {
    return $this->enfermedad === null ? null : Enfermedad::from($this->enfermedad);
  }

  public function getMedicacion(): string
  {
    return $this->medicacion;
  }

  public function getDescripcion(): string
  {
    return $this->descripcion;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setEnfermedad(mixed $enfermedad): void
  {
    $this->enfermedad = $this->extractId($enfermedad);
  }

  public function setMedicacion(string $medicacion): void
  {
    $this->medicacion = $medicacion;
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
      $repositorio = new \Repositories\EnfermedadPersonaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, '', '');
  }
}
