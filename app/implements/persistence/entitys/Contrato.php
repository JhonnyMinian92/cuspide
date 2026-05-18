<?php

namespace Entitys;

require_once __DIR__ . '/../repository/ContratoRepository.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Nivel.php';

class Contrato
{
  private ?int $id;
  private string $fecha_inscripcion;
  private string $fecha_inicio;
  private string $fecha_fin;
  private int $nivel;
  private int $institucion;
  private int $persona;

  public function __construct(?int $id, string $fecha_inscripcion, string $fecha_inicio, string $fecha_fin, mixed $nivel, mixed $institucion, mixed $persona)
  {
    $this->id = $id;
    $this->fecha_inscripcion = $fecha_inscripcion;
    $this->fecha_inicio = $fecha_inicio;
    $this->fecha_fin = $fecha_fin;
    $this->nivel = $this->extractId($nivel);
    $this->institucion = $this->extractId($institucion);
    $this->persona = $this->extractId($persona);
  }

  public function getId(): ?int { return $this->id; }
  public function getFechaInscripcion(): string { return $this->fecha_inscripcion; }
  public function getFechaInicio(): string { return $this->fecha_inicio; }
  public function getFechaFin(): string { return $this->fecha_fin; }
  public function getNivel(): ?Nivel { return Nivel::from($this->nivel); }
  public function getInstitucion(): ?Institucion { return Institucion::from($this->institucion); }
  public function getPersona(): ?Persona { return Persona::from($this->persona); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setFechaInscripcion(string $v): void { $this->fecha_inscripcion = $v; }
  public function setFechaInicio(string $v): void { $this->fecha_inicio = $v; }
  public function setFechaFin(string $v): void { $this->fecha_fin = $v; }
  public function setNivel(mixed $v): void { $this->nivel = $this->extractId($v); }
  public function setInstitucion(mixed $v): void { $this->institucion = $this->extractId($v); }
  public function setPersona(mixed $v): void { $this->persona = $this->extractId($v); }

  private function extractId(mixed $value): int
  {
    if (is_object($value) && method_exists($value, 'getId')) return $value->getId();
    return (int) $value;
  }

  public static function from(int $id): ?self
  {
    try {
      $repo = new \Repositories\ContratoRepository();
      $entity = $repo->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {}
    return new self($id, '', '', '', 0, 0, 0);
  }
}
