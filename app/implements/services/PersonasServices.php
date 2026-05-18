<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/UserRepository.php';
require_once __DIR__ . '/../persistence/repository/TelefonoRepository.php';
require_once __DIR__ . '/../persistence/repository/CorreoRepository.php';
require_once __DIR__ . '/../../controllers/request/PersonaRequest.php';
require_once __DIR__ . '/../utils/Token.php';
require_once __DIR__ . '/../interfaces/PersonaServiceInterface.php';

use Core\Database;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Repositories\UserRepository;
use Repositories\TelefonoRepository;
use Repositories\CorreoRepository;
use Request\PersonaRequest;
use RuntimeException;
use Utils\Token;
use PDO;
use Interfaces\PersonaServiceInterface;

final class PersonasServices implements PersonaServiceInterface
{
  private string $jwtSecret;
  private PDO $db;
  private PersonaRepository $personaRepo;
  private PersonaInstitucionRepository $piRepo;
  private UserRepository $userRepo;
  private TelefonoRepository $telRepo;
  private CorreoRepository $correoRepo;

  public function __construct()
  {
    $config = require __DIR__ . '/../../properties/security.php';
    $this->jwtSecret = (string) ($config['jwt_secret'] ?? '');
    if ($this->jwtSecret === '') {
      throw new RuntimeException('No existe configuración de seguridad para JWT.', 500);
    }
    $this->db = Database::getInstance();
    $this->personaRepo = new PersonaRepository();
    $this->piRepo = new PersonaInstitucionRepository();
    $this->userRepo = new UserRepository();
    $this->telRepo = new TelefonoRepository();
    $this->correoRepo = new CorreoRepository();
  }

  public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
  {
    $payload = Token::validateBearerToken($authHeader, $this->jwtSecret);
    $userId  = (int) ($payload['sub'] ?? 0);
    $instId  = $this->getInstitucionByUserId($userId);

    $resource = strtolower(trim((string) ($queryParams['api'] ?? ''))); // requestUri is not used here, but kept for interface compatibility
    $id = isset($queryParams['id']) ? (int)$queryParams['id'] : null;

    return match (true) {
      $method === 'GET' && $resource === 'employees' => $this->fetchEmployees($instId, $queryParams),
      $method === 'GET' && $resource === 'profile' && $id !== null => $this->fetchProfile($id, $instId),
      $method === 'GET' && $resource === 'catalogs' => $this->fetchCatalogs(),
      $method === 'POST' && $resource === 'saveemployee' => $this->saveEmployee($instId, null),
      $method === 'PUT' && $resource === 'saveemployee' && $id !== null => $this->saveEmployee($instId, $id),
      default => throw new RuntimeException('Ruta de API no soportada: ' . $method . ' ' . $resource, 404),
    };
  }

  private function fetchEmployees(int $instId, array $queryParams): array
  {
    $query = isset($queryParams['q']) ? trim($queryParams['q']) : '';
    $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 20;

    // ID del tipo_persona 'Estudiante' (asumimos que es 1, como en alumnos.html.twig)
    $studentTypeId = 1;

    $sql = "SELECT p.id, p.identificacion,
                       TRIM(CONCAT(p.primer_nombre, ' ', COALESCE(p.segundo_nombre, ''), ' ', p.primer_apellido, ' ', COALESCE(p.segundo_apellido, ''))) as nombre_completo,
                       tp.nombre as tipo,
                       pi.estado as pi_estado
                FROM personas p
                JOIN persona_institucion pi ON p.id = pi.id_persona
                JOIN tipo_persona tp ON pi.tipo = tp.id
                WHERE pi.id_institucion = :instId AND pi.tipo != :studentTypeId";

    $params = [':instId' => $instId, ':studentTypeId' => $studentTypeId];

    if ($query !== '') {
      $sql .= " AND (p.identificacion LIKE :q OR p.primer_nombre LIKE :q OR p.primer_apellido LIKE :q)";
      $params[':q'] = "%{$query}%";
    }

    $sql .= " ORDER BY p.primer_apellido, p.primer_nombre LIMIT :limit";
    $params[':limit'] = $limit;

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Total count
    $countStmt = $this->db->prepare("SELECT COUNT(*) FROM persona_institucion WHERE id_institucion = :instId AND tipo != :studentTypeId");
    $countStmt->execute([':instId' => $instId, ':studentTypeId' => $studentTypeId]);
    $total = (int) $countStmt->fetchColumn();

    return ['total' => $total, 'items' => $items];
  }

  private function fetchProfile(int $personaId, int $instId): array
  {
    // Validar que la persona pertenezca a la institución del usuario
    $pi = $this->piRepo->findByPersonaAndInstitucion($personaId, $instId);
    if ($pi === null) {
      throw new RuntimeException('Persona no encontrada o no pertenece a su institución.', 404);
    }

    $persona = $this->personaRepo->findCompleteById($personaId);
    if ($persona === null) {
      throw new RuntimeException('No se pudo cargar el perfil completo de la persona.', 404);
    }
    return ['persona' => $persona];
  }

  private function fetchCatalogs(): array
  {
    $catalogs = [
      'generos' => 'genero',
      'etnias' => 'etnia',
      'nacionalidades' => 'nacionalidad',
      'tipos_persona' => 'tipo_persona'
    ];
    $result = [];
    foreach ($catalogs as $key => $table) {
      $stmt = $this->db->query("SELECT id, nombre FROM {$table} ORDER BY nombre ASC");
      $result[$key] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $stmt = $this->db->query("SELECT c.id, c.nombre, p.nombre as provincia FROM ciudad c JOIN provincia p ON c.provincia = p.id ORDER BY p.nombre, c.nombre");
    $result['ciudades'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result;
  }

  private function saveEmployee(int $instId, ?int $personaId): array
  {
    $req = PersonaRequest::fromJson();

    if ($req->institucion !== $instId) {
      throw new RuntimeException('La institución de la solicitud no coincide con la del usuario.', 403);
    }

    // No permitir crear/modificar a tipo 'Estudiante' desde este endpoint
    if ($req->tipo_persona_id === 1) { // Asumiendo 1 = Estudiante
      throw new RuntimeException('No se puede gestionar estudiantes desde este módulo.', 400);
    }

    $this->db->beginTransaction();
    try {
      // 1. Guardar Persona
      $personaData = [
        'id' => $personaId,
        'identificacion' => $req->identificacion,
        'primer_nombre' => $req->primer_nombre,
        'segundo_nombre' => $req->segundo_nombre,
        'primer_apellido' => $req->primer_apellido,
        'segundo_apellido' => $req->segundo_apellido,
        'fecha_nacimiento' => $req->fecha_nacimiento,
        'genero' => $req->genero,
        'etnia' => $req->etnia,
        'nacionalidad' => $req->nacionalidad,
        'ciudad' => $req->ciudad,
        'direccion' => $req->direccion,
        'grupo_sanguineo' => $req->grupo_sanguineo,
        'contacto_emergencia_nombre' => $req->contacto_emergencia_nombre,
        'contacto_emergencia_telefono' => $req->contacto_emergencia_telefono,
        'alergias' => $req->alergias,
        'enfermedades_preexistentes' => $req->enfermedades_preexistentes,
        'medicamentos_actuales' => $req->medicamentos_actuales,
      ];
      $savedPersonaId = $this->personaRepo->saveFromArray($personaData);

      // 2. Guardar PersonaInstitucion
      $this->piRepo->saveOrUpdate($savedPersonaId, $req->institucion, $req->tipo_persona_id, $req->pi_estado);

      // 3. Guardar Usuario (si aplica)
      if ($req->username) {
        $this->userRepo->saveFromRequest($savedPersonaId, $req->username, $req->password, $req->tipo_user);
      }

      // 4. Guardar Telefonos y Correos
      $this->telRepo->syncForPersona($savedPersonaId, $req->telefonos);
      $this->correoRepo->syncForPersona($savedPersonaId, $req->correos);

      $this->db->commit();

      return [
        'id' => $savedPersonaId,
        'identificacion' => $req->identificacion,
        'nombre_completo' => trim("{$req->primer_nombre} {$req->primer_apellido}")
      ];
    } catch (\Exception $e) {
      $this->db->rollBack();
      if ($e instanceof RuntimeException) {
        throw $e;
      }
      // Capturar errores de duplicado de cédula/usuario
      if (str_contains($e->getMessage(), 'Duplicate entry')) {
        if (str_contains($e->getMessage(), 'identificacion')) {
          throw new RuntimeException("La identificación '{$req->identificacion}' ya está registrada.", 409);
        }
        if (str_contains($e->getMessage(), 'username')) {
          throw new RuntimeException("El nombre de usuario '{$req->username}' ya está en uso.", 409);
        }
      }
      throw new RuntimeException('Error al guardar la persona: ' . $e->getMessage(), 500);
    }
  }

  private function getInstitucionByUserId(int $userId): int
  {
    $personaRepo = new PersonaRepository();
    $piRepo      = new PersonaInstitucionRepository();

    $persona = $personaRepo->findByUserId($userId);
    if ($persona === null) {
      throw new RuntimeException('No se encontró persona asociada al usuario.', 404);
    }

    $pi = $piRepo->findByPersonaId((int) $persona->getId());
    if ($pi === null) {
      throw new RuntimeException('No se encontró institución asociada al usuario.', 404);
    }

    $institucion = $pi->getIdInstitucion();
    if ($institucion === null) {
      throw new RuntimeException('La persona no tiene una institución válida asociada.', 404);
    }

    return (int) $institucion->getId();
  }
}
