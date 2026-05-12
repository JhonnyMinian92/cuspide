<?php

namespace Entitys;

require_once __DIR__ . '/../repository/BotonRepository.php';
require_once __DIR__ . '/Estado.php';

class Boton
{
  private ?int $id;
  private ?int $orden;
  private string $nombre;
  private string $icono;
  private string $tipo;
  private ?string $color;
  private int $estado;

  public function __construct(?int $id, ?int $orden, string $nombre, string $icono, string $tipo, ?string $color, mixed $estado)
  {
    $this->id = $id;
    $this->orden = $orden;
    $this->nombre = $nombre;
    $this->icono = $icono;
    $this->tipo = $tipo;
    $this->color = $color;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getOrden(): ?int
  {
    return $this->orden;
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function getIcono(): string
  {
    return $this->icono;
  }

  public function getTipo(): string
  {
    return $this->tipo;
  }

  public function getColor(): ?string
  {
    return $this->color;
  }

  public function getEstado(): ?Estado
  {
    return Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setOrden(?int $orden): void
  {
    $this->orden = $orden;
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public function setIcono(string $icono): void
  {
    $this->icono = $icono;
  }

  public function setTipo(string $tipo): void
  {
    $this->tipo = $tipo;
  }

  public function setColor(?string $color): void
  {
    $this->color = $color;
  }

  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  private function extractId(mixed $value): int
  {
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return $value;
  }

  public static function from($id): ?self
  {
    try {
      $repositorio = new \Repositories\BotonRepository();
      $boton = $repositorio->findById($id);
      if ($boton) {
        return $boton;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, '', '', '', '', 0);
  }
}
