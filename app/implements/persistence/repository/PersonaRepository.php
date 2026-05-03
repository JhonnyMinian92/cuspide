<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';

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
        ':nacionalidad' => $persona->getNacionalidad(),
        ':genero' => $persona->getGenero(),
        ':etnia' => $persona->getEtnia(),
        ':fecha_nacimiento' => $persona->getFechaNacimiento(),
        ':ciudad' => $persona->getCiudad(),
        ':direccion' => $persona->getDireccion(),
        ':user' => $persona->getUser()
      ]);
      $persona->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE personas SET identificacion = :identificacion, primer_nombre = :primer_nombre, segundo_nombre = :segundo_nombre, primer_apellido = :primer_apellido, segundo_apellido = :segundo_apellido, nacionalidad = :nacionalidad, genero = :genero, etnia = :etnia, fecha_nacimiento = :fecha_nacimiento, ciudad = :ciudad, direccion = :direccion WHERE id = :id");
      $stmt->execute([
        ':id' => $persona->getId(),
        ':identificacion' => $persona->getIdentificacion(),
        ':primer_nombre' => $persona->getPrimerNombre(),
        ':segundo_nombre' => $persona->getSegundoNombre(),
        ':primer_apellido' => $persona->getPrimerApellido(),
        ':segundo_apellido' => $persona->getSegundoApellido(),
        ':nacionalidad' => $persona->getNacionalidad(),
        ':genero' => $persona->getGenero(),
        ':etnia' => $persona->getEtnia(),
        ':fecha_nacimiento' => $persona->getFechaNacimiento(),
        ':ciudad' => $persona->getCiudad(),
        ':direccion' => $persona->getDireccion()
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
}
