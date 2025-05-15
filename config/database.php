<?php
// Configuración de la base de datos
$host = "localhost";
$dbname = "database_medibot";
$username = "root";
$password = "";

try {
    // Intentar conectar a la base de datos
    $conn = new PDO("mysql:host=$host", $username, $password);
    
    // Configurar el modo de error de PDO a excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si la base de datos existe, si no, crearla
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    $conn->exec($sql);
    
    // Seleccionar la base de datos
    $conn->exec("USE $dbname");
    
    // Configurar el conjunto de caracteres a utf8
    $conn->exec("SET NAMES utf8");
    
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
