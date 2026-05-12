<?php

namespace Entitys;

require_once __DIR__ . '/../repository/EnfermedadRepository.php';
require_once __DIR__ . '/TipoEnfermedad.php';
require_once __DIR__ . '/Nivel.php';

class Enfermedad
{
  private ?int $id;
  private int $tipo;
  private string $nombre;
  private string $nemonico;
  private int $nivel;

  public function __construct(?int $id, mixed $tipo, string $nombre, string $nemonico, mixed $nivel)
  {
    $this->id = $id;
    $this->tipo = $this->extractId($tipo);
    $this->nombre = $nombre;
    $this->nemonico = $nemonico;
    $this->nivel = $this->extractId($nivel);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getTipo(): ?TipoEnfermedad
  {
    return $this->tipo === null ? null : TipoEnfermedad::from($this->tipo);
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function getNemonico(): string
  {
    return $this->nemonico;
  }

  public function getNivel(): ?Nivel
  {
    return $this->nivel === null ? null : Nivel::from($this->nivel);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setTipo(mixed $tipo): void
  {
    $this->tipo = $this->extractId($tipo);
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public function setNemonico(string $nemonico): void
  {
    $this->nemonico = $nemonico;
  }

  public function setNivel(mixed $nivel): void
  {
    $this->nivel = $this->extractId($nivel);
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
      $repositorio = new \Repositories\EnfermedadRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, '', '', 0);
  }
}
