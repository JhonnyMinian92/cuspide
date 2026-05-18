<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/UsuarioServiceInterface.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/UserRepository.php';
require_once __DIR__ . '/../persistence/entitys/User.php';
require_once __DIR__ . '/../persistence/repository/BotonRepository.php';
require_once __DIR__ . '/../persistence/entitys/Boton.php';
require_once __DIR__ . '/../persistence/repository/BotonUserRepository.php';
require_once __DIR__ . '/../persistence/entitys/BotonUser.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\UserRepository;
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
    private BotonRepository $botonRepo;
    private BotonUserRepository $botonUserRepo;
    private array $security;

    public function __construct()
    {
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
        $tipoUser    = $queryParams['tipo_user']  ?? '';
        $institucion = $queryParams['institucion'] ?? '';

        if ($tipoUser === '') {
            throw new \RuntimeException('El parámetro tipo_user es requerido.', 400);
        }

        if ($institucion !== '') {
            $botonUsers = $this->botonUserRepo->findByTypeUserAndInstitucion($tipoUser, (int)$institucion);
        } else {
            $botonUsers = $this->botonUserRepo->findByTypeUser($tipoUser);
        }

        $result = [];
        foreach ($botonUsers as $bu) {
            $boton = $bu->getBoton();
            if ($boton === null) {
                continue;
            }
            $result[] = [
                'id'     => $boton->getId(),
                'nombre' => $boton->getNombre(),
                'icono'  => $boton->getIcono(),
                'tipo'   => $boton->getTipo(),
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

        foreach (['nombre', 'tipo_user'] as $field) {
            if (empty($data[$field])) {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $nombre   = trim($data['nombre']);
        $tipoUser = $data['tipo_user'];

        $boton  = new Boton(
            null,
            $data['orden'] ?? null,
            $nombre,
            $data['icono'] ?? '',
            $data['tipo']  ?? $tipoUser,
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

        $boton = $this->botonRepo->findById($id);
        if ($boton === null) {
            throw new \RuntimeException('Botón no encontrado.', 404);
        }

        $boton->setEstado($estado);
        $this->botonRepo->save($boton);

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

        // The users table has no estado column — disable by setting type = 'INACTIVO'
        $this->userRepo->disableById($id);

        return [
            'id'      => $id,
            'message' => 'Usuario desactivado correctamente.',
        ];
    }
}
