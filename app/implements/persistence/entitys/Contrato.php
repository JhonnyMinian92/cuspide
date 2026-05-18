<?php

namespace Entitys;

require_once __DIR__ . '/../repository/ContratoRepository.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/NivelAcademico.php';

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

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getFechaInscripcion(): string
  {
    return $this->fecha_inscripcion;
  }

  public function getFechaInicio(): string
  {
    return $this->fecha_inicio;
  }

  public function getFechaFin(): string
  {
    return $this->fecha_fin;
  }

  public function getNivel(): ?NivelAcademico
  {
    return $this->nivel === 0 ? null : NivelAcademico::from($this->nivel);
  }

  public function getInstitucion(): ?Institucion
  {
    return $this->institucion === 0 ? null : Institucion::from($this->institucion);
  }

  public function getPersona(): ?Persona
  {
    return $this->persona === 0 ? null : Persona::from($this->persona);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
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
    if ($id === 0) return null;
    try {
      $repo = new \Repositories\ContratoRepository();
      return $repo->findById($id);
    } catch (\Exception $e) {
      return null;
    }
  }
}
