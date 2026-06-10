<?php

namespace Services\Traits;

require_once __DIR__ . '/../../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../../persistence/repository/PersonaInstitucionRepository.php';

use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use RuntimeException;

trait TAuthenticable
{
    protected function getInstitucionByUserId(int $userId): int
    {
        // Cachea el resultado por ID de usuario para evitar consultas redundantes en la misma petición.
        static $institucionId = [];
        if (isset($institucionId[$userId])) {
            return $institucionId[$userId];
        }

        $persona = (new PersonaRepository())->findByUserId($userId);
        if ($persona === null) {
            throw new RuntimeException('No se encontró persona asociada al usuario.', 404);
        }

        $pi = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());
        if ($pi === null || $pi->getIdInstitucion() === null || $pi->getIdInstitucion()->getId() === null) {
            throw new RuntimeException('No se encontró institución válida asociada al usuario.', 404);
        }

        return $institucionId[$userId] = (int) $pi->getIdInstitucion()->getId();
    }
}