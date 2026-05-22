<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/TelefonoRepository.php';
require_once __DIR__ . '/../persistence/repository/CorreoRepository.php';
require_once __DIR__ . '/../persistence/repository/MembresiaRepository.php';
require_once __DIR__ . '/../persistence/repository/SocioRepository.php';
require_once __DIR__ . '/../persistence/repository/GeneroRepository.php';
require_once __DIR__ . '/../persistence/repository/EtniaRepository.php';
require_once __DIR__ . '/../persistence/repository/NacionalidadRepository.php';
require_once __DIR__ . '/../persistence/entitys/Persona.php';
require_once __DIR__ . '/../persistence/entitys/PersonaInstitucion.php';
require_once __DIR__ . '/../persistence/entitys/Socio.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Repositories\TelefonoRepository;
use Repositories\CorreoRepository;
use Repositories\MembresiaRepository;
use Repositories\SocioRepository;
use Repositories\GeneroRepository;
use Repositories\EtniaRepository;
use Repositories\NacionalidadRepository;
use Entitys\Persona;
use Entitys\PersonaInstitucion;
use Entitys\Socio;
use Utils\Token;

class SocioServices
{
  private PersonaRepository $personaRepo;
  private PersonaInstitucionRepository $piRepo;
  private TelefonoRepository $telefonoRepo;
  private CorreoRepository $correoRepo;
  private MembresiaRepository $membresiaRepo;
  private SocioRepository $socioRepo;
  private GeneroRepository $generoRepo;
  private EtniaRepository $etniaRepo;
  private NacionalidadRepository $nacRepo;
  private array $security;

  public function __construct()
  {
    $this->personaRepo   = new PersonaRepository();
    $this->piRepo        = new PersonaInstitucionRepository();
    $this->telefonoRepo  = new TelefonoRepository();
    $this->correoRepo    = new CorreoRepository();
    $this->membresiaRepo = new MembresiaRepository();
    $this->socioRepo     = new SocioRepository();
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

    $resource = $queryParams['api'] ?? '';
    if ($resource === '') {
      $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
      $resource = strtolower(end($parts) ?: '');
    }

    $userId = (int) ($payload['sub'] ?? 0);

    switch (true) {
      case $method === 'GET' && $resource === 'catalogs':
        return $this->fetchCatalogs($userId);

      case $method === 'GET' && $resource === 'list':
        return $this->fetchList($userId, $queryParams);

      case $method === 'GET' && $resource === 'profile':
        return $this->fetchProfile($queryParams);

      case in_array($method, ['POST', 'PUT'], true) && $resource === 'savesocio':
        return $this->saveSocio($userId, $queryParams);

      default:
        throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
    }
  }

  // -----------------------------------------------------------------------
  // fetchCatalogs
  // -----------------------------------------------------------------------
  private function fetchCatalogs(int $userId): array
  {
    $instId = $this->getInstitucionByUserId($userId);
    $db     = \Core\Database::getInstance();

    $generos        = $this->generoRepo->findAll();
    $etnias         = $this->etniaRepo->findAll();
    $nacionalidades = $this->nacRepo->findAll();

    $ciudades = $db->query("SELECT id, nombre, provincia FROM ciudades ORDER BY provincia ASC, nombre ASC")
                   ->fetchAll(\PDO::FETCH_ASSOC) ?: [];

    $stmtTipo = $db->prepare("SELECT id, nombre, nemonico FROM tipo_persona WHERE nemonico = 'TIPO_SOCIO' LIMIT 1");
    $stmtTipo->execute();
    $tipoSocio = $stmtTipo->fetch(\PDO::FETCH_ASSOC) ?: null;

    $membresias = $this->membresiaRepo->findByInstitucion($instId);

    return [
      'generos'        => array_map(fn($g) => ['id' => $g->getId(), 'nombre' => $g->getNombre()], $generos),
      'etnias'         => array_map(fn($e) => ['id' => $e->getId(), 'nombre' => $e->getNombre()], $etnias),
      'nacionalidades' => array_map(fn($n) => ['id' => $n->getId(), 'nombre' => $n->getNombre()], $nacionalidades),
      'ciudades'       => array_map(fn($c) => ['id' => (int)$c['id'], 'nombre' => $c['nombre'], 'provincia' => $c['provincia']], $ciudades),
      'tipo_socio'     => $tipoSocio ? ['id' => (int)$tipoSocio['id'], 'nombre' => $tipoSocio['nombre'], 'nemonico' => $tipoSocio['nemonico']] : null,
      'membresias'     => array_map(fn($m) => [
        'id'          => $m->getId(),
        'titulo'      => $m->getTitulo(),
        'precio'      => $m->getPrecio(),
        'descripcion' => $m->getDescripcion(),
        'fecha_inicio' => $m->getFechaInicio(),
        'fecha_fin'   => $m->getFechaFin(),
        'periodo'     => $m->getPeriodo(),
      ], $membresias),
    ];
  }

  // -----------------------------------------------------------------------
  // fetchList
  // -----------------------------------------------------------------------
  private function fetchList(int $userId, array $queryParams): array
  {
    $instId = $this->getInstitucionByUserId($userId);
    $q      = trim($queryParams['q'] ?? '');
    $limit  = min((int)($queryParams['limit'] ?? 20), 100);
    $offset = (int)($queryParams['offset'] ?? 0);

    $rows  = $this->socioRepo->findByInstitucion($instId, $q, $limit, $offset);
    $total = $this->socioRepo->countByInstitucion($instId, $q);

    $items = array_map(fn($r) => [
      'id'               => (int) $r['socio_id'],
      'persona_id'       => (int) $r['persona_id'],
      'membresia_id'     => (int) $r['membresia_id'],
      'identificacion'   => $r['identificacion'],
      'nombre_completo'  => trim($r['nombre_completo']),
      'primer_nombre'    => $r['primer_nombre'],
      'primer_apellido'  => $r['primer_apellido'],
      'membresia_titulo' => $r['membresia_titulo'],
      'membresia_precio' => $r['membresia_precio'],
      'fecha_inicio'     => $r['fecha_inicio'],
      'fecha_fin'        => $r['fecha_fin'],
      'estado'           => (int) $r['socio_estado'],
    ], $rows);

    return ['total' => $total, 'items' => $items];
  }

  // -----------------------------------------------------------------------
  // fetchProfile
  // -----------------------------------------------------------------------
  private function fetchProfile(array $queryParams): array
  {
    $personaId = isset($queryParams['id']) ? (int)$queryParams['id'] : 0;
    if ($personaId <= 0) {
      throw new \RuntimeException('El parámetro id es requerido.', 400);
    }

    $persona = $this->personaRepo->findById($personaId);
    if (!$persona) {
      throw new \RuntimeException('Persona no encontrada.', 404);
    }

    $telefonos = $this->telefonoRepo->findByPersonaId($personaId);
    $correos   = $this->correoRepo->findByPersonaId($personaId);
    $socio     = $this->socioRepo->findByPersonaId($personaId);

    $genero       = $persona->getGenero();
    $etnia        = $persona->getEtnia();
    $nacionalidad = $persona->getNacionalidad();
    $ciudad       = $persona->getCiudad();

    return [
      'id'               => (int) $persona->getId(),
      'identificacion'   => $persona->getIdentificacion(),
      'nombre_completo'  => trim(
        ($persona->getPrimerNombre()    ?? '') . ' ' .
        ($persona->getSegundoNombre()   ?? '') . ' ' .
        ($persona->getPrimerApellido()  ?? '') . ' ' .
        ($persona->getSegundoApellido() ?? '')
      ),
      'primer_nombre'    => $persona->getPrimerNombre(),
      'segundo_nombre'   => $persona->getSegundoNombre(),
      'primer_apellido'  => $persona->getPrimerApellido(),
      'segundo_apellido' => $persona->getSegundoApellido(),
      'fecha_nacimiento' => $persona->getFechaNacimiento(),
      'direccion'        => $persona->getDireccion(),
      'genero_id'        => $genero       ? (int) $genero->getId()       : null,
      'etnia_id'         => $etnia        ? (int) $etnia->getId()        : null,
      'nacionalidad_id'  => $nacionalidad ? (int) $nacionalidad->getId() : null,
      'ciudad_id'        => $ciudad       ? (int) $ciudad->getId()       : null,
      'telefonos'        => array_map(fn($t) => ['id' => $t->getId(), 'numero' => $t->getNumero()], $telefonos),
      'correos'          => array_map(fn($c) => ['id' => $c->getId(), 'email'  => $c->getEmail()],  $correos),
      'socio_id'         => $socio ? $socio->getId()          : null,
      'membresia_id'     => $socio ? $socio->getMembresiaId() : null,
      'fecha_inicio'     => $socio ? $socio->getFechaInicio() : null,
      'fecha_fin'        => $socio ? $socio->getFechaFin()    : null,
      'socio_estado'     => $socio ? $socio->getEstado()      : null,
    ];
  }

  // -----------------------------------------------------------------------
  // saveSocio
  // -----------------------------------------------------------------------
  private function saveSocio(int $userId, array $queryParams): array
  {
    $instId = $this->getInstitucionByUserId($userId);
    $data   = json_decode((string) file_get_contents('php://input'), true) ?: [];

    foreach (['identificacion', 'primer_nombre', 'primer_apellido', 'membresia_id'] as $field) {
      if (empty($data[$field])) {
        throw new \RuntimeException("El campo {$field} es requerido.", 400);
      }
    }

    $membresiaId = (int) $data['membresia_id'];
    $membresia   = $this->membresiaRepo->findById($membresiaId);
    if (!$membresia) {
      throw new \RuntimeException('Membresía no encontrada.', 404);
    }

    // Resolve tipo_persona for SOCIO
    $db = \Core\Database::getInstance();
    $stmtTipo = $db->prepare("SELECT id FROM tipo_persona WHERE nemonico = 'TIPO_SOCIO' LIMIT 1");
    $stmtTipo->execute();
    $tipoId = (int) ($stmtTipo->fetchColumn() ?: 103);

    $paramId   = isset($queryParams['id']) ? (int)$queryParams['id'] : 0;
    $bodyId    = isset($data['id'])        ? (int)$data['id']        : 0;
    $personaId = $bodyId > 0 ? $bodyId : $paramId;

    $identificacion  = trim($data['identificacion']);
    $primerNombre    = trim($data['primer_nombre']);
    $segundoNombre   = trim($data['segundo_nombre']   ?? '');
    $primerApellido  = trim($data['primer_apellido']);
    $segundoApellido = trim($data['segundo_apellido'] ?? '');
    $fechaNacimiento = $data['fecha_nacimiento'] ?? null;
    $direccion       = $data['direccion']       ?? null;
    $genero          = $data['genero']          ?? null;
    $etnia           = $data['etnia']           ?? null;
    $nacionalidad    = $data['nacionalidad']    ?? null;
    $ciudad          = $data['ciudad']          ?? null;

    $fechaInicio = $data['fecha_inicio'] ?? $membresia->getFechaInicio();
    $fechaFin    = $data['fecha_fin']    ?? $membresia->getFechaFin();

    $existente = $this->personaRepo->findByIdentificacion($identificacion);

    $db->beginTransaction();
    try {
      if ($personaId > 0 || $existente) {
        $persona = $existente ?? $this->personaRepo->findById($personaId);
        if (!$persona) {
          throw new \RuntimeException('Persona no encontrada para actualizar.', 404);
        }
        $updated = new Persona(
          (int) $persona->getId(),
          $identificacion, $primerNombre, $segundoNombre,
          $primerApellido, $segundoApellido,
          $nacionalidad, $genero, $etnia, $fechaNacimiento, $ciudad, $direccion,
          $persona->getUser()
        );
        $saved = $this->personaRepo->save($updated);

        $pi = $this->piRepo->findByPersonaAndInstitucion((int)$saved->getId(), $instId);
        $piEntity = new PersonaInstitucion(
          $pi ? (int)$pi->getId() : null,
          (int)$saved->getId(), $instId, $tipoId, 1
        );
        $this->piRepo->save($piEntity);
      } else {
        $persona = new Persona(
          null, $identificacion, $primerNombre, $segundoNombre,
          $primerApellido, $segundoApellido,
          $nacionalidad, $genero, $etnia, $fechaNacimiento, $ciudad, $direccion, null
        );
        $saved = $this->personaRepo->save($persona);
        $piEntity = new PersonaInstitucion(null, (int)$saved->getId(), $instId, $tipoId, 1);
        $this->piRepo->save($piEntity);
      }

      $pid = (int) $saved->getId();

      // Save phones
      if (isset($data['telefonos']) && is_array($data['telefonos'])) {
        $db->prepare("UPDATE telefonos SET estado = 2 WHERE persona = ?")->execute([$pid]);
        $stmtTel = $db->prepare("INSERT INTO telefonos (numero, operadora, persona, tipo, estado) VALUES (?, 1, ?, 1, 1)");
        foreach ($data['telefonos'] as $t) {
          $num = trim($t['numero'] ?? '');
          if ($num !== '') {
            $stmtTel->execute([$num, $pid]);
          }
        }
      }

      // Save emails
      if (isset($data['correos']) && is_array($data['correos'])) {
        $db->prepare("UPDATE correos SET estado = 2 WHERE persona = ?")->execute([$pid]);
        $stmtCor = $db->prepare("INSERT INTO correos (email, tipo, persona, estado) VALUES (?, 1, ?, 1)");
        foreach ($data['correos'] as $c) {
          $email = trim($c['email'] ?? '');
          if ($email !== '') {
            $stmtCor->execute([$email, $pid]);
          }
        }
      }

      // Save or update socios record
      $socio = $this->socioRepo->findByPersonaAndInstitucion($pid, $instId);
      $socioEntity = new Socio(
        $socio ? $socio->getId() : null,
        $pid, $membresiaId,
        substr($fechaInicio, 0, 10),
        substr($fechaFin, 0, 10),
        1, $instId
      );
      $this->socioRepo->save($socioEntity);

      // Regenerate mensualidades: delete fully unpaid, then insert one per month
      $db->prepare(
        "DELETE FROM ventas WHERE persona = :pid AND tipo = 'MENSUALIDAD' AND saldo = total AND estado = 1 AND institucion = :inst"
      )->execute([':pid' => $pid, ':inst' => $instId]);

      $precio = (float) $membresia->getPrecio();
      $stmtV  = $db->prepare(
        "INSERT INTO ventas (numero, fecha, persona, matricula, institucion, tipo, subtotal, descuento, impuesto, total, saldo, estado)
         VALUES (:num, :fecha, :pid, NULL, :inst, 'MENSUALIDAD', :sub, 0, 0, :total, :saldo, 1)"
      );

      $current = new \DateTime(substr($fechaInicio, 0, 10));
      $current->modify('first day of this month');
      $fin = new \DateTime(substr($fechaFin, 0, 10));

      while ($current <= $fin) {
        $stmtV->execute([
          ':num'   => 'MENS-' . $current->format('Ym') . '-' . $pid . '-' . substr(uniqid(), -5),
          ':fecha' => $current->format('Y-m-d') . ' 00:00:00',
          ':pid'   => $pid,
          ':inst'  => $instId,
          ':sub'   => $precio,
          ':total' => $precio,
          ':saldo' => $precio,
        ]);
        $current->modify('+1 month');
      }

      $db->commit();
    } catch (\Throwable $e) {
      $db->rollBack();
      throw $e;
    }

    return [
      'id'              => $pid,
      'identificacion'  => $identificacion,
      'nombre_completo' => trim("$primerNombre $segundoNombre $primerApellido $segundoApellido"),
    ];
  }

  // -----------------------------------------------------------------------
  // getInstitucionByUserId
  // -----------------------------------------------------------------------
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
}
