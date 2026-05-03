<?php

namespace Entitys;

require_once __DIR__ . '/../repository/BotonRepository.php';
require_once __DIR__ . '/Estado.php';

class Boton
{
  private $id;
  private $orden;
  private $nombre;
  private $icono;
  private $tipo;
  private $color;
  private $estado;

  public function __construct($id, $orden, $nombre, $icono, $tipo, $color, $estado)
  {
    $this->id = $id;
    $this->orden = $orden;
    $this->nombre = $nombre;
    $this->icono = $icono;
    $this->tipo = $tipo;
    $this->color = $color;
    $this->estado = $estado;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getOrden()
  {
    return $this->orden;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  public function getIcono()
  {
    return $this->icono;
  }

  public function getTipo()
  {
    return $this->tipo;
  }

  public function getColor()
  {
    return $this->color;
  }

  public function getEstado()
  {
    return Estado::from($this->estado);
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setOrden($orden)
  {
    $this->orden = $orden;
  }

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setIcono($icono)
  {
    $this->icono = $icono;
  }

  public function setTipo($tipo)
  {
    $this->tipo = $tipo;
  }

  public function setColor($color)
  {
    $this->color = $color;
  }

  public function setEstado($estado)
  {
    $this->estado = is_string($estado) ? $estado : $estado->getId();
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
    return new self($id, '', '', '', '', '', 0, 0, 0, '', '', '', 0);
  }
}