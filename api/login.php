<?php
// Incluir archivo de conexión
require_once '../config/database.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del formulario
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];
$remember = isset($data['remember']) ? $data['remember'] : false;

// Validaciones adicionales
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}

try {
    // Buscar usuario por email
    $stmt = $conn->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        exit;
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar contraseña
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        exit;
    }
    
    // Iniciar sesión
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nombre'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['rol'];
    
    // Si se seleccionó "recordarme", crear una cookie
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        
        // Guardar token en la base de datos (esto requeriría una tabla adicional o un campo en la tabla usuarios)
        // Por simplicidad, no implementamos esto completamente aquí
        
        // Crear cookie que dura 30 días
        setcookie('remember_token', $token, time() + (86400 * 30), "/");
    }
    
    // Determinar la redirección según el rol del usuario
    $redirect = '';
    if ($user['rol'] === 'superusuario' || $user['rol'] === 'administrador') {
        $redirect = 'admin/dashboard.php';
    } else {
        $redirect = 'user/dashboard.php';
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Inicio de sesión exitoso', 
        'user' => [
            'id' => $user['id'],
            'name' => $user['nombre'],
            'email' => $user['email'],
            'role' => $user['rol']
        ],
        'redirect' => $redirect
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>
