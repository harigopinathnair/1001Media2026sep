<?php
// Check if running on local server environment (WampServer/Localhost/VHost)
$is_local = false;
if (
    !isset($_SERVER['SERVER_ADDR']) || 
    $_SERVER['SERVER_ADDR'] === '127.0.0.1' || 
    $_SERVER['SERVER_ADDR'] === '::1' || 
    (isset($_SERVER['HTTP_HOST']) && (
        strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
        strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
        strpos($_SERVER['HTTP_HOST'], '1001mediame') !== false
    ))
) {
    $is_local = true;
}

$charset = 'utf8mb4';

if ($is_local) {
    // Local configuration
    $db_host = 'localhost';
    $db_name = '1001mediame';
    $db_user = 'root';
    $db_pass = '';
} else {
    // Server configuration
    $db_host = 'localhost';
    $db_name = 'nqatsxqe_1001med';
    $db_user = 'nqatsxqe_1001med';
    $db_pass = 'pDv76!S1)g@#';
}

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";
     $pdo = new PDO($dsn, $db_user, $db_pass, $options);

     // Auto-Migration/Self-Healing: Import schema.sql if tables are missing
     $tableExists = false;
     try {
         $pdo->query("SELECT 1 FROM `posts` LIMIT 1");
         $tableExists = true;
     } catch (\PDOException $ex) {
         $tableExists = false;
     }

     if (!$tableExists) {
         $schema_path = dirname(__DIR__) . '/schema.sql';
         if (file_exists($schema_path)) {
             $sql = file_get_contents($schema_path);
             // Strip out local database creation queries to run safely inside the target DB
             $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/is', '', $sql);
             $sql = preg_replace('/USE `.*?`;/is', '', $sql);
             $pdo->exec($sql);
         }
     }
} catch (\PDOException $e) {
     // If server credentials failed, try local fallback automatically
     if (!$is_local) {
         try {
             $dsn = "mysql:host=localhost;dbname=1001mediame;charset=$charset";
             $pdo = new PDO($dsn, 'root', '', $options);
         } catch (\PDOException $fallback_ex) {
             throw new \PDOException($e->getMessage(), (int)$e->getCode());
         }
     } else {
         throw new \PDOException($e->getMessage(), (int)$e->getCode());
     }
}
?>
