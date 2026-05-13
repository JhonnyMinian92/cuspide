<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/UsuarioServiceInterface.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/UserRepository.php';
require_once __DIR__ . '/../persistence/entitys/User.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Repositories\UserRepository;
use Entitys\User;
use Utils\Token;
use Interfaces\UsuarioServiceInterface;

class UsuarioServices implements UsuarioServiceInterface
{
    private UserRepository $userRepo;
    private array $security;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
        $this->security = require __DIR__ . '/../../properties/security.php';
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
            case $method === 'GET' && $resource === 'list':
                return $this->fetchUsuarios();

            case $method === 'GET' && $resource === 'buttons':
                return $this->fetchButtons($queryParams);

            case $method === 'POST' && $resource === 'button':
                return $this->createButton();

            case $method === 'PUT' && $resource === 'button':
                return $this->toggleButton((int)($queryParams['id'] ?? 0));

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                return $this->saveUsuario($method, $queryParams);

            case $method === 'PUT' && $resource === 'disable':
                return $this->disableUsuario((int)($queryParams['id'] ?? 0));

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchUsuarios
    // -------------------------------------------------------------------------
    private function fetchUsuarios(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT id, username, type, image
             FROM users
             ORDER BY username ASC"
        );
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(static function (array $row): array {
            $type = (string) ($row['type'] ?? '');
            return [
                'id'       => (int) $row['id'],
                'username' => (string) $row['username'],
                'type'     => $type,
                'tipo_user'=> $type,
                'image'    => $row['image'] ?? null,
                'imagen'   => $row['image'] ?? null,
                'estado'   => strtoupper($type) === 'INACTIVO' ? 0 : 1,
            ];
        }, $rows);
    }

    // -------------------------------------------------------------------------
    // fetchButtons
    // -------------------------------------------------------------------------
    private function fetchButtons(array $queryParams): array
    {
        $tipoUser   = $queryParams['tipo_user']  ?? '';
        $institucion = $queryParams['institucion'] ?? '';

        if ($tipoUser === '') {
            throw new \RuntimeException('El parámetro tipo_user es requerido.', 400);
        }

        $db = Database::getInstance();

        $sql = "SELECT b.id, b.orden, b.nombre, b.tipo AS ruta, b.icono, b.estado
                FROM boton_user bu
                JOIN botones b ON b.id = bu.boton
                WHERE bu.type_user = :tipo
                  AND b.estado = 1";

        $params = [':tipo' => $tipoUser];

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        $rows   = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'     => (int)$row['id'],
                'orden'  => isset($row['orden']) ? (int)$row['orden'] : null,
                'nombre' => $row['nombre'],
                'ruta'   => $row['ruta'],
                'icono'  => $row['icono'],
                'estado' => (int)($row['estado'] ?? 1),
            ];
        }
        return $result;
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

        foreach (['nombre', 'ruta', 'tipo_user'] as $field) {
            if (empty($data[$field])) {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $nombre      = trim($data['nombre']);
        $ruta        = trim($data['ruta']);
        $icono       = $data['icono']       ?? null;
        $tipoUser    = $data['tipo_user'];
        $estado      = 1;

        $db = Database::getInstance();

        $ordenStmt = $db->query('SELECT COALESCE(MAX(orden), 0) + 1 AS next_orden FROM botones');
        $orden = (int)($ordenStmt->fetch(\PDO::FETCH_ASSOC)['next_orden'] ?? 1);

        $sql = "INSERT INTO botones (orden, nombre, icono, tipo, color, estado)
                VALUES (:orden, :nombre, :icono, :tipo, NULL, :estado)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':orden',       $orden, \PDO::PARAM_INT);
        $stmt->bindValue(':nombre',      $nombre);
        $stmt->bindValue(':icono',       $icono ?: 'bi-circle');
        $stmt->bindValue(':tipo',        $ruta);
        $stmt->bindValue(':estado',      $estado, \PDO::PARAM_INT);
        $stmt->execute();

        $newId = (int)$db->lastInsertId();

        $rel = $db->prepare('INSERT INTO boton_user (boton, type_user) VALUES (:boton, :type_user)');
        $rel->execute([':boton' => $newId, ':type_user' => $tipoUser]);

        return [
            'id'     => $newId,
            'nombre' => $nombre,
            'ruta'   => $ruta,
        ];
    }

    // -------------------------------------------------------------------------
    // toggleButton
    // -------------------------------------------------------------------------
    private function toggleButton(int $id): array
    {
        if ($id <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data) || !isset($data['estado'])) {
            throw new \RuntimeException('El campo estado es requerido.', 400);
        }

        $estado = (int)$data['estado'];
        if (!in_array($estado, [0, 1], true)) {
            throw new \RuntimeException('El campo estado debe ser 0 o 1.', 400);
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare("UPDATE botones SET estado = :estado WHERE id = :id");
        $stmt->bindValue(':estado', $estado, \PDO::PARAM_INT);
        $stmt->bindValue(':id',     $id,     \PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Botón no encontrado.', 404);
        }

        return [
            'id'     => $id,
            'estado' => $estado,
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

            $username = $data['username'] ?? $existing->getUsername();
            $tipoUser = $data['tipo_user'] ?? $data['type'] ?? $existing->getType();
            $imagen   = $data['imagen']   ?? $data['image'] ?? $existing->getImage();
            $db       = Database::getInstance();

            if (!empty($data['password'])) {
                // New password provided — let UserRepository hash it
                $user  = new User($userId, $username, $data['password'], $tipoUser, $imagen);
                $saved = $this->userRepo->save($user);
            } else {
                // No password change — update directly to avoid double-hashing
                $stmt = $db->prepare("UPDATE users SET username=:u, type=:t, image=:i WHERE id=:id");
                $stmt->execute([':u' => $username, ':t' => $tipoUser, ':i' => $imagen, ':id' => $userId]);
                $saved = $this->userRepo->findById($userId);
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

            $tipoUser = $data['tipo_user'] ?? $data['type'] ?? 'ESTUDIANTE';
            $imagen   = $data['imagen']    ?? $data['image'] ?? null;

            $user  = new User(null, $username, $data['password'], $tipoUser, $imagen);
            $saved = $this->userRepo->save($user);
        }

        return [
            'id'        => (int)$saved->getId(),
            'username'  => $saved->getUsername(),
            'tipo_user' => $saved->getType(),
        ];
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

        // The users table (id, username, password, type, image) has no estado column.
        // Disable by setting type to 'INACTIVO' as a status marker.
        $db   = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET type = 'INACTIVO' WHERE id = :id");
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'id'      => $id,
            'message' => 'Usuario desactivado correctamente.',
        ];
    }
}
