<?php

namespace Entitys;

require_once __DIR__ . '/../repository/EvaluacionRepository.php';
require_once __DIR__ . '/Clase.php';
require_once __DIR__ . '/Actividad.php';
require_once __DIR__ . '/Estado.php';

class Evaluacion
{
  private ?int $id;
  private string $nombre;
  private ?int $clase;
  private ?int $actividad;
  private string $fecha;
  private float $peso_porcentaje;
  private int $estado;

  public function __construct(?int $id, string $nombre, mixed $clase, mixed $actividad, string $fecha, float $peso_porcentaje, mixed $estado)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->clase = $this->extractId($clase);
    $this->actividad = $this->extractId($actividad);
    $this->fecha = $fecha;
    $this->peso_porcentaje = $peso_porcentaje;
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
  public function getClase(): ?Clase
  {
    return $this->clase === null ? null : Clase::from($this->clase);
  }
  public function getActividad(): ?Actividad
  {
    return $this->actividad === null ? null : Actividad::from($this->actividad);
  }
  public function getFecha(): string
  {
    return $this->fecha;
  }
  public function getPesoPorcentaje(): float
  {
    return $this->peso_porcentaje;
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
  public function setClase(mixed $clase): void
  {
    $this->clase = $this->extractId($clase);
  }
  public function setActividad(mixed $actividad): void
  {
    $this->actividad = $this->extractId($actividad);
  }
  public function setFecha(string $fecha): void
  {
    $this->fecha = $fecha;
  }
  public function setPesoPorcentaje(float $peso_porcentaje): void
  {
    $this->peso_porcentaje = $peso_porcentaje;
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
    return new self($id, '', null, null, '', 0.0, 0);
  }
}
