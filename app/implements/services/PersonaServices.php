<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/PersonaServiceInterface.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/TelefonoRepository.php';
require_once __DIR__ . '/../persistence/repository/CorreoRepository.php';
require_once __DIR__ . '/../persistence/repository/FichaMedicaRepository.php';
require_once __DIR__ . '/../persistence/repository/TallaRepository.php';
require_once __DIR__ . '/../persistence/repository/UserRepository.php';
require_once __DIR__ . '/../persistence/entitys/Persona.php';
require_once __DIR__ . '/../persistence/entitys/PersonaInstitucion.php';
require_once __DIR__ . '/../persistence/entitys/FichaMedica.php';
require_once __DIR__ . '/../persistence/entitys/User.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Repositories\TelefonoRepository;
use Repositories\CorreoRepository;
use Repositories\FichaMedicaRepository;
use Repositories\TallaRepository;
use Repositories\UserRepository;
use Entitys\Persona;
use Entitys\PersonaInstitucion;
use Entitys\FichaMedica;
use Entitys\User;
use Utils\Token;
use Interfaces\PersonaServiceInterface;

class PersonaServices implements PersonaServiceInterface
{
    private PersonaRepository $personaRepo;
    private PersonaInstitucionRepository $piRepo;
    private TelefonoRepository $telefonoRepo;
    private CorreoRepository $correoRepo;
    private FichaMedicaRepository $fichaRepo;
    private TallaRepository $tallaRepo;
    private UserRepository $userRepo;
    private array $security;

    public function __construct()
    {
        $this->personaRepo   = new PersonaRepository();
        $this->piRepo        = new PersonaInstitucionRepository();
        $this->telefonoRepo  = new TelefonoRepository();
        $this->correoRepo    = new CorreoRepository();
        $this->fichaRepo     = new FichaMedicaRepository();
        $this->tallaRepo     = new TallaRepository();
        $this->userRepo      = new UserRepository();
        $this->security      = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        // Detect resource from URL path or ?api= param
        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'search':
                return $this->fetchSearch($queryParams);

            case $method === 'GET' && $resource === 'profile':
                return $this->fetchProfile($queryParams);

            case $method === 'GET' && $resource === 'sections':
                return $this->fetchSections($queryParams);

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                return $this->savePersona($method, $queryParams);

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchSearch
    // -------------------------------------------------------------------------
    private function fetchSearch(array $queryParams): array
    {
        $q = trim($queryParams['q'] ?? '');
        if ($q === '') {
            throw new \RuntimeException('El parámetro q es requerido.', 400);
        }

        $like = '%' . $q . '%';
        $db   = Database::getInstance();

        $sql = "SELECT p.id,
                       p.identificacion,
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
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':q1', $like);
        $stmt->bindValue(':q2', $like);
        $stmt->bindValue(':q3', $like);
        $stmt->execute();

        $rows   = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'             => (int)$row['id'],
                'identificacion' => $row['identificacion'],
                'nombre_completo'=> trim($row['nombre_completo']),
                'tipo'           => $row['tipo']        ?? null,
                'institucion'    => $row['institucion'] ?? null,
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // fetchProfile
    // -------------------------------------------------------------------------
    private function fetchProfile(array $queryParams): array
    {
        $personaId      = isset($queryParams['id']) ? (int)$queryParams['id'] : 0;
        $identificacion = $queryParams['identificacion'] ?? '';

        if ($personaId > 0) {
            $persona = $this->personaRepo->findById($personaId);
        } elseif ($identificacion !== '') {
            $persona = $this->personaRepo->findByIdentificacion($identificacion);
        } else {
            throw new \RuntimeException('Se requiere id o identificacion.', 400);
        }

        if (!$persona) {
            throw new \RuntimeException('Persona no encontrada.', 404);
        }

        $pid = (int)$persona->getId();

        $db  = Database::getInstance();

        // Persona-Institución con nombres
        $sqlPi = "SELECT pi.id, pi.estado,
                         tp.nombre AS tipo,
                         i.nombre  AS institucion,
                         i.id      AS institucion_id
                  FROM persona_institucion pi
                  LEFT JOIN tipo_persona tp ON tp.id = pi.tipo
                  LEFT JOIN instituciones i  ON i.id  = pi.id_institucion
                  WHERE pi.id_persona = :pid
                  LIMIT 1";
        $stmtPi = $db->prepare($sqlPi);
        $stmtPi->bindValue(':pid', $pid, \PDO::PARAM_INT);
        $stmtPi->execute();
        $piRow = $stmtPi->fetch(\PDO::FETCH_ASSOC);

        $telefonos = $this->telefonoRepo->findByPersonaId($pid);
        $correos   = $this->correoRepo->findByPersonaId($pid);

        return [
            'id'             => $pid,
            'identificacion' => $persona->getIdentificacion(),
            'nombre_completo'=> trim(
                ($persona->getPrimerNombre()   ?? '') . ' ' .
                ($persona->getSegundoNombre()  ?? '') . ' ' .
                ($persona->getPrimerApellido() ?? '') . ' ' .
                ($persona->getSegundoApellido()?? '')
            ),
            'primer_nombre'   => $persona->getPrimerNombre(),
            'segundo_nombre'  => $persona->getSegundoNombre(),
            'primer_apellido' => $persona->getPrimerApellido(),
            'segundo_apellido'=> $persona->getSegundoApellido(),
            'nacionalidad'    => $persona->getNacionalidad(),
            'genero'          => $persona->getGenero(),
            'etnia'           => $persona->getEtnia(),
            'fecha_nacimiento'=> $persona->getFechaNacimiento(),
            'ciudad'          => $persona->getCiudad(),
            'direccion'       => $persona->getDireccion(),
            'tipo'            => $piRow['tipo']           ?? null,
            'institucion'     => $piRow['institucion']    ?? null,
            'institucion_id'  => isset($piRow['institucion_id']) ? (int)$piRow['institucion_id'] : null,
            'telefonos'       => $telefonos,
            'correos'         => $correos,
        ];
    }

    // -------------------------------------------------------------------------
    // fetchSections
    // -------------------------------------------------------------------------
    private function fetchSections(array $queryParams): array
    {
        $personaId = isset($queryParams['id']) ? (int)$queryParams['id'] : 0;
        if ($personaId <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $persona = $this->personaRepo->findById($personaId);
        if (!$persona) {
            throw new \RuntimeException('Persona no encontrada.', 404);
        }

        $db = Database::getInstance();

        // Roles / instituciones
        $sqlRoles = "SELECT pi.id, pi.estado,
                            tp.nombre AS tipo,
                            i.nombre  AS institucion,
                            i.id      AS institucion_id
                     FROM persona_institucion pi
                     LEFT JOIN tipo_persona tp ON tp.id = pi.tipo
                     LEFT JOIN instituciones i  ON i.id  = pi.id_institucion
                     WHERE pi.id_persona = :pid";
        $stmtRoles = $db->prepare($sqlRoles);
        $stmtRoles->bindValue(':pid', $personaId, \PDO::PARAM_INT);
        $stmtRoles->execute();
        $roles = $stmtRoles->fetchAll(\PDO::FETCH_ASSOC);

        // Matrículas
        $sqlMat = "SELECT m.id,
                          m.fecha_inicio,
                          m.fecha_fin,
                          i.nombre AS institucion
                   FROM matricula m
                   JOIN instituciones i ON i.id = m.institucion
                   WHERE m.persona = :pid
                   ORDER BY m.fecha_inicio DESC";
        $stmtMat = $db->prepare($sqlMat);
        $stmtMat->bindValue(':pid', $personaId, \PDO::PARAM_INT);
        $stmtMat->execute();
        $matriculas = $stmtMat->fetchAll(\PDO::FETCH_ASSOC);

        // Repositorios
        $telefonos = $this->telefonoRepo->findByPersonaId($personaId);
        $correos   = $this->correoRepo->findByPersonaId($personaId);
        $ficha     = $this->fichaRepo->findByPersonaId($personaId);
        $tallas    = $this->tallaRepo->findByPersonaId($personaId);

        return [
            'datos_personales' => [
                'id'              => (int)$persona->getId(),
                'identificacion'  => $persona->getIdentificacion(),
                'primer_nombre'   => $persona->getPrimerNombre(),
                'segundo_nombre'  => $persona->getSegundoNombre(),
                'primer_apellido' => $persona->getPrimerApellido(),
                'segundo_apellido'=> $persona->getSegundoApellido(),
                'nacionalidad'    => $persona->getNacionalidad(),
                'genero'          => $persona->getGenero(),
                'etnia'           => $persona->getEtnia(),
                'fecha_nacimiento'=> $persona->getFechaNacimiento(),
                'ciudad'          => $persona->getCiudad(),
                'direccion'       => $persona->getDireccion(),
            ],
            'contacto'            => [
                'telefonos' => $telefonos,
                'correos'   => $correos,
            ],
            'medico'              => $ficha,
            'fisico'              => $tallas,
            'roles_instituciones' => $roles,
            'matriculas'          => $matriculas,
        ];
    }

    // -------------------------------------------------------------------------
    // savePersona
    // -------------------------------------------------------------------------
    private function savePersona(string $method, array $queryParams): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        // Required fields
        foreach (['identificacion', 'primer_nombre', 'primer_apellido', 'institucion', 'tipo_persona'] as $field) {
            if (empty($data[$field])) {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $bodyId   = isset($data['id']) ? (int)$data['id'] : 0;
        $paramId  = isset($queryParams['id']) ? (int)$queryParams['id'] : 0;
        $personaId = $bodyId > 0 ? $bodyId : $paramId;

        $identificacion  = trim($data['identificacion']);
        $primerNombre    = trim($data['primer_nombre']);
        $segundoNombre   = trim($data['segundo_nombre']   ?? '');
        $primerApellido  = trim($data['primer_apellido']);
        $segundoApellido = trim($data['segundo_apellido'] ?? '');
        $nacionalidad    = $data['nacionalidad']    ?? null;
        $genero          = $data['genero']          ?? null;
        $etnia           = $data['etnia']           ?? null;
        $fechaNacimiento = $data['fecha_nacimiento']?? null;
        $ciudad          = $data['ciudad']          ?? null;
        $direccion       = $data['direccion']       ?? null;
        $institucionId   = (int)$data['institucion'];
        $tipoPersona     = (int)$data['tipo_persona'];

        $existente = $this->personaRepo->findByIdentificacion($identificacion);

        if ($personaId > 0 || $existente) {
            // UPDATE
            $persona = $existente ?? $this->personaRepo->findById($personaId);
            if (!$persona) {
                throw new \RuntimeException('Persona no encontrada para actualizar.', 404);
            }
            $userId = $persona->getUser();

            $updated = new Persona(
                (int)$persona->getId(),
                $identificacion,
                $primerNombre,
                $segundoNombre,
                $primerApellido,
                $segundoApellido,
                $nacionalidad,
                $genero,
                $etnia,
                $fechaNacimiento,
                $ciudad,
                $direccion,
                $userId
            );
            $saved = $this->personaRepo->save($updated);

            // Update PersonaInstitucion
            $pi = $this->piRepo->findByPersonaId((int)$saved->getId());
            $piEntity = new PersonaInstitucion(
                $pi ? (int)$pi->getId() : null,
                (int)$saved->getId(),
                $institucionId,
                $tipoPersona,
                1
            );
            $this->piRepo->save($piEntity);

        } else {
            // INSERT — create User first if credentials provided
            $userId = null;
            if (!empty($data['username']) && !empty($data['password'])) {
                $newUser = new User(null, $data['username'], $data['password'], $data['tipo_user'] ?? 'ESTUDIANTE', $data['imagen'] ?? null);
                $savedUser = $this->userRepo->save($newUser);
                $userId    = (int)$savedUser->getId();
            }

            $persona = new Persona(
                null,
                $identificacion,
                $primerNombre,
                $segundoNombre,
                $primerApellido,
                $segundoApellido,
                $nacionalidad,
                $genero,
                $etnia,
                $fechaNacimiento,
                $ciudad,
                $direccion,
                $userId
            );
            $saved = $this->personaRepo->save($persona);

            $piEntity = new PersonaInstitucion(null, (int)$saved->getId(), $institucionId, $tipoPersona, 1);
            $this->piRepo->save($piEntity);
        }

        // Save FichaMedica if medical fields are present
        $medicalFields = ['grupo_sanguineo','alergias','enfermedades_preexistentes','medicamentos_actuales',
                          'contacto_emergencia_nombre','contacto_emergencia_telefono'];
        $hasMedical = array_reduce($medicalFields, fn($carry, $f) => $carry || isset($data[$f]), false);

        if ($hasMedical) {
            $fichaExistente = $this->fichaRepo->findByPersonaId((int)$saved->getId());
            $fichaId = $fichaExistente ? (int)$fichaExistente->getId() : null;
            $ficha   = new FichaMedica(
                $fichaId,
                (int)$saved->getId(),
                $data['grupo_sanguineo']                ?? null,
                $data['alergias']                       ?? null,
                $data['enfermedades_preexistentes']     ?? null,
                $data['medicamentos_actuales']          ?? null,
                $data['contacto_emergencia_nombre']     ?? null,
                $data['contacto_emergencia_telefono']   ?? null,
                1
            );
            $this->fichaRepo->save($ficha);
        }

        return [
            'id'             => (int)$saved->getId(),
            'identificacion' => $saved->getIdentificacion(),
            'nombre_completo'=> trim(
                ($saved->getPrimerNombre()   ?? '') . ' ' .
                ($saved->getSegundoNombre()  ?? '') . ' ' .
                ($saved->getPrimerApellido() ?? '') . ' ' .
                ($saved->getSegundoApellido()?? '')
            ),
        ];
    }
}
