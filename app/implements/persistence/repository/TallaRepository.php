<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Talla.php';

use Core\Database;
use Entitys\Talla;
use PDO;

class TallaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Talla $talla): Talla
  {
    if ($talla->getId() === null) {
      $stmt = $this->db->prepare('INSERT INTO tallas (persona, tipo_prenda, talla, medida_cm, fecha_medicion, estado) VALUES (:persona, :tipo_prenda, :talla, :medida_cm, :fecha_medicion, :estado)');
      $stmt->execute([
        ':persona' => $talla->getPersona()?->getId(),
        ':tipo_prenda' => $talla->getTipoPrenda(),
        ':talla' => $talla->getTalla(),
        ':medida_cm' => $talla->getMedidaCm(),
        ':fecha_medicion' => $talla->getFechaMedicion(),
        ':estado' => $talla->getEstado()?->getId(),
      ]);
      $talla->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare('UPDATE tallas SET persona = :persona, tipo_prenda = :tipo_prenda, talla = :talla, medida_cm = :medida_cm, fecha_medicion = :fecha_medicion, estado = :estado WHERE id = :id');
      $stmt->execute([
        ':id' => $talla->getId(),
        ':persona' => $talla->getPersona()?->getId(),
        ':tipo_prenda' => $talla->getTipoPrenda(),
        ':talla' => $talla->getTalla(),
        ':medida_cm' => $talla->getMedidaCm(),
        ':fecha_medicion' => $talla->getFechaMedicion(),
        ':estado' => $talla->getEstado()?->getId(),
      ]);
    }
    return $talla;
  }

  public function findById(int $id): ?Talla
  {
    $stmt = $this->db->prepare('SELECT * FROM tallas WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? new Talla($row['id'], $row['persona'], $row['tipo_prenda'], $row['talla'], $row['medida_cm'], $row['fecha_medicion'], $row['estado']) : null;
  }

  /**
   * @param int $personaId
   * @return Talla[]
   */
  public function findByPersonaId(int $personaId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM tallas WHERE persona = :personaId AND estado = 1 ORDER BY fecha_medicion DESC');
    $stmt->execute([':personaId' => $personaId]);
    $tallas = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $tallas[] = new Talla(
        $row['id'],
        $row['persona'],
        $row['tipo_prenda'],
        $row['talla'],
        $row['medida_cm'],
        $row['fecha_medicion'],
        $row['estado']
      );
    }
    return $tallas;
  }
}
