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

            case $method === 'POST' && $resource === 'upload-logo':
                return $this->uploadLogo();

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

    // -------------------------------------------------------------------------
    // uploadLogo
    // -------------------------------------------------------------------------
    private function uploadLogo(): array
    {
        if (empty($_FILES['logo']) || (int)$_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            $code = (int)($_FILES['logo']['error'] ?? -1);
            $msg  = match ($code) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido.',
                UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente.',
                UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo.',
                default               => 'Error al recibir el archivo.',
            };
            throw new \RuntimeException($msg, 400);
        }

        $file     = $_FILES['logo'];
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mime     = $finfo->file($file['tmp_name']);
        $allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];

        if (!isset($allowed[$mime])) {
            throw new \RuntimeException('Tipo de archivo no permitido. Solo imágenes JPG, PNG, GIF o WebP.', 400);
        }

        $ext       = $allowed[$mime];
        $filename  = 'inst_' . uniqid('', true) . '.' . $ext;
        $uploadDir = realpath(__DIR__ . '/../../../') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR;

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new \RuntimeException('No se pudo crear el directorio de imágenes.', 500);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            throw new \RuntimeException('Error al guardar el archivo en el servidor.', 500);
        }

        return ['path' => '/assets/logo/' . $filename];
    }

    private function createInstitucionAndAdmin(?string $ruc, string $razonSocial, string $nombre, ?string $logotipo, int $estado): array
    {
        if (empty($ruc)) {
            throw new \RuntimeException('El RUC es requerido para crear una institución y su administrador.', 400);
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 1. Check for duplicates in users and institutions
            $stmt = $db->prepare("SELECT id FROM users WHERE username = :ruc");
            $stmt->execute([':ruc' => $ruc]);
            if ($stmt->fetch()) {
                throw new \RuntimeException("El RUC '{$ruc}' ya está en uso como nombre de usuario.", 409);
            }

            $stmt = $db->prepare("SELECT id FROM instituciones WHERE ruc = :ruc");
            $stmt->execute([':ruc' => $ruc]);
            if ($stmt->fetch()) {
                throw new \RuntimeException("Ya existe una institución registrada con el RUC '{$ruc}'.", 409);
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

            // 3. Get or Create Persona
            $stmt = $db->prepare("SELECT id, user FROM personas WHERE identificacion = :ruc");
            $stmt->execute([':ruc' => $ruc]);
            $existingPersona = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existingPersona) {
                $personaId = (int)$existingPersona['id'];
                // Optionally throw an error if this persona already has an active user that isn't the one we are creating.
                // For now, we will link it.
            } else {
                $stmt = $db->prepare(
                    "INSERT INTO personas (identificacion, primer_nombre, primer_apellido, direccion, nacionalidad, genero, etnia, ciudad, fecha_nacimiento) VALUES (:ruc, 'Administrador', :nombre_inst, 'N/A', :nac_id, 1, 1, 1, CURDATE())"
                );
                $stmt->execute([':ruc' => $ruc, ':nombre_inst' => $nombre, ':nac_id' => $nacionalidadId]);
                $personaId = (int)$db->lastInsertId();
            }

            // 4. Create User
            $adminPassword = '123456789';
            $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users (username, password, type) VALUES (:username, :password, 'TIPO_ADMIN')");
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
                throw new \RuntimeException("No se encontró el 'tipo_persona' con nemónico 'TIPO_ADMIN' para asociar al usuario.");
            }

            $stmt = $db->prepare("INSERT INTO persona_institucion (id_persona, id_institucion, tipo, estado) VALUES (:personaId, :instId, :tipo, 1)");
            $stmt->execute([':personaId' => $personaId, ':instId' => $instId, ':tipo' => $adminTipoPersonaId]);

            // 7. Grant all active buttons to TIPO_ADMIN for this institution
            $stmtBotones = $db->query("SELECT id FROM botones WHERE estado = 1");
            $botonIds    = $stmtBotones ? array_column($stmtBotones->fetchAll(\PDO::FETCH_ASSOC), 'id') : [];

            if (!empty($botonIds)) {
                $stmtBU = $db->prepare(
                    "INSERT IGNORE INTO boton_user (boton, type_user, institucion) VALUES (:boton, 'TIPO_ADMIN', :institucion)"
                );
                foreach ($botonIds as $botonId) {
                    $stmtBU->execute([
                        ':boton'       => $botonId,
                        ':institucion' => $instId,
                    ]);
                }
            }

            // 8. Create 4 academic periods starting from the current year
            $currentYear = (int) date('Y');
            $stmtPeriodo = $db->prepare(
                "INSERT INTO periodos_academicos (nombre, fecha_inicio, fecha_fin, institucion, estado) VALUES (:nombre, :fecha_inicio, :fecha_fin, :institucion, :estado)"
            );
            for ($i = 0; $i < 4; $i++) {
                $year = $currentYear + $i;
                $stmtPeriodo->execute([
                    ':nombre'       => "Periodo Lectivo {$year}",
                    ':fecha_inicio' => "{$year}-01-01",
                    ':fecha_fin'    => "{$year}-12-31",
                    ':institucion'  => $instId,
                    ':estado'       => 1,
                ]);
            }

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
