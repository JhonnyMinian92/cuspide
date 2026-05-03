<?php

namespace Entitys;

class Boton
{
  private $id;
  private $orden;
  private $nombre;
  private $icono;
  private $tipo;
  private $color;

  public function __construct($id, $orden, $nombre, $icono, $tipo, $color)
  {
    $this->id = $id;
    $this->orden = $orden;
    $this->nombre = $nombre;
    $this->icono = $icono;
    $this->tipo = $tipo;
    $this->color = $color;
  }

  public function getId() { return $this->id; }
  public function getOrden() { return $this->orden; }
  public function getNombre() { return $this->nombre; }
  public function getIcono() { return $this->icono; }
  public function getTipo() { return $this->tipo; }
  public function getColor() { return $this->color; }

  public function setId($id) { $this->id = $id; }
}
