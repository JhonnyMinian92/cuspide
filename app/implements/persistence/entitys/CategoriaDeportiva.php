<?php

namespace Entitys;

require_once __DIR__ . '/../repository/CategoriaDeportivaRepository.php';
require_once __DIR__ . '/Deporte.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Estado.php';

class CategoriaDeportiva
{
  private ?int $id;
  private string $nombre;
  private string $nemonico;
  private int $deporte;
  private ?int $edad_minima;
  private ?int $edad_maxima;
  private string $descripcion;
  private int $institucion;
  private int $estado;

  public function __construct(?int $id, string $nombre, string $nemonico, mixed $deporte, ?int $edad_minima, ?int $edad_maxima, string $descripcion, mixed $institucion, mixed $estado)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->nemonico = $nemonico;
    $this->deporte = $this->extractId($deporte);
    $this->edad_minima = $edad_minima;
    $this->edad_maxima = $edad_maxima;
    $this->descripcion = $descripcion;
    $this->institucion = $this->extractId($institucion);
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getNombre(): string { return $this->nombre; }
  public function getNemonico(): string { return $this->nemonico; }
  public function getDeporte(): ?Deporte { return $this->deporte === null ? null : Deporte::from($this->deporte); }
  public function getEdadMinima(): ?int { return $this->edad_minima; }
  public function getEdadMaxima(): ?int { return $this->edad_maxima; }
  public function getDescripcion(): string { return $this->descripcion; }
  public function getInstitucion(): ?Institucion { return $this->institucion === null ? null : Institucion::from($this->institucion); }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setNombre(string $nombre): void { $this->nombre = $nombre; }
  public function setNemonico(string $nemonico): void { $this->nemonico = $nemonico; }
  public function setDeporte(mixed $deporte): void { $this->deporte = $this->extractId($deporte); }
  public function setEdadMinima(?int $edad_minima): void { $this->edad_minima = $edad_minima; }
  public function setEdadMaxima(?int $edad_maxima): void { $this->edad_maxima = $edad_maxima; }
  public function setDescripcion(string $descripcion): void { $this->descripcion = $descripcion; }
  public function setInstitucion(mixed $institucion): void { $this->institucion = $this->extractId($institucion); }
  public function setEstado(mixed $estado): void { $this->estado = $this->extractId($estado); }

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
      $repositorio = new \Repositories\CategoriaDeportivaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, '', '', 0, null, null, '', 0, 0);
  }
}
