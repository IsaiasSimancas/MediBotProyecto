<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Redirigir a la página de inicio de sesión
    header("Location: ../index.html");
    exit;
}

// Verificar si el usuario tiene el rol correcto (usuario)
if ($_SESSION['user_role'] !== 'usuario') {
    // Redirigir según el rol
    if ($_SESSION['user_role'] === 'administrador' || $_SESSION['user_role'] === 'superusuario') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../index.html");
    }
    exit;
}

// Verificar si se proporciona un ID de medicamento
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: medicamentos.php");
    exit;
}

$medicamento_id = $_GET['id'];

// Incluir archivo de conexión
require_once '../config/database.php';

// Obtener datos del usuario
try {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// Obtener items en el carrito del usuario
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM carrito WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $items_carrito = $stmt->fetchColumn();
} catch(PDOException $e) {
    $items_carrito = 0;
}

// Obtener detalles del medicamento
try {
    $stmt = $conn->prepare("SELECT * FROM medicamentos WHERE id = ?");
    $stmt->execute([$medicamento_id]);
    
    if ($stmt->rowCount() === 0) {
        // Si no se encuentra el medicamento, redirigir
        header("Location: medicamentos.php");
        exit;
    }
    
    $medicamento = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// Obtener medicamentos relacionados (misma categoría)
try {
    $stmt = $conn->prepare("SELECT * FROM medicamentos WHERE categoria = ? AND id != ? LIMIT 4");
    $stmt->execute([$medicamento['categoria'], $medicamento_id]);
    $medicamentos_relacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $medicamentos_relacionados = [];
}
?>

<!DOCTYPE html
