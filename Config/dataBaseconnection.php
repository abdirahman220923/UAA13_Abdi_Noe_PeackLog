<?php
// Renseigner les identifiants de ta connexion MySQL Workbench.
$host = "192.168.68.61";
$port = "3306";
$database = "PeackLog";
$user = "Noe";
$password = "root";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;port=$port;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    $pdo->exec("SET time_zone = '+00:00'");
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("La connexion a la base de donnees a echoue. Verifie Config/dataBaseconnection.php.");
}