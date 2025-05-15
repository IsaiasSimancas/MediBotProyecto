<?php
// Iniciar sesión
session_start();

// Destruir la sesión
session_unset();
session_destroy();

// Eliminar cookie de "recordarme" si existe
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirigir a la página de inicio
header("Location: ../index.html");
exit;
?>
