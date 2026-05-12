<?php

namespace Entitys;

require_once __DIR__ . '/../repository/CalificacionRepository.php';
require_once __DIR__ . '/Evaluacion.php';
require_once __DIR__ . '/Matricula.php';
require_once __DIR__ . '/Estado.php';

class Calificacion
{
  private ?int $id;
  private int $evaluacion;
  private int $matricula;
  private float $nota;
  private string $observacion;
  private int $estado;

  public function __construct(?int $id, mixed $evaluacion, mixed $matricula, float $nota, string $observacion, mixed $estado)
  {
    $this->id = $id;
    $this->evaluacion = $this->extractId($evaluacion);
    $this->matricula = $this->extractId($matricula);
    $this->nota = $nota;
    $this->observacion = $observacion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }
  public function getEvaluacion(): ?Evaluacion
  {
    return $this->evaluacion === null ? null : Evaluacion::from($this->evaluacion);
  }
  public function getMatricula(): ?Matricula
  {
    return $this->matricula === null ? null : Matricula::from($this->matricula);
  }
  public function getNota(): float
  {
    return $this->nota;
  }
  public function getObservacion(): string
  {
    return $this->observacion;
  }
  public function getEstado(): ?Estado
  {
    return $this->estado === null ? null : Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }
  public function setEvaluacion(mixed $evaluacion): void
  {
    $this->evaluacion = $this->extractId($evaluacion);
  }
  public function setMatricula(mixed $matricula): void
  {
    $this->matricula = $this->extractId($matricula);
  }
  public function setNota(float $nota): void
  {
    $this->nota = $nota;
  }
  public function setObservacion(string $observacion): void
  {
    $this->observacion = $observacion;
  }
  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  private function extractId(mixed $value): ?int
  {
    if ($value === null) return null;
    if (is_object($value) && method_exists($value, 'getId')) return $value->getId();
    return (int) $value;
  }

  public static function from(int $id): ?self
  {
    return new self($id, 0, 0, 0.0, '', 0);
  }
}
