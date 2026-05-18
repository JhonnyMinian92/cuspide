<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/PersonaServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/MatriculaRepository.php';
require_once __DIR__ . '/../persistence/repository/TelefonoRepository.php';
require_once __DIR__ . '/../persistence/repository/CorreoRepository.php';
require_once __DIR__ . '/../persistence/repository/FichaMedicaRepository.php';
require_once __DIR__ . '/../persistence/repository/TallaRepository.php';
require_once __DIR__ . '/../persistence/repository/UserRepository.php';
require_once __DIR__ . '/../persistence/repository/GeneroRepository.php';
require_once __DIR__ . '/../persistence/repository/EtniaRepository.php';
require_once __DIR__ . '/../persistence/repository/NacionalidadRepository.php';
require_once __DIR__ . '/../persistence/entitys/Persona.php';
require_once __DIR__ . '/../persistence/entitys/PersonaInstitucion.php';
require_once __DIR__ . '/../persistence/entitys/FichaMedica.php';
require_once __DIR__ . '/../persistence/entitys/User.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Repositories\MatriculaRepository;
use Repositories\TelefonoRepository;
use Repositories\CorreoRepository;
use Repositories\FichaMedicaRepository;
use Repositories\TallaRepository;
use Repositories\UserRepository;
use Repositories\GeneroRepository;
use Repositories\EtniaRepository;
use Repositories\NacionalidadRepository;
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
    private MatriculaRepository $matriculaRepo;
    private TelefonoRepository $telefonoRepo;
    private CorreoRepository $correoRepo;
    private FichaMedicaRepository $fichaRepo;
    private TallaRepository $tallaRepo;
    private UserRepository $userRepo;
    private GeneroRepository $generoRepo;
    private EtniaRepository $etniaRepo;
    private NacionalidadRepository $nacRepo;
    private array $security;

    public function __construct()
    {
        $this->personaRepo   = new PersonaRepository();
        $this->piRepo        = new PersonaInstitucionRepository();
        $this->matriculaRepo = new MatriculaRepository();
        $this->telefonoRepo  = new TelefonoRepository();
        $this->correoRepo    = new CorreoRepository();
        $this->fichaRepo     = new FichaMedicaRepository();
        $this->tallaRepo     = new TallaRepository();
        $this->userRepo      = new UserRepository();
        $this->generoRepo    = new GeneroRepository();
        $this->etniaRepo     = new EtniaRepository();
        $this->nacRepo       = new NacionalidadRepository();
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

        $userId = (int) ($payload['sub'] ?? 0);

        switch (true) {
            case $method === 'GET' && $resource === 'search':
                return $this->fetchSearch($queryParams);

            case $method === 'GET' && $resource === 'catalogs':
                return $this->fetchCatalogs();

            case $method === 'GET' && $resource === 'profile':
                return $this->fetchProfile($queryParams);

            case $method === 'GET' && $resource === 'sections':
                return $this->fetchSections($queryParams);

            case $method === 'GET' && $resource === 'students':
                return $this->fetchStudents($userId, $queryParams);

            case $method === 'GET' && $resource === 'employees':
                return $this->fetchEmployees($userId, $queryParams);

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                return $this->savePersona($method, $queryParams);

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'savestudent':
                return $this->savePersonaByToken($userId, $queryParams, 'TIPO_EST');

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'saveemployee':
                return $this->savePersonaByToken($userId, $queryParams, null);

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchCatalogs
    // -------------------------------------------------------------------------
    private function fetchCatalogs(): array
    {
        $generos        = $this->generoRepo->findAll();
        $etnias         = $this->etniaRepo->findAll();
        $nacionalidades = $this->nacRepo->findAll();

        $db = \Core\Database::getInstance();

        $stmtCiudades = $db->query(
            "SELECT id, nombre, provincia FROM ciudades ORDER BY provincia ASC, nombre ASC"
        );
        $ciudades = $stmtCiudades->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $stmtTipos = $db->query(
            "SELECT id, nombre, nemonico FROM tipo_persona ORDER BY nombre ASC"
        );
        $tiposPersona = $stmtTipos->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return [
            'generos'       => array_map(fn($g) => ['id' => $g->getId(), 'nombre' => $g->getNombre()], $generos),
            'etnias'        => array_map(fn($e) => ['id' => $e->getId(), 'nombre' => $e->getNombre()], $etnias),
            'nacionalidades'=> array_map(fn($n) => ['id' => $n->getId(), 'nombre' => $n->getNombre()], $nacionalidades),
            'ciudades'      => array_map(fn($c) => [
                'id'       => (int) $c['id'],
                'nombre'   => $c['nombre'],
                'provincia'=> $c['provincia'],
            ], $ciudades),
            'tipos_persona' => array_map(fn($t) => [
                'id'      => (int) $t['id'],
                'nombre'  => $t['nombre'],
                'nemonico'=> $t['nemonico'],
            ], $tiposPersona),
        ];
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

        $rows   = $this->personaRepo->findByQuery($q);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'              => (int)$row['id'],
                'identificacion'  => $row['identificacion'],
                'nombre_completo' => trim($row['nombre_completo']),
                'tipo'            => $row['tipo']        ?? null,
                'institucion'     => $row['institucion'] ?? null,
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

        $piRow = $this->piRepo->findByPersonaIdWithDetails($pid);

        $telefonos = $this->telefonoRepo->findByPersonaId($pid);
        $correos   = $this->correoRepo->findByPersonaId($pid);

        $fichas = $this->fichaRepo->findByPersonaId($pid);
        $ficha  = $fichas[0] ?? null;

        $genero       = $persona->getGenero();
        $etnia        = $persona->getEtnia();
        $nacionalidad = $persona->getNacionalidad();
        $ciudad       = $persona->getCiudad();

        return [
            // ── Identidad ──────────────────────────────────────────────────
            'id'               => $pid,
            'identificacion'   => $persona->getIdentificacion(),
            'nombre_completo'  => trim(
                ($persona->getPrimerNombre()   ?? '') . ' ' .
                ($persona->getSegundoNombre()  ?? '') . ' ' .
                ($persona->getPrimerApellido() ?? '') . ' ' .
                ($persona->getSegundoApellido()?? '')
            ),
            'primer_nombre'    => $persona->getPrimerNombre(),
            'segundo_nombre'   => $persona->getSegundoNombre(),
            'primer_apellido'  => $persona->getPrimerApellido(),
            'segundo_apellido' => $persona->getSegundoApellido(),
            'fecha_nacimiento' => $persona->getFechaNacimiento(),
            'direccion'        => $persona->getDireccion(),
            // ── Clasificación ───────────────────────────────────────────────
            'genero_id'        => $genero       ? (int) $genero->getId()       : null,
            'genero'           => $genero       ? $genero->getNombre()         : null,
            'etnia_id'         => $etnia        ? (int) $etnia->getId()        : null,
            'etnia'            => $etnia        ? $etnia->getNombre()          : null,
            'nacionalidad_id'  => $nacionalidad ? (int) $nacionalidad->getId() : null,
            'nacionalidad'     => $nacionalidad ? $nacionalidad->getNombre()   : null,
            'ciudad_id'        => $ciudad       ? (int) $ciudad->getId()       : null,
            'ciudad_nombre'    => $ciudad       ? $ciudad->getNombre()         : null,
            // ── Institución ─────────────────────────────────────────────────
            'tipo'             => $piRow['tipo']          ?? null,
            'tipo_persona_id'  => isset($piRow['tipo_id']) ? (int) $piRow['tipo_id'] : null,
            'institucion'      => $piRow['institucion']   ?? null,
            'institucion_id'   => isset($piRow['institucion_id']) ? (int) $piRow['institucion_id'] : null,
            'pi_estado'        => isset($piRow['pi_estado']) ? (int) $piRow['pi_estado'] : 1,
            // ── Contacto ────────────────────────────────────────────────────
            'telefonos' => array_map(fn($t) => ['id' => $t->getId(), 'numero' => $t->getNumero()], $telefonos),
            'correos'   => array_map(fn($c) => ['id' => $c->getId(), 'email'  => $c->getEmail()],  $correos),
            // ── Ficha Médica ─────────────────────────────────────────────────
            'ficha_id'                       => $ficha ? $ficha->getId()                          : null,
            'grupo_sanguineo'                => $ficha ? $ficha->getGrupoSanguineo()              : null,
            'alergias'                       => $ficha ? $ficha->getAlergias()                    : null,
            'enfermedades_preexistentes'     => $ficha ? $ficha->getEnfermedadesPreexistentes()   : null,
            'medicamentos_actuales'          => $ficha ? $ficha->getMedicamentosActuales()        : null,
            'contacto_emergencia_nombre'     => $ficha ? $ficha->getContactoEmergenciaNombre()    : null,
            'contacto_emergencia_telefono'   => $ficha ? $ficha->getContactoEmergenciaTelefono()  : null,
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

        // Roles / instituciones
        $roles = $this->piRepo->findAllByPersonaIdWithDetails($personaId);

        // Matrículas
        $matriculas = $this->matriculaRepo->findByPersonaId($personaId);

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
    // -------------------------------------------------------------------------
    // fetchStudents
    // -------------------------------------------------------------------------
    private function fetchStudents(int $userId, array $queryParams): array
    {
        $instId = $this->getInstitucionByUserId($userId);
        $q      = trim($queryParams['q'] ?? '');

        if ($q !== '') {
            $rows = $this->personaRepo->findByQueryAndInstitucion($q, $instId, 'TIPO_EST');
        } else {
            $rows = $this->personaRepo->findStudentsByInstitucion($instId, 20);
        }

        $total = $this->personaRepo->countStudentsByInstitucion($instId);

        $items = array_map(fn($r) => [
            'id'              => (int) $r['id'],
            'identificacion'  => $r['identificacion'],
            'nombre_completo' => trim($r['nombre_completo']),
            'primer_nombre'   => $r['primer_nombre'] ?? null,
            'primer_apellido' => $r['primer_apellido'] ?? null,
            'tipo'            => $r['tipo'] ?? null,
            'tipo_nemonico'   => $r['tipo_nemonico'] ?? null,
            'pi_estado'       => isset($r['pi_estado']) ? (int) $r['pi_estado'] : null,
            'fecha_nacimiento'=> $r['fecha_nacimiento'] ?? null,
        ], $rows);

        return ['total' => $total, 'items' => $items];
    }

    // -------------------------------------------------------------------------
    // fetchEmployees
    // -------------------------------------------------------------------------
    private function fetchEmployees(int $userId, array $queryParams): array
    {
        $instId = $this->getInstitucionByUserId($userId);
        $q      = trim($queryParams['q'] ?? '');

        if ($q !== '') {
            $rows = $this->personaRepo->findByQueryAndInstitucion($q, $instId, 'NOT_EST');
        } else {
            $rows = $this->personaRepo->findEmployeesByInstitucion($instId, 20);
        }

        $total = $this->personaRepo->countEmployeesByInstitucion($instId);

        $items = array_map(fn($r) => [
            'id'              => (int) $r['id'],
            'identificacion'  => $r['identificacion'],
            'nombre_completo' => trim($r['nombre_completo']),
            'primer_nombre'   => $r['primer_nombre'] ?? null,
            'primer_apellido' => $r['primer_apellido'] ?? null,
            'tipo'            => $r['tipo'] ?? null,
            'tipo_nemonico'   => $r['tipo_nemonico'] ?? null,
            'pi_estado'       => isset($r['pi_estado']) ? (int) $r['pi_estado'] : null,
            'fecha_nacimiento'=> $r['fecha_nacimiento'] ?? null,
        ], $rows);

        return ['total' => $total, 'items' => $items];
    }

    // -------------------------------------------------------------------------
    // getInstitucionByUserId
    // -------------------------------------------------------------------------
    private function getInstitucionByUserId(int $userId): int
    {
        $persona = $this->personaRepo->findByUserId($userId);
        if ($persona === null) {
            throw new \RuntimeException('No se encontró persona asociada al usuario.', 404);
        }
        $pi = $this->piRepo->findByPersonaId((int) $persona->getId());
        if ($pi === null) {
            throw new \RuntimeException('No se encontró institución asociada al usuario.', 404);
        }
        return (int) $pi->getIdInstitucion()->getId();
    }

    // -------------------------------------------------------------------------
    // savePersonaByToken — resolves institution and tipo_persona from token
    // -------------------------------------------------------------------------
    private function savePersonaByToken(int $userId, array $queryParams, ?string $tipoNemonico): array
    {
        $instId = $this->getInstitucionByUserId($userId);
        $data   = json_decode((string) file_get_contents('php://input'), true) ?: [];

        $db = \Core\Database::getInstance();

        // Prioridad: 1. tipo_persona_id explícito del form, 2. nemonico por defecto, 3. fallback
        if (!empty($data['tipo_persona_id'])) {
            $tipoId = (int) $data['tipo_persona_id'];
        } elseif ($tipoNemonico !== null) {
            $stmt = $db->prepare("SELECT id FROM tipo_persona WHERE nemonico = :n LIMIT 1");
            $stmt->execute([':n' => $tipoNemonico]);
            $tipoId = (int) ($stmt->fetchColumn() ?: 3);
        } else {
            $nemo = $data['tipo_nemonico'] ?? '';
            if ($nemo !== '') {
                $stmt = $db->prepare("SELECT id FROM tipo_persona WHERE nemonico = :n LIMIT 1");
                $stmt->execute([':n' => $nemo]);
                $tipoId = (int) ($stmt->fetchColumn() ?: 1);
            } else {
                $tipoId = isset($data['tipo_persona']) ? (int) $data['tipo_persona'] : 1;
            }
        }

        $data['institucion']  = $instId;
        $data['tipo_persona'] = $tipoId;

        return $this->savePersonaData($queryParams, $data);
    }

    private function savePersona(string $method, array $queryParams): array
    {
        unset($method);
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }
        return $this->savePersonaData($queryParams, $data);
    }

    private function savePersonaData(array $queryParams, array $data): array
    {
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
        $piEstado        = isset($data['pi_estado']) ? (int)$data['pi_estado'] : 1;

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
                $piEstado
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

            $piEntity = new PersonaInstitucion(null, (int)$saved->getId(), $institucionId, $tipoPersona, $piEstado);
            $this->piRepo->save($piEntity);
        }

        // Save FichaMedica if medical fields are present
        $medicalFields = ['grupo_sanguineo','alergias','enfermedades_preexistentes','medicamentos_actuales',
                          'contacto_emergencia_nombre','contacto_emergencia_telefono'];
        $hasMedical = array_reduce($medicalFields, fn($carry, $f) => $carry || isset($data[$f]), false);

        if ($hasMedical) {
            $fichasExistentes = $this->fichaRepo->findByPersonaId((int)$saved->getId());
            $fichaExistente   = $fichasExistentes[0] ?? null;
            $fichaId = $fichaExistente !== null ? (int)$fichaExistente->getId() : null;
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

        // Save telefonos via raw SQL (avoids operadora/tipo FK default lookups)
        $db = \Core\Database::getInstance();
        if (isset($data['telefonos']) && is_array($data['telefonos'])) {
            $db->prepare("UPDATE telefonos SET estado = 2 WHERE persona = ?")->execute([(int)$saved->getId()]);
            $stmtTel = $db->prepare("INSERT INTO telefonos (numero, operadora, persona, tipo, estado) VALUES (?, 1, ?, 1, 1)");
            foreach ($data['telefonos'] as $t) {
                $num = trim($t['numero'] ?? '');
                if ($num !== '') {
                    $stmtTel->execute([$num, (int)$saved->getId()]);
                }
            }
        }

        // Save correos via raw SQL
        if (isset($data['correos']) && is_array($data['correos'])) {
            $db->prepare("UPDATE correos SET estado = 2 WHERE persona = ?")->execute([(int)$saved->getId()]);
            $stmtCor = $db->prepare("INSERT INTO correos (email, tipo, persona, estado) VALUES (?, 1, ?, 1)");
            foreach ($data['correos'] as $c) {
                $email = trim($c['email'] ?? '');
                if ($email !== '') {
                    $stmtCor->execute([$email, (int)$saved->getId()]);
                }
            }
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