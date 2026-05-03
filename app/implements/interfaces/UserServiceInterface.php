<?php

declare(strict_types=1);

namespace Interfaces;

interface UserServiceInterface
{
    public function processRequest(string $requestUri, array $queryParams, string $authorizationHeader): array;
}
