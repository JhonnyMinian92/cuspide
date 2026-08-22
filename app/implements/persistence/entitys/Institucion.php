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
      $id = $value->getId();
      // Asumimos 1 como estado por defecto (p. ej. "Activo") si el ID es nulo.
      return $id !== null ? (int) $id : 1;
    }
    if ($value === null) {
      return 1; // Asumimos 1 como estado por defecto
    }
    return (int) $value;
  }

  public static function from($id): ?self
  {
    if (empty($id)) {
      return null;
    }
    try {
      $repositorio = new \Repositories\InstitucionRepository();
      return $repositorio->findById((int) $id);
    } catch (\Exception $e) {
      // Opcional: Loggear el error $e para depuración.
      return null;
    }
  }
}
