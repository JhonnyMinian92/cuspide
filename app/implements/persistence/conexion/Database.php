<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
  private static ?PDO $instance = null;

  private function __construct() {}

  public static function getInstance(): PDO
  {
    if (self::$instance === null) {
      try {
        self::$instance = new PDO(
          "mysql:host=localhost;dbname=cuspide;charset=utf8mb4",
          "root",
          "root",
          [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          ]
        );
      } catch (PDOException $e) {
        throw new \Exception("Error de conexión DB: " . $e->getMessage());
      }
    }

    return self::$instance;
  }
}
