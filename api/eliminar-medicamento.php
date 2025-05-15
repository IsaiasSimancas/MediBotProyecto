<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado y es superusuario o administrador
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] != 'superusuario' && $_SESSION['user_role'] != 'administrador')) {
    // Redirigir a la página de inicio de sesión
    header("Location: ../index.html");
    exit;
}

// Incluir archivo de conexión
require_once '../config/database.php';

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../admin/medicamentos.php?error=ID no proporcionado");
    exit;
}

$id = intval($_GET['id']);

try {
    // Obtener la imagen del medicamento
    $stmt = $conn->prepare("SELECT imagen FROM medicamentos WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() == 0) {
        header("Location: ../admin/medicamentos.php?error=Medicamento no encontrado");
        exit;
    }
    
    $medicamento = $stmt->fetch(PDO::FETCH_ASSOC);
    $imagen = $medicamento['imagen'];
    
    // Eliminar el medicamento
    $stmt = $conn->prepare("DELETE FROM medicamentos WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        // Eliminar la imagen si no es la imagen por defecto
        if ($imagen != 'img/placeholder.jpg' && file_exists('../' . $imagen)) {
            unlink('../' . $imagen);
        }
        
        // Redirigir con mensaje de éxito
        header("Location: ../admin/medicamentos.php?success=1");
    } else {
        // Redirigir con mensaje de error
        header("Location: ../admin/medicamentos.php?error=Error al eliminar el medicamento");
    }
    
} catch(PDOException $e) {
    // Redirigir con mensaje de error
    header("Location: ../admin/medicamentos.php?error=Error en la base de datos: " . $e->getMessage());
}
?>
