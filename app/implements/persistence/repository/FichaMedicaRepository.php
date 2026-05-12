<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/FichaMedica.php';

use Core\Database;
use Entitys\FichaMedica;
use PDO;

class FichaMedicaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(FichaMedica $ficha): FichaMedica
  {
    if ($ficha->getId() === null) {
      $stmt = $this->db->prepare('INSERT INTO fichas_medicas (persona, grupo_sanguineo, alergias, enfermedades_preexistentes, medicamentos_actuales, contacto_emergencia_nombre, contacto_emergencia_telefono, estado) VALUES (:persona, :grupo_sanguineo, :alergias, :enfermedades_preexistentes, :medicamentos_actuales, :contacto_emergencia_nombre, :contacto_emergencia_telefono, :estado)');
      $stmt->execute([
        ':persona' => $ficha->getPersona()?->getId(),
        ':grupo_sanguineo' => $ficha->getGrupoSanguineo(),
        ':alergias' => $ficha->getAlergias(),
        ':enfermedades_preexistentes' => $ficha->getEnfermedadesPreexistentes(),
        ':medicamentos_actuales' => $ficha->getMedicamentosActuales(),
        ':contacto_emergencia_nombre' => $ficha->getContactoEmergenciaNombre(),
        ':contacto_emergencia_telefono' => $ficha->getContactoEmergenciaTelefono(),
        ':estado' => $ficha->getEstado()?->getId(),
      ]);
      $ficha->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare('UPDATE fichas_medicas SET persona = :persona, grupo_sanguineo = :grupo_sanguineo, alergias = :alergias, enfermedades_preexistentes = :enfermedades_preexistentes, medicamentos_actuales = :medicamentos_actuales, contacto_emergencia_nombre = :contacto_emergencia_nombre, contacto_emergencia_telefono = :contacto_emergencia_telefono, estado = :estado WHERE id = :id');
      $stmt->execute([
        ':id' => $ficha->getId(),
        ':persona' => $ficha->getPersona()?->getId(),
        ':grupo_sanguineo' => $ficha->getGrupoSanguineo(),
        ':alergias' => $ficha->getAlergias(),
        ':enfermedades_preexistentes' => $ficha->getEnfermedadesPreexistentes(),
        ':medicamentos_actuales' => $ficha->getMedicamentosActuales(),
        ':contacto_emergencia_nombre' => $ficha->getContactoEmergenciaNombre(),
        ':contacto_emergencia_telefono' => $ficha->getContactoEmergenciaTelefono(),
        ':estado' => $ficha->getEstado()?->getId(),
      ]);
    }
    return $ficha;
  }

  public function findById(int $id): ?FichaMedica
  {
    $stmt = $this->db->prepare('SELECT * FROM fichas_medicas WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? new FichaMedica($row['id'], $row['persona'], $row['grupo_sanguineo'], $row['alergias'], $row['enfermedades_preexistentes'], $row['medicamentos_actuales'], $row['contacto_emergencia_nombre'], $row['contacto_emergencia_telefono'], $row['estado']) : null;
  }

  /**
   * @param int $personaId
   * @return FichaMedica[]
   */
  public function findByPersonaId(int $personaId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM fichas_medicas WHERE persona = :personaId AND estado = 1');
    $stmt->execute([':personaId' => $personaId]);
    $fichas = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $fichas[] = new FichaMedica(
        $row['id'],
        $row['persona'],
        $row['grupo_sanguineo'],
        $row['alergias'],
        $row['enfermedades_preexistentes'],
        $row['medicamentos_actuales'],
        $row['contacto_emergencia_nombre'],
        $row['contacto_emergencia_telefono'],
        $row['estado']
      );
    }
    return $fichas;
  }
}
