<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/FormaPago.php';

use Core\Database;
use Entitys\FormaPago;
use PDO;

class FormaPagoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(FormaPago $forma): FormaPago
  {
    if ($forma->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO formas_pago (nombre, nemonico, descripcion, estado) VALUES (:nombre, :nemonico, :descripcion, :estado)");
      $stmt->execute($this->params($forma));
      $forma->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE formas_pago SET nombre = :nombre, nemonico = :nemonico, descripcion = :descripcion, estado = :estado WHERE id = :id");
      $params = $this->params($forma);
      $params[':id'] = $forma->getId();
      $stmt->execute($params);
    }
    return $forma;
  }

  public function findById(int $id): ?FormaPago
  {
    $stmt = $this->db->prepare('SELECT * FROM formas_pago WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByNemonico(string $nemonico): ?FormaPago
  {
    $stmt = $this->db->prepare('SELECT * FROM formas_pago WHERE nemonico = :nemonico');
    $stmt->execute([':nemonico' => $nemonico]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM formas_pago ORDER BY nombre ASC');
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(FormaPago $forma): array
  {
    return [
      ':nombre' => $forma->getNombre(),
      ':nemonico' => $forma->getNemonico(),
      ':descripcion' => $forma->getDescripcion(),
      ':estado' => $forma->getEstado()?->getId(),
    ];
  }

  private function map(array $row): FormaPago
  {
    return new FormaPago($row['id'], $row['nombre'], $row['nemonico'], $row['descripcion'], $row['estado']);
  }
}
