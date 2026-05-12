<?php

declare(strict_types=1);

namespace Interfaces;

interface ButtomServiceInterface
{
  public function processRequest(string $requestUri, array $queryParams, string $authorizationHeader): array;
}