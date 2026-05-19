<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/InstitucionServiceInterface.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/InstitucionRepository.php';
require_once __DIR__ . '/../persistence/entitys/Institucion.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\InstitucionRepository;
use Core\Database;
use Entitys\Institucion;
use Utils\Token;
use Interfaces\InstitucionServiceInterface;

class InstitucionServices implements InstitucionServiceInterface
{
    private InstitucionRepository $institucionRepo;
    private array $security;

    public function __construct()
    {
        $this->institucionRepo = new InstitucionRepository();
        $this->security        = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        // Detect resource
        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'data':
                return $this->fetchData((int)($queryParams['id'] ?? 0));

            case $method === 'GET' && $resource === 'list':
                return $this->fetchAll();

            case $method === 'POST' && $resource === 'save':
                return $this->saveInstitucion(null);

            case $method === 'PUT' && $resource === 'save':
                return $this->saveInstitucion((int)($queryParams['id'] ?? 0));

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchData
    // -------------------------------------------------------------------------
    private function fetchData(int $id): array
    {
        if ($id <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $inst = $this->institucionRepo->findById($id);
        if (!$inst) {
            throw new \RuntimeException('Institución no encontrada.', 404);
        }

        return [
            'id'           => (int)$inst->getId(),
            'ruc'          => $inst->getRuc(),
            'razon_social' => $inst->getRazonSocial(),
            'nombre'       => $inst->getNombre(),
            'logotipo'     => $inst->getLogotipo(),
            'estado'       => (int)$inst->getEstado()?->getId(),
        ];
    }

    // -------------------------------------------------------------------------
    // fetchAll
    // -------------------------------------------------------------------------
    private function fetchAll(): array
    {
        $instituciones = $this->institucionRepo->findAll();
        $result        = [];
        foreach ($instituciones as $inst) {
            $result[] = [
                'id'           => (int)$inst->getId(),
                'ruc'          => $inst->getRuc(),
                'razon_social' => $inst->getRazonSocial(),
                'nombre'       => $inst->getNombre(),
                'logotipo'     => $inst->getLogotipo(),
                'estado'       => (int)$inst->getEstado()?->getId(),
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // saveInstitucion
    // -------------------------------------------------------------------------
    private function saveInstitucion(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['razon_social', 'nombre'] as $field) {
            if (empty($data[$field])) {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $ruc         = $data['ruc']          ?? null;
        $razonSocial = trim($data['razon_social']);
        $nombre      = trim($data['nombre']);
        $logotipo    = $data['logotipo']     ?? null;
        $estado      = isset($data['estado']) ? (int)$data['estado'] : 1;

        if ($id !== null && $id > 0) { // UPDATE
            $existing = $this->institucionRepo->findById($id);
            if (!$existing) {
                throw new \RuntimeException('Institución no encontrada para actualizar.', 404);
            }
            $ruc      = $ruc      ?? $existing->getRuc();
            $logotipo = $logotipo ?? $existing->getLogotipo();

            $inst  = new Institucion($id, $ruc, $razonSocial, $nombre, $logotipo, $estado);
            $saved = $this->institucionRepo->save($inst);

            return [
                'id'           => (int)$saved->getId(),
                'ruc'          => $saved->getRuc(),
                'razon_social' => $saved->getRazonSocial(),
                'nombre'       => $saved->getNombre(),
                'logotipo'     => $saved->getLogotipo(),
                'estado'       => (int)$saved->getEstado()?->getId(),
            ];
        } else { // CREATE
            return $this->createInstitucionAndAdmin($ruc, $razonSocial, $nombre, $logotipo, $estado);
        }
    }

    private function createInstitucionAndAdmin(?string $ruc, string $razonSocial, string $nombre, ?string $logotipo, int $estado): array
    {
        if (empty($ruc)) {
            throw new \RuntimeException('El RUC es requerido para crear una institución y su administrador.', 400);
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 1. Check for duplicates
            $stmt = $db->prepare("SELECT id FROM users WHERE username = :ruc");
            $stmt->execute([':ruc' => $ruc]);
            if ($stmt->fetch()) {
                throw new \RuntimeException("El RUC '{$ruc}' ya está en uso como nombre de usuario.", 409);
            }

            // 2. Create Institucion
            $inst  = new Institucion(null, $ruc, $razonSocial, $nombre, $logotipo, $estado);
            $savedInst = $this->institucionRepo->save($inst);
            $instId = $savedInst->getId();

            // Get default nationality ID to prevent FK constraint violation
            $stmtNac = $db->prepare("SELECT id FROM nacionalidades WHERE nemonico = 'ECU' OR nombre = 'Ecuatoriana' LIMIT 1");
            $stmtNac->execute();
            $nacionalidadId = $stmtNac->fetchColumn();
            if (!$nacionalidadId) {
                // Fallback to the first one if 'ECU' is not found
                $stmtNacFallback = $db->query("SELECT id FROM nacionalidades ORDER BY id ASC LIMIT 1");
                $nacionalidadId = $stmtNacFallback ? $stmtNacFallback->fetchColumn() : 1; // Last resort
                if (!$nacionalidadId) {
                    throw new \RuntimeException("No se encontraron nacionalidades para asignar al administrador.");
                }
            }

            // 3. Create Persona
            $stmt = $db->prepare(
                "INSERT INTO personas (identificacion, primer_nombre, primer_apellido, direccion, nacionalidad, genero, etnia, ciudad, fecha_nacimiento) VALUES (:ruc, 'Administrador', :nombre_inst, 'N/A', :nac_id, 1, 1, 1, CURDATE())"
            );
            $stmt->execute([':ruc' => $ruc, ':nombre_inst' => $nombre, ':nac_id' => $nacionalidadId]);
            $personaId = (int)$db->lastInsertId();

            // 4. Create User
            $adminPassword = '123456789';
            $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users (username, password, type) VALUES (:username, :password, 'ADMIN_USER')");
            $stmt->execute([':username' => $ruc, ':password' => $hashedPassword]);
            $userId = (int)$db->lastInsertId();

            // 5. Link User to Persona
            $stmt = $db->prepare("UPDATE personas SET user = :userId WHERE id = :personaId");
            $stmt->execute([':userId' => $userId, ':personaId' => $personaId]);

            // 6. Link Persona to Institucion
            $stmt = $db->prepare("SELECT id FROM tipo_persona WHERE nemonico = 'TIPO_ADMIN' OR nombre = 'Administrador' LIMIT 1");
            $stmt->execute();
            $adminTipoPersonaId = $stmt->fetchColumn();
            if (!$adminTipoPersonaId) {
                throw new \RuntimeException("No se encontró el 'tipo_persona' con nemónico 'ADMIN_USER' para asociar al usuario.");
            }

            $stmt = $db->prepare("INSERT INTO persona_institucion (id_persona, id_institucion, tipo, estado) VALUES (:personaId, :instId, :tipo, 1)");
            $stmt->execute([':personaId' => $personaId, ':instId' => $instId, ':tipo' => $adminTipoPersonaId]);

            $db->commit();

            return [
                'id' => (int)$savedInst->getId(),
                'ruc' => $savedInst->getRuc(),
                'razon_social' => $savedInst->getRazonSocial(),
                'nombre' => $savedInst->getNombre(),
                'logotipo' => $savedInst->getLogotipo(),
                'estado' => (int)$savedInst->getEstado()?->getId(),
            ];
        } catch (\Exception $e) {
            $db->rollBack();
            throw new \RuntimeException('Error al crear la institución y su administrador: ' . $e->getMessage(), 500);
        }
    }
}
