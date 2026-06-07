<?php

namespace Entitys;

require_once __DIR__ . '/../repository/InstitucionRepository.php';
require_once __DIR__ . '/Estado.php';

class Institucion
{

  private ?int $id;
  private ?string $ruc;
  private string $razon_social;
  private string $nombre;
  private ?string $logotipo;
  private int $estado;

  public function __construct(?int $id, ?string $ruc, string $razon_social, string $nombre, ?string $logotipo, mixed $estado)
  {
    $this->id = $id;
    $this->ruc = $ruc;
    $this->razon_social = $razon_social;
    $this->nombre = $nombre;
    $this->logotipo = $logotipo;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getRuc(): ?string
  {
    return $this->ruc;
  }

  public function getRazonSocial(): string
  {
    return $this->razon_social;
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function getLogotipo(): ?string
  {
    return $this->logotipo;
  }

  public function getEstado(): ?Estado
  {
    return Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setRuc(?string $ruc): void
  {
    $this->ruc = $ruc;
  }

  public function setRazonSocial(string $razon_social): void
  {
    $this->razon_social = $razon_social;
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public function setLogotipo(?string $logotipo): void
  {
    $this->logotipo = $logotipo;
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
      $repositorio = new \Repositories\InstitucionRepository();
      $institucion = $repositorio->findById($id);
      if ($institucion) {
        return $institucion;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', '', '', '', 1);
  }
}
