<?php

namespace Entitys;

require_once __DIR__ . '/../repository/CiudadRepository.php';

class Ciudad
{
  private ?int $id;
  private string $nemonico;
  private string $nombre;
  private string $provincia;

  public function __construct(?int $id, string $nemonico, string $nombre, string $provincia)
  {
    $this->id = $id;
    $this->nemonico = $nemonico;
    $this->nombre = $nombre;
    $this->provincia = $provincia;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getNemonico(): string
  {
    return $this->nemonico;
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function getProvincia(): string
  {
    return $this->provincia;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setNemonico(string $nemonico): void
  {
    $this->nemonico = $nemonico;
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public function setProvincia(string $provincia): void
  {
    $this->provincia = $provincia;
  }

  public static function from($id): ?self
  {
    try {
      $repositorio = new \Repositories\CiudadRepository();
      $ciudad = $repositorio->findById($id);
      if ($ciudad) {
        return $ciudad;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', '', '');
  }
}
