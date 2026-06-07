<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Permiso.php';

use Core\Database;
use Entitys\Permiso;
use PDO;

class PermisoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function save(Permiso $permiso): Permiso
    {
        if ($permiso->getId() === null) {
            $stmt = $this->db->prepare(
                "INSERT INTO permisos (persona, matricula, clase, actividad, fecha_inicio, fecha_fin, horas_por_dia, motivo, observacion, estado)
                 VALUES (:persona, :matricula, :clase, :actividad, :fecha_inicio, :fecha_fin, :horas_por_dia, :motivo, :observacion, :estado)"
            );
        } else {
            $stmt = $this->db->prepare(
                "UPDATE permisos SET persona=:persona, matricula=:matricula, clase=:clase, actividad=:actividad,
                 fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin, horas_por_dia=:horas_por_dia,
                 motivo=:motivo, observacion=:observacion, estado=:estado WHERE id=:id"
            );
            $stmt->bindValue(':id', $permiso->getId(), PDO::PARAM_INT);
        }

        $stmt->execute([
            ':persona'      => $permiso->getPersona(),
            ':matricula'    => $permiso->getMatricula(),
            ':clase'        => $permiso->getClase(),
            ':actividad'    => $permiso->getActividad(),
            ':fecha_inicio' => $permiso->getFechaInicio(),
            ':fecha_fin'    => $permiso->getFechaFin(),
            ':horas_por_dia'=> $permiso->getHorasPorDia(),
            ':motivo'       => $permiso->getMotivo(),
            ':observacion'  => $permiso->getObservacion(),
            ':estado'       => $permiso->getEstado(),
        ]);

        if ($permiso->getId() === null) {
            $permiso->setId((int) $this->db->lastInsertId());
        }

        return $permiso;
    }

    public function findById(int $id): ?Permiso
    {
        $stmt = $this->db->prepare('SELECT * FROM permisos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->rowToEntity($row) : null;
    }

    public function findByPersonaId(int $personaId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM permisos WHERE persona = :p AND estado != 0 ORDER BY fecha_inicio DESC');
        $stmt->execute([':p' => $personaId]);
        return array_map([$this, 'rowToEntity'], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function findByInstitucion(int $instId, ?string $fechaDesde = null): array
    {
        $sql = "SELECT p.*, per.primer_nombre, per.primer_apellido, per.identificacion
                FROM permisos p
                JOIN personas per ON per.id = p.persona
                JOIN persona_institucion pi ON pi.id_persona = p.persona AND pi.id_institucion = :inst
                WHERE p.estado != 0";
        $params = [':inst' => $instId];
        if ($fechaDesde) {
            $sql .= " AND p.fecha_inicio >= :desde";
            $params[':desde'] = $fechaDesde;
        }
        $sql .= " ORDER BY p.fecha_inicio DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function rowToEntity(array $row): Permiso
    {
        return new Permiso(
            (int) $row['id'],
            (int) $row['persona'],
            isset($row['matricula']) ? (int) $row['matricula'] : null,
            isset($row['clase'])     ? (int) $row['clase']     : null,
            isset($row['actividad']) ? (int) $row['actividad'] : null,
            $row['fecha_inicio'],
            $row['fecha_fin'],
            isset($row['horas_por_dia']) ? (float) $row['horas_por_dia'] : null,
            $row['motivo'],
            $row['observacion'] ?? null,
            (int) $row['estado'],
            $row['fecha_registro'] ?? null
        );
    }
}
