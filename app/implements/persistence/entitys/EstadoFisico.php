<?php

namespace Entitys;

require_once __DIR__ . '/../repository/EstadoFisicoRepository.php';
require_once __DIR__ . '/PerfilMedico.php';
require_once __DIR__ . '/TipoEstadoFisico.php';

class EstadoFisico
{
  private ?int $id;
  private int $perfil_medico;
  private int $tipo;
  private string $descripcion;

  public function __construct(?int $id, mixed $perfil_medico, mixed $tipo, string $descripcion)
  {
    $this->id = $id;
    $this->perfil_medico = $this->extractId($perfil_medico);
    $this->tipo = $this->extractId($tipo);
    $this->descripcion = $descripcion;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getPerfilMedico(): ?PerfilMedico
  {
    return $this->perfil_medico === null ? null : PerfilMedico::from($this->perfil_medico);
  }

  public function getTipo(): ?TipoEstadoFisico
  {
    return $this->tipo === null ? null : TipoEstadoFisico::from($this->tipo);
  }

  public function getDescripcion(): string
  {
    return $this->descripcion;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setPerfilMedico(mixed $perfil_medico): void
  {
    $this->perfil_medico = $this->extractId($perfil_medico);
  }

  public function setTipo(mixed $tipo): void
  {
    $this->tipo = $this->extractId($tipo);
  }

  public function setDescripcion(string $descripcion): void
  {
    $this->descripcion = $descripcion;
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
      $repositorio = new \Repositories\EstadoFisicoRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, 0, '');
  }
}
