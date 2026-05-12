<?php

namespace Entitys;

require_once __DIR__ . '/../repository/TorneoRepository.php';
require_once __DIR__ . '/Deporte.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Estado.php';

class Torneo
{
  private ?int $id;
  private string $nombre;
  private string $fecha_inicio;
  private string $fecha_fin;
  private int $deporte;
  private int $institucion;
  private int $estado;

  public function __construct(?int $id, string $nombre, string $fecha_inicio, string $fecha_fin, mixed $deporte, mixed $institucion, mixed $estado)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->fecha_inicio = $fecha_inicio;
    $this->fecha_fin = $fecha_fin;
    $this->deporte = $this->extractId($deporte);
    $this->institucion = $this->extractId($institucion);
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
  public function getFechaInicio(): string
  {
    return $this->fecha_inicio;
  }
  public function getFechaFin(): string
  {
    return $this->fecha_fin;
  }
  public function getDeporte(): ?Deporte
  {
    return $this->deporte === null ? null : Deporte::from($this->deporte);
  }
  public function getInstitucion(): ?Institucion
  {
    return $this->institucion === null ? null : Institucion::from($this->institucion);
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
  public function setFechaInicio(string $fecha_inicio): void
  {
    $this->fecha_inicio = $fecha_inicio;
  }
  public function setFechaFin(string $fecha_fin): void
  {
    $this->fecha_fin = $fecha_fin;
  }
  public function setDeporte(mixed $deporte): void
  {
    $this->deporte = $this->extractId($deporte);
  }
  public function setInstitucion(mixed $institucion): void
  {
    $this->institucion = $this->extractId($institucion);
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
    return new self($id, '', '', '', 0, 0, 0); // Lógica de carga lazy o mock según necesidades
  }
}
