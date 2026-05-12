<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Boton.php';

use Core\Database;
use Entitys\Boton;
use PDO;

class BotonRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Boton $boton): Boton
  {
    if ($boton->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO botones (orden, nombre, icono, tipo, color, estado) VALUES (:orden, :nombre, :icono, :tipo, :color, :estado)");
      $stmt->execute([
        ':orden' => $boton->getOrden(),
        ':nombre' => $boton->getNombre(),
        ':icono' => $boton->getIcono(),
        ':tipo' => $boton->getTipo(),
        ':color' => $boton->getColor(),
        ':estado' => $boton->getEstado()->getId()
      ]);
      $boton->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE botones SET orden = :orden, nombre = :nombre, icono = :icono, tipo = :tipo, color = :color, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $boton->getId(),
        ':orden' => $boton->getOrden(),
        ':nombre' => $boton->getNombre(),
        ':icono' => $boton->getIcono(),
        ':tipo' => $boton->getTipo(),
        ':color' => $boton->getColor(),
        ':estado' => $boton->getEstado()->getId()
      ]);
    }

    return $boton;
  }

  public function findById($id): ?Boton
  {
    $stmt = $this->db->prepare('SELECT * FROM botones WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Boton($row['id'], $row['orden'], $row['nombre'], $row['icono'], $row['tipo'], $row['color'], $row['estado']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM botones ORDER BY orden ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Boton($row['id'], $row['orden'], $row['nombre'], $row['icono'], $row['tipo'], $row['color'], $row['estado']), $rows ?: []);
  }
}
