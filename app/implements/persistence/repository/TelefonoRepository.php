<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Persona.php'; // Required for Persona entity
require_once __DIR__ . '/../entitys/Telefono.php';
require_once __DIR__ . '/../entitys/Operadora.php'; // Assuming these entities exist
require_once __DIR__ . '/../entitys/TipoContacto.php'; // Assuming these entities exist
require_once __DIR__ . '/../entitys/Estado.php'; // Assuming these entities exist

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

  public function findByPersonaId($personaId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM telefonos WHERE persona = :personaId AND estado = 1');
    $stmt->execute([':personaId' => $personaId]);
    $telefonos = [];

    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $telefonos[] = new Telefono($data['id'], $data['numero'], $data['operadora'], $data['persona'], $data['tipo'], $data['estado']);
    }

    return $telefonos;
  }

  public function syncForPersona(int $personaId, array $newTelefonos): void
  {
    // Delete existing phones for this persona
    $stmt = $this->db->prepare('DELETE FROM telefonos WHERE persona = :personaId');
    $stmt->execute([':personaId' => $personaId]);

    // Insert new phones
    foreach ($newTelefonos as $telData) {
      $numero = $telData['numero'] ?? '';
      if ($numero === '') continue;

      // Assuming default values for operadora, tipo, estado if not provided by request
      // You might need to fetch these from your database or define constants
      // For now, using dummy IDs. Adjust as per your actual default IDs.
      $defaultOperadoraId = 1; // Example: ID for 'Claro' or 'Movistar'
      $defaultTipoId = 1;      // Example: ID for 'Movil'
      $defaultEstadoId = 1;    // Example: ID for 'Activo'

      $telefono = new Telefono(
        null,
        $numero,
        $defaultOperadoraId,
        $personaId,
        $defaultTipoId,
        $defaultEstadoId
      );
      $this->save($telefono);
    }
  }
}
