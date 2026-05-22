<?php

namespace Entitys;

class Socio
{
  private ?int $id;
  private int $personaId;
  private int $membresiaId;
  private string $fechaInicio;
  private string $fechaFin;
  private int $estado;
  private int $institucionId;

  public function __construct(
    ?int $id,
    int $personaId,
    int $membresiaId,
    string $fechaInicio,
    string $fechaFin,
    int $estado,
    int $institucionId
  ) {
    $this->id           = $id;
    $this->personaId    = $personaId;
    $this->membresiaId  = $membresiaId;
    $this->fechaInicio  = $fechaInicio;
    $this->fechaFin     = $fechaFin;
    $this->estado       = $estado;
    $this->institucionId = $institucionId;
  }

  public function getId(): ?int          { return $this->id; }
  public function getPersonaId(): int    { return $this->personaId; }
  public function getMembresiaId(): int  { return $this->membresiaId; }
  public function getFechaInicio(): string { return $this->fechaInicio; }
  public function getFechaFin(): string  { return $this->fechaFin; }
  public function getEstado(): int       { return $this->estado; }
  public function getInstitucionId(): int { return $this->institucionId; }

  public function setId(?int $id): void           { $this->id = $id; }
  public function setPersonaId(int $v): void      { $this->personaId = $v; }
  public function setMembresiaId(int $v): void    { $this->membresiaId = $v; }
  public function setFechaInicio(string $v): void { $this->fechaInicio = $v; }
  public function setFechaFin(string $v): void    { $this->fechaFin = $v; }
  public function setEstado(int $v): void         { $this->estado = $v; }
  public function setInstitucionId(int $v): void  { $this->institucionId = $v; }
}
