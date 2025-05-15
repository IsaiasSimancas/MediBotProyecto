<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Verificar si el usuario está logueado
// Comprobamos tanto usuario_id como user_id para mayor compatibilidad
$logueado = false;
if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
    $logueado = true;
    $usuario_id = $_SESSION['usuario_id'];
} elseif (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $logueado = true;
    $usuario_id = $_SESSION['user_id'];
}

// Devolver estado de la sesión
echo json_encode([
    'logueado' => $logueado,
    'usuario_id' => $logueado ? $usuario_id : null
]);
?>
