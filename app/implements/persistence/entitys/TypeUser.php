<?php

namespace Entitys;

class TypeUser
{
  private $nemonico;
  private $nombre;
  private $descripcion;
  private $imagen;

  public function __construct($nemonico, $nombre, $descripcion, $imagen)
  {
    $this->nemonico = $nemonico;
    $this->nombre = $nombre;
    $this->descripcion = $descripcion;
    $this->imagen = $imagen;
  }

  public function getNemonico()
  {
    return $this->nemonico;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  public function getDescripcion()
  {
    return $this->descripcion;
  }

  public function getImagen()
  {
    return $this->imagen;
  }

  public function setNemonico($nemonico)
  {
    $this->nemonico = $nemonico;
  }

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setDescripcion($descripcion)
  {
    $this->descripcion = $descripcion;
  }

  public function setImagen($imagen)
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