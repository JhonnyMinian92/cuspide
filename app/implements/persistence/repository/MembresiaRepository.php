<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Membresia.php';

use Core\Database;
use Entitys\Membresia;
use PDO;

class MembresiaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Membresia $membresia): Membresia
  {
    if ($membresia->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO membresias (titulo, precio, porcentaje, descripcion, url_boton, fecha_inicio, fecha_fin, estado) VALUES (:titulo, :precio, :porcentaje, :descripcion, :url_boton, :fecha_inicio, :fecha_fin, :estado)");
      $stmt->execute([
        ':titulo' => $membresia->getTitulo(),
        ':precio' => $membresia->getPrecio(),
        ':porcentaje' => $membresia->getPorcentaje(),
        ':descripcion' => $membresia->getDescripcion(),
        ':url_boton' => $membresia->getUrlBoton(),
        ':fecha_inicio' => $membresia->getFechaInicio(),
        ':fecha_fin' => $membresia->getFechaFin(),
        ':estado' => $membresia->getEstado()?->getId(),
      ]);
      $membresia->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE membresias SET titulo = :titulo, precio = :precio, porcentaje = :porcentaje, descripcion = :descripcion, url_boton = :url_boton, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $membresia->getId(),
        ':titulo' => $membresia->getTitulo(),
        ':precio' => $membresia->getPrecio(),
        ':porcentaje' => $membresia->getPorcentaje(),
        ':descripcion' => $membresia->getDescripcion(),
        ':url_boton' => $membresia->getUrlBoton(),
        ':fecha_inicio' => $membresia->getFechaInicio(),
        ':fecha_fin' => $membresia->getFechaFin(),
        ':estado' => $membresia->getEstado()?->getId(),
      ]);
    }

    return $membresia;
  }

  public function findById(int $id): ?Membresia
  {
    $stmt = $this->db->prepare('SELECT * FROM membresias WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Membresia($row['id'], $row['titulo'], $row['precio'], (float)$row['porcentaje'], $row['descripcion'] ?? '', $row['url_boton'] ?? '', $row['fecha_inicio'], $row['fecha_fin'], $row['estado']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM membresias ORDER BY fecha_inicio DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Membresia($row['id'], $row['titulo'], $row['precio'], (float)$row['porcentaje'], $row['descripcion'] ?? '', $row['url_boton'] ?? '', $row['fecha_inicio'], $row['fecha_fin'], $row['estado']), $rows ?: []);
  }
}
