<?php

namespace Entitys;

require_once __DIR__ . '/../repository/AlergiaRepository.php';
require_once __DIR__ . '/TipoAlergia.php';
require_once __DIR__ . '/Nivel.php';
require_once __DIR__ . '/FichaMedica.php';

class Alergia
{
  private ?int $id;
  private int $tipo;
  private string $nombre;
  private string $descripcion;
  private int $nivel;
  private int $ficha_medica;

  public function __construct(?int $id, mixed $tipo, string $nombre, string $descripcion, mixed $nivel, mixed $ficha_medica)
  {
    $this->id = $id;
    $this->tipo = $this->extractId($tipo);
    $this->nombre = $nombre;
    $this->descripcion = $descripcion;
    $this->nivel = $this->extractId($nivel);
    $this->ficha_medica = $this->extractId($ficha_medica);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getTipo(): ?TipoAlergia
  {
    return $this->tipo === null ? null : TipoAlergia::from($this->tipo);
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function getDescripcion(): string
  {
    return $this->descripcion;
  }

  public function getNivel(): ?Nivel
  {
    return $this->nivel === null ? null : Nivel::from($this->nivel);
  }

  public function getFichaMedica(): ?FichaMedica
  {
    return $this->ficha_medica === null ? null : FichaMedica::from($this->ficha_medica);
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

  public function setDescripcion(string $descripcion): void
  {
    $this->descripcion = $descripcion;
  }

  public function setNivel(mixed $nivel): void
  {
    $this->nivel = $this->extractId($nivel);
  }

  public function setFichaMedica(mixed $ficha_medica): void
  {
    $this->ficha_medica = $this->extractId($ficha_medica);
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
      $repositorio = new \Repositories\AlergiaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, '', '', 0, 0);
  }
}
