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
      $stmt = $this->db->prepare("INSERT INTO membresias (titulo, precio, porcentaje, descripcion, url_boton, fecha_inicio, fecha_fin, periodo, estado, institucion) VALUES (:titulo, :precio, :porcentaje, :descripcion, :url_boton, :fecha_inicio, :fecha_fin, :periodo, :estado, :institucion)");
      $stmt->execute($this->params($membresia));
      $membresia->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE membresias SET titulo = :titulo, precio = :precio, porcentaje = :porcentaje, descripcion = :descripcion, url_boton = :url_boton, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, periodo = :periodo, estado = :estado, institucion = :institucion WHERE id = :id");
      $params = $this->params($membresia);
      $params[':id'] = $membresia->getId();
      $stmt->execute($params);
    }
    return $membresia;
  }

  public function findById(int $id): ?Membresia
  {
    $stmt = $this->db->prepare('SELECT * FROM membresias WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findAll(): array
  {
    $rows = $this->db->query('SELECT * FROM membresias ORDER BY fecha_inicio DESC')->fetchAll(PDO::FETCH_ASSOC);
    return array_map(fn($row) => $this->map($row), $rows ?: []);
  }

  public function findByInstitucion(int $instId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM membresias WHERE (institucion = :inst OR institucion IS NULL) ORDER BY fecha_inicio DESC');
    $stmt->execute([':inst' => $instId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(Membresia $m): array
  {
    return [
      ':titulo'       => $m->getTitulo(),
      ':precio'       => $m->getPrecio(),
      ':porcentaje'   => $m->getPorcentaje(),
      ':descripcion'  => $m->getDescripcion(),
      ':url_boton'    => $m->getUrlBoton(),
      ':fecha_inicio' => $m->getFechaInicio(),
      ':fecha_fin'    => $m->getFechaFin(),
      ':periodo'      => $m->getPeriodo(),
      ':estado'       => $m->getEstado()?->getId(),
      ':institucion'  => $m->getInstitucion(),
    ];
  }

  private function map(array $row): Membresia
  {
    return new Membresia($row['id'], $row['titulo'], $row['precio'], (float) $row['porcentaje'], $row['descripcion'] ?? '', $row['url_boton'] ?? '', $row['fecha_inicio'], $row['fecha_fin'], $row['estado'], $row['institucion'] ?? null, $row['periodo'] ?? 'mensual');
  }
}
