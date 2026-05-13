<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Persona.php';

use Core\Database;
use Entitys\Persona;
use PDO;

class PersonaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Persona $persona): Persona
  {
    if ($persona->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO personas (identificacion, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, nacionalidad, genero, etnia, fecha_nacimiento, ciudad, direccion, user) VALUES (:identificacion, :primer_nombre, :segundo_nombre, :primer_apellido, :segundo_apellido, :nacionalidad, :genero, :etnia, :fecha_nacimiento, :ciudad, :direccion, :user)");
      $stmt->execute([
        ':identificacion' => $persona->getIdentificacion(),
        ':primer_nombre' => $persona->getPrimerNombre(),
        ':segundo_nombre' => $persona->getSegundoNombre(),
        ':primer_apellido' => $persona->getPrimerApellido(),
        ':segundo_apellido' => $persona->getSegundoApellido(),
        ':nacionalidad' => $persona->getNacionalidad()?->getId(),
        ':genero' => $persona->getGenero()?->getId(),
        ':etnia' => $persona->getEtnia()?->getId(),
        ':fecha_nacimiento' => $persona->getFechaNacimiento(),
        ':ciudad' => $persona->getCiudad()?->getId(),
        ':direccion' => $persona->getDireccion(),
        ':user' => $persona->getUser()?->getId()
      ]);
      $persona->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE personas SET identificacion = :identificacion, primer_nombre = :primer_nombre, segundo_nombre = :segundo_nombre, primer_apellido = :primer_apellido, segundo_apellido = :segundo_apellido, nacionalidad = :nacionalidad, genero = :genero, etnia = :etnia, fecha_nacimiento = :fecha_nacimiento, ciudad = :ciudad, direccion = :direccion, user = :user WHERE id = :id");
      $stmt->execute([
        ':id' => $persona->getId(),
        ':identificacion' => $persona->getIdentificacion(),
        ':primer_nombre' => $persona->getPrimerNombre(),
        ':segundo_nombre' => $persona->getSegundoNombre(),
        ':primer_apellido' => $persona->getPrimerApellido(),
        ':segundo_apellido' => $persona->getSegundoApellido(),
        ':nacionalidad' => $persona->getNacionalidad()?->getId(),
        ':genero' => $persona->getGenero()?->getId(),
        ':etnia' => $persona->getEtnia()?->getId(),
        ':fecha_nacimiento' => $persona->getFechaNacimiento(),
        ':ciudad' => $persona->getCiudad()?->getId(),
        ':direccion' => $persona->getDireccion(),
        ':user' => $persona->getUser()?->getId()
      ]);
    }
    return $persona;
  }

  public function findById(int $id): ?Persona
  {
    $stmt = $this->db->prepare("SELECT * FROM personas WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new Persona($row['id'], $row['identificacion'], $row['primer_nombre'], $row['segundo_nombre'], $row['primer_apellido'], $row['segundo_apellido'], $row['nacionalidad'], $row['genero'], $row['etnia'], $row['fecha_nacimiento'], $row['ciudad'], $row['direccion'], $row['user']);
    }

    return null;
  }

  public function findByUserId(int $userId): ?Persona
  {
    $stmt = $this->db->prepare("SELECT * FROM personas WHERE user = :user LIMIT 1");
    $stmt->execute([':user' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new Persona($row['id'], $row['identificacion'], $row['primer_nombre'], $row['segundo_nombre'], $row['primer_apellido'], $row['segundo_apellido'], $row['nacionalidad'], $row['genero'], $row['etnia'], $row['fecha_nacimiento'], $row['ciudad'], $row['direccion'], $row['user']);
    }

    return null;
  }

  public function findByIdentificacion(string $identificacion): ?Persona
  {
    $stmt = $this->db->prepare("SELECT * FROM personas WHERE identificacion = :identificacion");
    $stmt->execute([':identificacion' => $identificacion]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new Persona($row['id'], $row['identificacion'], $row['primer_nombre'], $row['segundo_nombre'], $row['primer_apellido'], $row['segundo_apellido'], $row['nacionalidad'], $row['genero'], $row['etnia'], $row['fecha_nacimiento'], $row['ciudad'], $row['direccion'], $row['user']);
    }

    return null;
  }

  public function findByQuery(string $q, int $limit = 50): array
  {
    $like = '%' . $q . '%';
    $stmt = $this->db->prepare(
      "SELECT p.id, p.identificacion,
              CONCAT(COALESCE(p.primer_nombre,''),' ',COALESCE(p.segundo_nombre,''),' ',
                     COALESCE(p.primer_apellido,''),' ',COALESCE(p.segundo_apellido,'')) AS nombre_completo,
              tp.nombre AS tipo,
              i.nombre  AS institucion
       FROM personas p
       LEFT JOIN persona_institucion pi ON pi.id_persona = p.id AND pi.estado = 1
       LEFT JOIN tipo_persona tp        ON tp.id = pi.tipo
       LEFT JOIN instituciones i        ON i.id  = pi.id_institucion
       WHERE p.identificacion  LIKE :q1
          OR p.primer_nombre   LIKE :q2
          OR p.primer_apellido LIKE :q3
       LIMIT :lim"
    );
    $stmt->bindValue(':q1', $like);
    $stmt->bindValue(':q2', $like);
    $stmt->bindValue(':q3', $like);
    $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
  }
}
