<?php

namespace Entitys;

require_once __DIR__ . '/../repository/HorarioRepository.php';
require_once __DIR__ . '/Clase.php';
require_once __DIR__ . '/Actividad.php';
require_once __DIR__ . '/CategoriaDeportiva.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Estado.php';

class Horario
{
  private ?int $id;
  private string $nombre;
  private int $institucion;
  private ?int $clase;
  private ?int $actividad;
  private ?int $categoria_deportiva;
  private ?int $entrenador;
  private int $dia_semana;
  private string $hora_inicio;
  private string $hora_fin;
  private ?string $fecha_inicio;
  private ?string $fecha_fin;
  private int $cupo_maximo;
  private string $ubicacion;
  private int $estado;

  public function __construct(?int $id, string $nombre, mixed $institucion, mixed $clase, mixed $actividad, mixed $categoria_deportiva, mixed $entrenador, int $dia_semana, string $hora_inicio, string $hora_fin, ?string $fecha_inicio, ?string $fecha_fin, int $cupo_maximo, string $ubicacion, mixed $estado)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->institucion = $this->extractId($institucion);
    $this->clase = $this->extractId($clase);
    $this->actividad = $this->extractId($actividad);
    $this->categoria_deportiva = $this->extractId($categoria_deportiva);
    $this->entrenador = $this->extractId($entrenador);
    $this->dia_semana = $dia_semana;
    $this->hora_inicio = $hora_inicio;
    $this->hora_fin = $hora_fin;
    $this->fecha_inicio = $fecha_inicio;
    $this->fecha_fin = $fecha_fin;
    $this->cupo_maximo = $cupo_maximo;
    $this->ubicacion = $ubicacion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getNombre(): string { return $this->nombre; }
  public function getInstitucion(): ?Institucion { return $this->institucion === null ? null : Institucion::from($this->institucion); }
  public function getClase(): ?Clase { return $this->clase === null ? null : Clase::from($this->clase); }
  public function getActividad(): ?Actividad { return $this->actividad === null ? null : Actividad::from($this->actividad); }
  public function getCategoriaDeportiva(): ?CategoriaDeportiva { return $this->categoria_deportiva === null ? null : CategoriaDeportiva::from($this->categoria_deportiva); }
  public function getEntrenador(): ?Persona { return $this->entrenador === null ? null : Persona::from($this->entrenador); }
  public function getDiaSemana(): int { return $this->dia_semana; }
  public function getHoraInicio(): string { return $this->hora_inicio; }
  public function getHoraFin(): string { return $this->hora_fin; }
  public function getFechaInicio(): ?string { return $this->fecha_inicio; }
  public function getFechaFin(): ?string { return $this->fecha_fin; }
  public function getCupoMaximo(): int { return $this->cupo_maximo; }
  public function getUbicacion(): string { return $this->ubicacion; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setNombre(string $nombre): void { $this->nombre = $nombre; }
  public function setInstitucion(mixed $institucion): void { $this->institucion = $this->extractId($institucion); }
  public function setClase(mixed $clase): void { $this->clase = $this->extractId($clase); }
  public function setActividad(mixed $actividad): void { $this->actividad = $this->extractId($actividad); }
  public function setCategoriaDeportiva(mixed $categoria_deportiva): void { $this->categoria_deportiva = $this->extractId($categoria_deportiva); }
  public function setEntrenador(mixed $entrenador): void { $this->entrenador = $this->extractId($entrenador); }
  public function setDiaSemana(int $dia_semana): void { $this->dia_semana = $dia_semana; }
  public function setHoraInicio(string $hora_inicio): void { $this->hora_inicio = $hora_inicio; }
  public function setHoraFin(string $hora_fin): void { $this->hora_fin = $hora_fin; }
  public function setFechaInicio(?string $fecha_inicio): void { $this->fecha_inicio = $fecha_inicio; }
  public function setFechaFin(?string $fecha_fin): void { $this->fecha_fin = $fecha_fin; }
  public function setCupoMaximo(int $cupo_maximo): void { $this->cupo_maximo = $cupo_maximo; }
  public function setUbicacion(string $ubicacion): void { $this->ubicacion = $ubicacion; }
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
      $repositorio = new \Repositories\HorarioRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, '', 0, null, null, null, null, 1, '00:00:00', '00:00:00', null, null, 0, '', 0);
  }
}
