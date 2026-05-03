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

  /** @return Boton[] */
  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM botones ORDER BY orden ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Boton($row['id'], $row['orden'], $row['nombre'], $row['icono'], $row['tipo'], $row['color']), $rows ?: []);
  }
}
