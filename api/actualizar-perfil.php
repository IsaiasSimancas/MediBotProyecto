<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Redirigir a la página de inicio de sesión
    header("Location: ../index.html");
    exit;
}

// Incluir archivo de conexión
require_once '../config/database.php';

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/perfil.php");
    exit;
}

// Obtener la acción a realizar
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Procesar según la acción
switch ($action) {
    case 'update_info':
        updateInfo();
        break;
    case 'update_password':
        updatePassword();
        break;
    case 'update_role':
        updateRole();
        break;
    default:
        header("Location: ../admin/perfil.php?error=Acción no válida");
        exit;
}

// Función para actualizar información personal
function updateInfo() {
    global $conn;
    
    // Obtener datos del formulario
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Validar datos
    if (empty($nombre) || empty($email)) {
        header("Location: ../admin/perfil.php?error=Todos los campos son obligatorios");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../admin/perfil.php?error=El correo electrónico no es válido");
        exit;
    }
    
    try {
        // Verificar si el correo ya está en uso por otro usuario
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: ../admin/perfil.php?error=El correo electrónico ya está en uso por otro usuario");
            exit;
        }
        
        // Actualizar información del usuario
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
        $result = $stmt->execute([$nombre, $email, $_SESSION['user_id']]);
        
        if ($result) {
            // Actualizar datos de sesión
            $_SESSION['user_name'] = $nombre;
            $_SESSION['user_email'] = $email;
            
            header("Location: ../admin/perfil.php?success=1");
        } else {
            header("Location: ../admin/perfil.php?error=Error al actualizar la información");
        }
        
    } catch(PDOException $e) {
        header("Location: ../admin/perfil.php?error=Error en la base de datos: " . $e->getMessage());
    }
}

// Función para actualizar contraseña
function updatePassword() {
    global $conn;
    
    // Obtener datos del formulario
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validar datos
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: ../admin/perfil.php?error=Todos los campos son obligatorios");
        exit;
    }
    
    if ($new_password !== $confirm_password) {
        header("Location: ../admin/perfil.php?error=Las contraseñas no coinciden");
        exit;
    }
    
    if (strlen($new_password) < 6) {
        header("Location: ../admin/perfil.php?error=La contraseña debe tener al menos 6 caracteres");
        exit;
    }
    
    try {
        // Verificar contraseña actual
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($current_password, $usuario['password'])) {
            header("Location: ../admin/perfil.php?error=La contraseña actual es incorrecta");
            exit;
        }
        
        // Encriptar nueva contraseña
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Actualizar contraseña
        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $result = $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        
        if ($result) {
            header("Location: ../admin/perfil.php?success=1");
        } else {
            header("Location: ../admin/perfil.php?error=Error al actualizar la contraseña");
        }
        
    } catch(PDOException $e) {
        header("Location: ../admin/perfil.php?error=Error en la base de datos: " . $e->getMessage());
    }
}

// Función para actualizar rol (solo para superusuarios)
function updateRole() {
    global $conn;
    
    // Verificar si el usuario es superusuario
    if ($_SESSION['user_role'] !== 'superusuario') {
        header("Location: ../admin/perfil.php?error=No tienes permisos para cambiar roles");
        exit;
    }
    
    // Obtener datos del formulario
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validar datos
    if (empty($role) || empty($password)) {
        header("Location: ../admin/perfil.php?error=Todos los campos son obligatorios");
        exit;
    }
    
    // Validar rol
    $roles_validos = ['usuario', 'administrador', 'superusuario'];
    if (!in_array($role, $roles_validos)) {
        header("Location: ../admin/perfil.php?error=Rol no válido");
        exit;
    }
    
    try {
        // Verificar contraseña
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($password, $usuario['password'])) {
            header("Location: ../admin/perfil.php?error=La contraseña es incorrecta");
            exit;
        }
        
        // Actualizar rol
        $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        $result = $stmt->execute([$role, $_SESSION['user_id']]);
        
        if ($result) {
            // Actualizar rol en la sesión
            $_SESSION['user_role'] = $role;
            
            header("Location: ../admin/perfil.php?success=1");
        } else {
            header("Location: ../admin/perfil.php?error=Error al actualizar el rol");
        }
        
    } catch(PDOException $e) {
        header("Location: ../admin/perfil.php?error=Error en la base de datos: " . $e->getMessage());
    }
}
?>
