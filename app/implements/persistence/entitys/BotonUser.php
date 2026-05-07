<?php

namespace Entitys;

require_once __DIR__ . '/Boton.php';
require_once __DIR__ . '/TypeUser.php';

class BotonUser
{
  private ?int $id;
  private int $boton;
  private string $typeUser;

  public function __construct(?int $id, mixed $boton, mixed $typeUser)
  {
    $this->id = $id;
    $this->boton = $this->extractBotonId($boton);
    $this->typeUser = $this->extractTypeUserNemonico($typeUser);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getBoton(): ?Boton
  {
    return Boton::from($this->boton);
  }

  public function getTypeUser(): ?TypeUser
  {
    return TypeUser::from($this->typeUser);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setBoton(mixed $boton): void
  {
    $this->boton = $this->extractBotonId($boton);
  }

  public function setTypeUser(mixed $typeUser): void
  {
    $this->typeUser = $this->extractTypeUserNemonico($typeUser);
  }

  private function extractBotonId(mixed $value): int
  {
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return $value;
  }

  private function extractTypeUserNemonico(mixed $value): string
  {
    if (is_object($value) && method_exists($value, 'getNemonico')) {
      return $value->getNemonico();
    }
    return $value;
  }
}
