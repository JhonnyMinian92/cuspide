<?php

namespace Entitys;

require_once __DIR__ . '/../repository/EstadoRepository.php';

class Estado
{
  private ?int $id;
  private string $nombre;

  public function __construct(?int $id, string $nombre)
  {
    $this->id = $id;
    $this->nombre = $nombre;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public static function from($id): ?self
  {
    try {
      $repositorio = new \Repositories\EstadoRepository();
      $estado = $repositorio->findById($id);
      if ($estado) {
        return $estado;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '');
  }
}
