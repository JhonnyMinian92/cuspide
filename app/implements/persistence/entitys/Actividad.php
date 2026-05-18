<?php

namespace Entitys;

require_once __DIR__ . '/../repository/ActividadRepository.php';
require_once __DIR__ . '/CategoriaDeportiva.php';
require_once __DIR__ . '/NivelAsignatura.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Estado.php';
require_once __DIR__ . '/Persona.php';

class Actividad
{
  private ?int $id;
  private string $nombre;
  private int $categoria_deportiva;
  private float $precio;
  private int $nivel;
  private string $observacion;
  private int $institucion;
  private int $estado;
  private ?int $docente;
  private int $cupo_maximo;
  private ?int $periodo_academico;

  public function __construct(?int $id, mixed $categoria_deportiva, float $precio, mixed $nivel, string $observacion, mixed $institucion, string $nombre = '', mixed $estado = 1, mixed $docente = null, int $cupo_maximo = 0, ?int $periodo_academico = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->categoria_deportiva = $this->extractId($categoria_deportiva);
    $this->precio = $precio;
    $this->nivel = $this->extractId($nivel);
    $this->observacion = $observacion;
    $this->institucion = $this->extractId($institucion);
    $this->estado = $this->extractId($estado);
    $this->docente = $this->extractId($docente);
    $this->cupo_maximo = $cupo_maximo;
    $this->periodo_academico = $periodo_academico;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getCategoriaDeportiva(): ?CategoriaDeportiva
  {
    return $this->categoria_deportiva === null ? null : CategoriaDeportiva::from($this->categoria_deportiva);
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function getPrecio(): float
  {
    return $this->precio;
  }

  public function getNivel(): ?NivelAsignatura
  {
    return $this->nivel === null ? null : NivelAsignatura::from($this->nivel);
  }

  public function getObservacion(): string
  {
    return $this->observacion;
  }

  public function getInstitucion(): ?Institucion
  {
    return $this->institucion === null ? null : Institucion::from($this->institucion);
  }

  public function getEstado(): ?Estado
  {
    return $this->estado === null ? null : Estado::from($this->estado);
  }

  public function getDocente(): ?Persona
  {
    return $this->docente === null ? null : Persona::from($this->docente);
  }

  public function getCupoMaximo(): int
  {
    return $this->cupo_maximo;
  }

  public function getPeriodoAcademico(): ?int
  {
    return $this->periodo_academico;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public function setCategoriaDeportiva(mixed $categoria_deportiva): void
  {
    $this->categoria_deportiva = $this->extractId($categoria_deportiva);
  }

  public function setPrecio(float $precio): void
  {
    $this->precio = $precio;
  }

  public function setNivel(mixed $nivel): void
  {
    $this->nivel = $this->extractId($nivel);
  }

  public function setObservacion(string $observacion): void
  {
    $this->observacion = $observacion;
  }

  public function setInstitucion(mixed $institucion): void
  {
    $this->institucion = $this->extractId($institucion);
  }

  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  public function setDocente(mixed $docente): void
  {
    $this->docente = $this->extractId($docente);
  }

  public function setCupoMaximo(int $cupo_maximo): void
  {
    $this->cupo_maximo = $cupo_maximo;
  }

  public function setPeriodoAcademico(?int $periodo_academico): void
  {
    $this->periodo_academico = $periodo_academico;
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
      $repositorio = new \Repositories\ActividadRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, 0.0, 0, '', 0, '', 0, null, 0, null);
  }
}