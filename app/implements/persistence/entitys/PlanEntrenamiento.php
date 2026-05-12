<?php

namespace Entitys;

require_once __DIR__ . '/../repository/PlanEntrenamientoRepository.php';
require_once __DIR__ . '/Matricula.php';
require_once __DIR__ . '/Clase.php';
require_once __DIR__ . '/Actividad.php';
require_once __DIR__ . '/CategoriaDeportiva.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/NivelAsignatura.php';
require_once __DIR__ . '/Estado.php';

class PlanEntrenamiento
{
  private ?int $id;
  private string $nombre;
  private string $objetivo;
  private ?string $descripcion;
  private ?int $matricula;
  private ?int $clase;
  private ?int $actividad;
  private ?int $categoria_deportiva;
  private ?int $entrenador;
  private int $institucion;
  private ?int $nivel;
  private string $fecha_inicio;
  private string $fecha_fin;
  private int $version;
  private int $estado;

  public function __construct(?int $id, string $nombre, string $objetivo, ?string $descripcion, mixed $matricula, mixed $clase, mixed $actividad, mixed $categoria_deportiva, mixed $entrenador, mixed $institucion, mixed $nivel, string $fecha_inicio, string $fecha_fin, int $version, mixed $estado)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->objetivo = $objetivo;
    $this->descripcion = $descripcion;
    $this->matricula = $this->extractId($matricula);
    $this->clase = $this->extractId($clase);
    $this->actividad = $this->extractId($actividad);
    $this->categoria_deportiva = $this->extractId($categoria_deportiva);
    $this->entrenador = $this->extractId($entrenador);
    $this->institucion = $this->extractId($institucion);
    $this->nivel = $this->extractId($nivel);
    $this->fecha_inicio = $fecha_inicio;
    $this->fecha_fin = $fecha_fin;
    $this->version = $version;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getNombre(): string { return $this->nombre; }
  public function getObjetivo(): string { return $this->objetivo; }
  public function getDescripcion(): ?string { return $this->descripcion; }
  public function getMatricula(): ?Matricula { return $this->matricula === null ? null : Matricula::from($this->matricula); }
  public function getClase(): ?Clase { return $this->clase === null ? null : Clase::from($this->clase); }
  public function getActividad(): ?Actividad { return $this->actividad === null ? null : Actividad::from($this->actividad); }
  public function getCategoriaDeportiva(): ?CategoriaDeportiva { return $this->categoria_deportiva === null ? null : CategoriaDeportiva::from($this->categoria_deportiva); }
  public function getEntrenador(): ?Persona { return $this->entrenador === null ? null : Persona::from($this->entrenador); }
  public function getInstitucion(): ?Institucion { return $this->institucion === null ? null : Institucion::from($this->institucion); }
  public function getNivel(): ?NivelAsignatura { return $this->nivel === null ? null : NivelAsignatura::from($this->nivel); }
  public function getFechaInicio(): string { return $this->fecha_inicio; }
  public function getFechaFin(): string { return $this->fecha_fin; }
  public function getVersion(): int { return $this->version; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setNombre(string $nombre): void { $this->nombre = $nombre; }
  public function setObjetivo(string $objetivo): void { $this->objetivo = $objetivo; }
  public function setDescripcion(?string $descripcion): void { $this->descripcion = $descripcion; }
  public function setMatricula(mixed $matricula): void { $this->matricula = $this->extractId($matricula); }
  public function setClase(mixed $clase): void { $this->clase = $this->extractId($clase); }
  public function setActividad(mixed $actividad): void { $this->actividad = $this->extractId($actividad); }
  public function setCategoriaDeportiva(mixed $categoria_deportiva): void { $this->categoria_deportiva = $this->extractId($categoria_deportiva); }
  public function setEntrenador(mixed $entrenador): void { $this->entrenador = $this->extractId($entrenador); }
  public function setInstitucion(mixed $institucion): void { $this->institucion = $this->extractId($institucion); }
  public function setNivel(mixed $nivel): void { $this->nivel = $this->extractId($nivel); }
  public function setFechaInicio(string $fecha_inicio): void { $this->fecha_inicio = $fecha_inicio; }
  public function setFechaFin(string $fecha_fin): void { $this->fecha_fin = $fecha_fin; }
  public function setVersion(int $version): void { $this->version = $version; }
  public function setEstado(mixed $estado): void { $this->estado = $this->extractId($estado); }

  private function extractId(mixed $value): ?int
  {
    if ($value === null) return null;
    if (is_object($value) && method_exists($value, 'getId')) return $value->getId();
    return (int) $value;
  }

  public static function from(int $id): ?self
  {
    try {
      $repositorio = new \Repositories\PlanEntrenamientoRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, '', '', null, null, null, null, null, null, 0, null, '', '', 1, 0);
  }
}
