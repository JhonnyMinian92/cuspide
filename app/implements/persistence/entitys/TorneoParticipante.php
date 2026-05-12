<?php

namespace Entitys;

require_once __DIR__ . '/../repository/TorneoParticipanteRepository.php';
require_once __DIR__ . '/Torneo.php';
require_once __DIR__ . '/Matricula.php';
require_once __DIR__ . '/Estado.php';

class TorneoParticipante
{
  private ?int $id;
  private int $torneo;
  private int $matricula;
  private string $fecha_inscripcion;
  private int $estado;

  public function __construct(?int $id, mixed $torneo, mixed $matricula, string $fecha_inscripcion, mixed $estado)
  {
    $this->id = $id;
    $this->torneo = $this->extractId($torneo);
    $this->matricula = $this->extractId($matricula);
    $this->fecha_inscripcion = $fecha_inscripcion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }
  public function getTorneo(): ?Torneo
  {
    return $this->torneo === null ? null : Torneo::from($this->torneo);
  }
  public function getMatricula(): ?Matricula
  {
    return $this->matricula === null ? null : Matricula::from($this->matricula);
  }
  public function getFechaInscripcion(): string
  {
    return $this->fecha_inscripcion;
  }
  public function getEstado(): ?Estado
  {
    return $this->estado === null ? null : Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }
  public function setTorneo(mixed $torneo): void
  {
    $this->torneo = $this->extractId($torneo);
  }
  public function setMatricula(mixed $matricula): void
  {
    $this->matricula = $this->extractId($matricula);
  }
  public function setFechaInscripcion(string $fecha_inscripcion): void
  {
    $this->fecha_inscripcion = $fecha_inscripcion;
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
    return new self($id, 0, 0, '', 0);
  }
}
