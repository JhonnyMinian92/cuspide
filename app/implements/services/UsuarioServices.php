<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/UsuarioServiceInterface.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/UserRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/entitys/User.php';
require_once __DIR__ . '/../persistence/repository/BotonRepository.php';
require_once __DIR__ . '/../persistence/entitys/Boton.php';
require_once __DIR__ . '/../persistence/repository/BotonUserRepository.php';
require_once __DIR__ . '/../persistence/entitys/BotonUser.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\UserRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Repositories\BotonRepository;
use Repositories\BotonUserRepository;
use Core\Database;
use Entitys\User;
use Entitys\Boton;
use Entitys\BotonUser;
use Utils\Token;
use Interfaces\UsuarioServiceInterface;

class UsuarioServices implements UsuarioServiceInterface
{
    private UserRepository $userRepo;
    private PersonaRepository $personaRepo;
    private BotonRepository $botonRepo;
    private BotonUserRepository $botonUserRepo;
    private array $security;

    public function __construct()
    {
        $this->personaRepo   = new PersonaRepository();
        $this->userRepo      = new UserRepository();
        $this->botonRepo     = new BotonRepository();
        $this->botonUserRepo = new BotonUserRepository();
        $this->security      = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        $userId   = (int) ($payload['sub'] ?? 0);

        // Detect resource
        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'list':
                return $this->fetchUsuarios($queryParams);

            case $method === 'GET' && $resource === 'buttons':
                return $this->fetchButtons($queryParams, $userId);

            case $method === 'POST' && $resource === 'button':
                return $this->createButton();

            case $method === 'PUT' && $resource === 'button-permission':
                return $this->toggleButtonPermission();

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                return $this->saveUsuario($method, $queryParams);

            case $method === 'PUT' && $resource === 'disable':
                return $this->disableUsuario((int)($queryParams['id'] ?? 0));

            case $method === 'GET' && $resource === 'search-personas':
                return $this->searchPersonas($queryParams);

            case $method === 'GET' && $resource === 'types':
                return $this->fetchUserTypes();

            case $method === 'GET' && $resource === 'all-types':
                return $this->fetchAllUserTypes();

            case $method === 'GET' && $resource === 'permission-types':
                return $this->fetchPermissionUserTypes($userId);

            case $method === 'DELETE' && $resource === 'button':
                return $this->softDeleteButton((int)($queryParams['id'] ?? 0));

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchUsuarios
    // -------------------------------------------------------------------------
    private function fetchUsuarios(array $queryParams): array
    {
        $db = Database::getInstance();
        $q = trim((string)($queryParams['q'] ?? ''));

        $sql = "SELECT u.id, u.username, u.type, u.image,
                       p.id as persona_id, p.primer_nombre, p.primer_apellido
                FROM users u
                LEFT JOIN personas p ON u.id = p.user";

        $params = [];

        if ($q !== '') {
            $sql .= " WHERE (u.username LIKE :q OR p.primer_nombre LIKE :q OR p.primer_apellido LIKE :q)";
            $params[':q'] = "%{$q}%";
        }

        $sql .= " ORDER BY u.id DESC";

        if ($q === '') {
            $sql .= " LIMIT 20";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(static function (array $row): array {
            $type = (string) ($row['type'] ?? '');
            $nombreCompleto = trim(($row['primer_nombre'] ?? '') . ' ' . ($row['primer_apellido'] ?? ''));
            return [
                'id'              => (int) $row['id'],
                'username'        => (string) $row['username'],
                'nombre_completo' => $nombreCompleto !== '' ? $nombreCompleto : null,
                'persona_id'      => isset($row['persona_id']) ? (int)$row['persona_id'] : null,
                'type'            => $type,
                'image'           => $row['image'] ?? null,
                'estado'          => strtoupper($type) === 'INACTIVO' ? 0 : 1,
            ];
        }, $rows);
    }

    // -------------------------------------------------------------------------
    // fetchButtons
    // -------------------------------------------------------------------------
    private function fetchButtons(array $queryParams, int $userId): array
    {
        $tipoUser      = $queryParams['tipo_user'] ?? '';
        $institucionId = $this->getInstitucionByUserId($userId);

        if ($tipoUser === '') {
            throw new \RuntimeException('El parámetro tipo_user es requerido.', 400);
        }

        $db = Database::getInstance();

        // 1. Get all active buttons from the main 'botones' table.
        $stmtAll = $db->prepare("SELECT id, orden, nombre, icono, tipo FROM botones WHERE estado = 1 ORDER BY orden ASC, nombre ASC");
        $stmtAll->execute();
        $allButtons = $stmtAll->fetchAll(\PDO::FETCH_ASSOC);

        // 2. Get the IDs of buttons assigned to this specific type_user and institution.
        $stmtAssigned = $db->prepare(
            "SELECT boton FROM boton_user WHERE type_user = :tipo_user AND institucion = :inst_id"
        );
        $stmtAssigned->execute([':tipo_user' => $tipoUser, ':inst_id' => $institucionId]);
        $assignedButtonIds = array_flip($stmtAssigned->fetchAll(\PDO::FETCH_COLUMN, 0));

        // 3. Combine the lists and set the state.
        $result = [];
        foreach ($allButtons as $button) {
            $buttonId = (int)$button['id'];
            $result[] = [
                'id'     => $buttonId,
                'orden'  => isset($button['orden']) ? (int)$button['orden'] : null,
                'nombre' => $button['nombre'],
                'icono'  => $button['icono'],
                'tipo'   => $button['tipo'],
                'estado' => isset($assignedButtonIds[$buttonId]) ? 1 : 0,
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // searchPersonas
    // -------------------------------------------------------------------------
    private function searchPersonas(array $queryParams): array
    {
        $q = trim((string)($queryParams['q'] ?? ''));
        if (strlen($q) < 2) {
            return [];
        }
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT id, identificacion, primer_nombre, primer_apellido
             FROM personas
             WHERE (user IS NULL OR user = 0) AND (identificacion LIKE :q OR primer_nombre LIKE :q OR primer_apellido LIKE :q)
             LIMIT 10"
        );
        $stmt->execute([':q' => "%{$q}%"]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------------------------
    // fetchUserTypes
    // -------------------------------------------------------------------------
    private function fetchUserTypes(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT nemonico, nombre FROM type_users WHERE nemonico != 'TIPO_DEV' ORDER BY nombre ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------------------------
    // fetchAllUserTypes
    // -------------------------------------------------------------------------
    private function fetchAllUserTypes(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT nemonico, nombre, descripcion, imagen FROM type_users ORDER BY nombre ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------------------------
    // fetchPermissionUserTypes
    // -------------------------------------------------------------------------
    private function fetchPermissionUserTypes(int $currentUserId): array
    {
        $db = Database::getInstance();

        $user = $this->userRepo->findById($currentUserId);
        $currentUserTypeObject = $user ? $user->getType() : null;
        $currentUserNemonico = $currentUserTypeObject && method_exists($currentUserTypeObject, 'getNemonico') ? $currentUserTypeObject->getNemonico() : '';

        $sql = "SELECT nemonico, nombre FROM type_users";

        if ($currentUserNemonico !== 'TIPO_DEV') {
            $sql .= " WHERE nemonico != 'TIPO_DEV'";
        }

        $sql .= " ORDER BY nombre ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $types = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'types' => $types,
            'currentUserType' => $currentUserNemonico,
        ];
    }

    // -------------------------------------------------------------------------
    // createButton
    // -------------------------------------------------------------------------
    private function createButton(): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['nombre', 'tipo_user'] as $field) {
            if (empty($data[$field])) {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $nombre   = trim($data['nombre']);
        $tipoUser = $data['tipo_user'];
        $ruta     = $data['ruta'] ?? null;

        $boton  = new Boton(
            null,
            $data['orden'] ?? null,
            $nombre,
            $data['icono'] ?? '',
            $ruta,
            $data['color'] ?? null,
            1
        );
        $saved = $this->botonRepo->save($boton);

        $bu = new BotonUser(
            null,
            (int)$saved->getId(),
            $tipoUser,
            isset($data['institucion']) ? (int)$data['institucion'] : null
        );
        $this->botonUserRepo->save($bu);

        return [
            'id'     => $saved->getId(),
            'nombre' => $saved->getNombre(),
        ];
    }

    // -------------------------------------------------------------------------
    // toggleButtonPermission
    // -------------------------------------------------------------------------
    private function toggleButtonPermission(): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        $buttonId = (int)($data['button_id'] ?? 0);
        $tipoUser = (string)($data['tipo_user'] ?? '');
        $institucionId = (int)($data['institucion_id'] ?? 0);
        $estado = (int)$data['estado'];

        if ($buttonId <= 0 || $tipoUser === '' || $institucionId <= 0) {
            throw new \RuntimeException('Faltan parámetros requeridos: button_id, tipo_user, institucion_id.', 400);
        }

        $db = Database::getInstance();

        if ($estado === 1) { // Activate: create record in boton_user
            $stmt = $db->prepare(
                "INSERT IGNORE INTO boton_user (boton, type_user, institucion) VALUES (:boton, :type_user, :institucion)"
            );
            $stmt->execute([
                ':boton' => $buttonId,
                ':type_user' => $tipoUser,
                ':institucion' => $institucionId,
            ]);
        } else { // Deactivate: delete record from boton_user
            $stmt = $db->prepare(
                "DELETE FROM boton_user WHERE boton = :boton AND type_user = :type_user AND institucion = :institucion"
            );
            $stmt->execute([
                ':boton' => $buttonId,
                ':type_user' => $tipoUser,
                ':institucion' => $institucionId,
            ]);
        }

        return [
            'button_id' => $buttonId,
            'estado'    => $estado,
            'message'   => 'Permiso actualizado.',
        ];
    }

    private function softDeleteButton(int $id): array
    {
        $boton = $this->botonRepo->findById($id);
        if ($boton === null) {
            throw new \RuntimeException('Botón no encontrado.', 404);
        }
        $boton->setEstado(0);
        $this->botonRepo->save($boton);

        return [
            'id'      => $id,
            'message' => 'Botón deshabilitado globalmente.',
        ];
    }

    // -------------------------------------------------------------------------
    // saveUsuario
    // -------------------------------------------------------------------------
    private function saveUsuario(string $method, array $queryParams): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        $paramId = isset($queryParams['id']) ? (int)$queryParams['id'] : 0;
        $bodyId  = isset($data['id'])        ? (int)$data['id']        : 0;
        $userId  = $bodyId > 0 ? $bodyId : $paramId;

        if ($method === 'PUT' && $userId > 0) {
            // Update
            $existing = $this->userRepo->findById($userId);
            if (!$existing) {
                throw new \RuntimeException('Usuario no encontrado para actualizar.', 404);
            }
            if ($existing->getType() === 'TIPO_DEV') {
                throw new \RuntimeException('No se permite modificar usuarios de tipo Desarrollador.', 403);
            }

            $username = $data['username'] ?? $existing->getUsername();
            $tipoUser = $data['tipo_user'] ?? $data['type'] ?? $existing->getType();
            $imagen   = $data['imagen']   ?? $data['image'] ?? $existing->getImage();

            if (!empty($data['password'])) {
                // New password provided — let UserRepository hash it
                $user  = new User($userId, $username, $data['password'], $tipoUser, $imagen);
                $saved = $this->userRepo->save($user);
            } else {
                // No password change — update without re-hashing
                $saved = $this->userRepo->updateWithoutPassword($userId, $username, $tipoUser, $imagen);
            }
        } else {
            // Create
            foreach (['username', 'password'] as $field) {
                if (empty($data[$field])) {
                    throw new \RuntimeException("El campo {$field} es requerido.", 400);
                }
            }

            $username = trim($data['username']);
            // Check uniqueness
            $check = $this->userRepo->findByUsername($username);
            if ($check) {
                throw new \RuntimeException('El nombre de usuario ya existe.', 400);
            }

            $tipoUser = $data['tipo_user'] ?? $data['type'] ?? 'TIPO_EST';
            $imagen   = $data['imagen']    ?? $data['image'] ?? null;

            $user  = new User(null, $username, $data['password'], $tipoUser, $imagen);
            $saved = $this->userRepo->save($user);
        }

        $savedId = (int)$saved->getId();
        $personaId = isset($data['persona_id']) && $data['persona_id'] !== '' ? (int)$data['persona_id'] : null;

        $db = \Core\Database::getInstance();

        // Unlink this user from any previously associated persona
        $stmt = $db->prepare("UPDATE personas SET user = NULL WHERE user = :user_id");
        $stmt->execute([':user_id' => $savedId]);

        if ($personaId !== null) {
            $persona = $this->personaRepo->findById($personaId);
            if (!$persona) {
                throw new \RuntimeException('La persona a vincular no existe.', 404);
            }
            /*if ($persona->getUser() !== null && $persona->getUser()->getId() !== $savedId) {
                throw new \RuntimeException('La persona seleccionada ya está vinculada a otro usuario.', 409);
            }*/
            $stmt = $db->prepare("UPDATE personas SET user = :user_id WHERE id = :persona_id");
            $stmt->execute([':user_id' => $savedId, ':persona_id' => $personaId]);
        }

        return [
            'id'        => (int)$saved->getId(),
            'username'  => $saved->getUsername(),
            'tipo_user' => $saved->getType(),
        ];
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
        $pi = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());
        if ($pi === null) {
            throw new \RuntimeException('No se encontró institución asociada al usuario.', 404);
        }
        return (int) $pi->getIdInstitucion()->getId();
    }

    // -------------------------------------------------------------------------
    // disableUsuario
    // -------------------------------------------------------------------------
    private function disableUsuario(int $id): array
    {
        if ($id <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $existing = $this->userRepo->findById($id);
        if (!$existing) {
            throw new \RuntimeException('Usuario no encontrado.', 404);
        }
        if ($existing->getType() === 'TIPO_DEV') {
            throw new \RuntimeException('No se permite deshabilitar usuarios de tipo Desarrollador.', 403);
        }

        $this->userRepo->disableById($id);
        return [
            'id'      => $id,
            'message' => 'Usuario desactivado correctamente.',
        ];
    }
}