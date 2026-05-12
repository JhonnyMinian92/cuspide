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
  private int $periodo_academico;
  private int $institucion;
  private ?int $docente; // Persona ID (Profesor/Entrenador)
  private int $cupo_maximo;
  private int $estado;

  public function __construct(?int $id, string $nombre, mixed $periodo_academico, mixed $institucion, mixed $docente, int $cupo_maximo, mixed $estado)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->periodo_academico = $this->extractId($periodo_academico);
    $this->institucion = $this->extractId($institucion);
    $this->docente = $this->extractId($docente);
    $this->cupo_maximo = $cupo_maximo;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }
  public function getNombre(): string
  {
    return $this->nombre;
  }
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
  public function getCupoMaximo(): int
  {
    return $this->cupo_maximo;
  }
  public function getEstado(): ?Estado
  {
    return $this->estado === null ? null : Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }
  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }
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
  public function setCupoMaximo(int $cupo_maximo): void
  {
    $this->cupo_maximo = $cupo_maximo;
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
    try {
      $repositorio = new \Repositories\ClaseRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, '', 0, 0, null, 0, 0);
  }
}
