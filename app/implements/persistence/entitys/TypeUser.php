<?php

namespace Entitys;

require_once __DIR__ . '/../repository/TypeUserRepository.php';

class TypeUser
{
  private string $nemonico;
  private string $nombre;
  private string $descripcion;
  private string $imagen;

  public function __construct(string $nemonico, string $nombre, string $descripcion, string $imagen)
  {
    $this->nemonico = $nemonico;
    $this->nombre = $nombre;
    $this->descripcion = $descripcion;
    $this->imagen = $imagen;
  }

  public function getNemonico(): string
  {
    return $this->nemonico;
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function getDescripcion(): string
  {
    return $this->descripcion;
  }

  public function getImagen(): string
  {
    return $this->imagen;
  }

  public function setNemonico(string $nemonico): void
  {
    $this->nemonico = $nemonico;
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public function setDescripcion(string $descripcion): void
  {
    $this->descripcion = $descripcion;
  }

  public function setImagen(string $imagen): void
  {
    $this->imagen = $imagen;
  }

  public static function from($nemonico): ?self
  {
    try {
      $repositorio = new \Repositories\TypeUserRepository();
      $typeUser = $repositorio->findByNemonico($nemonico);
      if ($typeUser) {
        return $typeUser;
      }
    } catch (\Exception $e) {
    }
    return new self($nemonico, '', '', '');
  }
}
