<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/ContratoRepository.php';
require_once __DIR__ . '/../persistence/repository/NivelAcademicoRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../../controllers/request/ContratoRequest.php';
require_once __DIR__ . '/../utils/Token.php';
require_once __DIR__ . '/../persistence/entitys/Contrato.php';

use Repositories\ContratoRepository;
use Repositories\NivelAcademicoRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Request\ContratoRequest;
use Entitys\Contrato;
use RuntimeException;
use Utils\Token;

final class ContratoServices
{
    private string $jwtSecret;
    private ContratoRepository $contratoRepo;
    private NivelAcademicoRepository $nivelAcademicoRepo;

    public function __construct()
    {
        $config = require __DIR__ . '/../../properties/security.php';
        $this->jwtSecret = (string)($config['jwt_secret'] ?? '');
        if ($this->jwtSecret === '') {
            throw new RuntimeException('No existe configuración de seguridad para JWT.', 500);
        }
        $this->contratoRepo = new ContratoRepository();
        $this->nivelAcademicoRepo = new NivelAcademicoRepository();
    }

    public function processRequest(string $method, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->jwtSecret);
        $userId  = (int)($payload['sub'] ?? 0);
        $instId  = $this->getInstitucionByUserId($userId);

        $resource = strtolower(trim((string)($queryParams['api'] ?? '')));
        $id = isset($queryParams['id']) ? (int)$queryParams['id'] : null;

        return match (true) {
            $method === 'GET' && $resource === 'list' => $this->fetchContratos($instId, $queryParams),
            $method === 'GET' && $resource === 'niveles' => $this->fetchNivelesAcademicos(),
            $method === 'POST' && $resource === 'save' => $this->saveContrato($instId, null),
            $method === 'PUT' && $resource === 'save' && $id !== null => $this->saveContrato($instId, $id),
            default => throw new RuntimeException('Ruta de API no soportada: ' . $method . ' ' . $resource, 404),
        };
    }

    private function fetchContratos(int $instId, array $queryParams): array
    {
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 50;
        return $this->contratoRepo->findByInstitucion($instId, $limit);
    }

    private function fetchNivelesAcademicos(): array
    {
        $niveles = $this->nivelAcademicoRepo->findAll();
        $result = [];
        foreach ($niveles as $nivel) {
            $result[] = [
                'id' => $nivel->getId(),
                'nombre' => $nivel->getNombre(),
                'nemonico' => $nivel->getNemonico(),
            ];
        }
        return $result;
    }

    private function saveContrato(int $instId, ?int $contratoId): array
    {
        $req = ContratoRequest::fromJson();
        if ($req->institucion !== $instId) {
            throw new RuntimeException('La institución de la solicitud no coincide con la del usuario.', 403);
        }

        $contrato = new Contrato(
            $contratoId,
            $req->fecha_inscripcion,
            $req->fecha_inicio,
            $req->fecha_fin,
            $req->nivel,
            $req->institucion,
            $req->persona_id
        );

        $saved = $this->contratoRepo->save($contrato);
        return ['id' => $saved->getId()];
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
