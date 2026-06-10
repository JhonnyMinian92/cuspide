<?php
require 'app/implements/persistence/conexion/Database.php';
$db = Core\Database::getInstance();
$stmt = $db->query("SHOW TRIGGERS");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
