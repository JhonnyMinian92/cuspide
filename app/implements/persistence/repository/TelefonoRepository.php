<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Telefono.php';

use Core\Database;
use Entitys\Telefono;
use PDO;

class TelefonoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Telefono $telefono): Telefono
  {
    if ($telefono->getId() === null) {
      $stmt = $this->db->prepare('INSERT INTO telefonos (numero, operadora, persona, tipo, estado) VALUES (:numero, :operadora, :persona, :tipo, :estado)');
      $stmt->execute([
        ':numero' => $telefono->getNumero(),
        ':operadora' => $telefono->getOperadora()->getId(),
        ':persona' => $telefono->getPersona()->getId(),
        ':tipo' => $telefono->getTipo()->getId(),
        ':estado' => $telefono->getEstado()->getId(),
      ]);
      $telefono->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare('UPDATE telefonos SET numero = :numero, operadora = :operadora, persona = :persona, tipo = :tipo, estado = :estado WHERE id = :id');
      $stmt->execute([
        ':id' => $telefono->getId(),
        ':numero' => $telefono->getNumero(),
        ':operadora' => $telefono->getOperadora()->getId(),
        ':persona' => $telefono->getPersona()->getId(),
        ':tipo' => $telefono->getTipo()->getId(),
        ':estado' => $telefono->getEstado()->getId(),
      ]);
    }

    return $telefono;
  }

  public function findById($id): ?Telefono
  {
    $stmt = $this->db->prepare('SELECT * FROM telefonos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
      return new Telefono($data['id'], $data['numero'], $data['operadora'], $data['persona'], $data['tipo'], $data['estado']);
    }

    return null;
  }
}
