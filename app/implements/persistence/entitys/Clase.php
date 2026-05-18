<?php

namespace Entitys;

require_once __DIR__ . '/../repository/ClaseRepository.php';
require_once __DIR__ . '/PeriodoAcademico.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Estado.php';

class Clase
{
  private ?int $id;
  private string $nombre;
  private ?int $periodo_academico;
  private ?int $institucion;
  private ?int $docente;
  private int $cupo_maximo;
  private ?int $estado;
  private ?int $asignatura;
  private ?int $nivel_asignatura;
  private ?float $precio;
  private ?string $observacion;

  public function __construct(
    ?int $id,
    string $nombre,
    mixed $periodo_academico,
    mixed $institucion,
    mixed $docente,
    int $cupo_maximo,
    mixed $estado,
    mixed $asignatura = null,
    mixed $nivel_asignatura = null,
    mixed $precio = null,
    ?string $observacion = null
  ) {
    $this->id               = $id;
    $this->nombre           = $nombre;
    $this->periodo_academico = $this->extractId($periodo_academico);
    $this->institucion      = $this->extractId($institucion);
    $this->docente          = $this->extractId($docente);
    $this->cupo_maximo      = $cupo_maximo;
    $this->estado           = $this->extractId($estado);
    $this->asignatura       = $this->extractId($asignatura);
    $this->nivel_asignatura = $this->extractId($nivel_asignatura);
    $this->precio           = $precio !== null && $precio !== '' ? (float) $precio : null;
    $this->observacion      = $observacion;
  }

  public function getId(): ?int              { return $this->id; }
  public function getNombre(): string        { return $this->nombre; }
  public function getCupoMaximo(): int       { return $this->cupo_maximo; }
  public function getPrecio(): ?float        { return $this->precio; }
  public function getObservacion(): ?string  { return $this->observacion; }

  public function getPeriodoAcademico(): ?PeriodoAcademico
  {
    return $this->periodo_academico === null ? null : PeriodoAcademico::from($this->periodo_academico);
  }
  public function getInstitucion(): ?Institucion
  {
    return $this->institucion === null ? null : Institucion::from($this->institucion);
  }
  public function getDocente(): ?Persona
  {
    return $this->docente === null ? null : Persona::from($this->docente);
  }
  public function getEstado(): ?Estado
  {
    return $this->estado === null ? null : Estado::from($this->estado);
  }
  public function getAsignatura(): ?int        { return $this->asignatura; }
  public function getNivelAsignatura(): ?int   { return $this->nivel_asignatura; }

  public function setId(?int $id): void                     { $this->id = $id; }
  public function setNombre(string $nombre): void           { $this->nombre = $nombre; }
  public function setCupoMaximo(int $cupo_maximo): void     { $this->cupo_maximo = $cupo_maximo; }
  public function setPrecio(mixed $precio): void            { $this->precio = $precio !== null && $precio !== '' ? (float) $precio : null; }
  public function setObservacion(?string $obs): void        { $this->observacion = $obs; }
  public function setAsignatura(mixed $asignatura): void    { $this->asignatura = $this->extractId($asignatura); }
  public function setNivelAsignatura(mixed $nivel): void    { $this->nivel_asignatura = $this->extractId($nivel); }

  public function setPeriodoAcademico(mixed $periodo_academico): void
  {
    $this->periodo_academico = $this->extractId($periodo_academico);
  }
  public function setInstitucion(mixed $institucion): void
  {
    $this->institucion = $this->extractId($institucion);
  }
  public function setDocente(mixed $docente): void
  {
    $this->docente = $this->extractId($docente);
  }
  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  private function extractId(mixed $value): ?int
  {
    if ($value === null || $value === '') return null;
    if (is_object($value) && method_exists($value, 'getId')) return $value->getId();
    return (int) $value;
  }

  public static function from(int $id): ?self
  {
    try {
      $repositorio = new \Repositories\ClaseRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, '', 0, 0, null, 0, 0);
  }
}
