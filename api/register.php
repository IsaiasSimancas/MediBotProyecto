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
if (!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['confirmPassword'])) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}

$nombre = trim($data['name']);
$email = trim($data['email']);
$password = $data['password'];
$confirmPassword = $data['confirmPassword'];

// Validaciones adicionales
if (empty($nombre) || empty($email) || empty($password) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido']);
    exit;
}

try {
    // Verificar si el correo ya está registrado
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Este correo electrónico ya está registrado']);
        exit;
    }
    
    // Encriptar contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'usuario')");
    $result = $stmt->execute([$nombre, $email, $hashedPassword]);
    
    if ($result) {
        // Obtener el ID del usuario recién creado
        $userId = $conn->lastInsertId();
        
        // Iniciar sesión
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'usuario';
        
        echo json_encode([
            'success' => true, 
            'message' => 'Registro exitoso', 
            'user' => [
                'id' => $userId,
                'name' => $nombre,
                'email' => $email,
                'role' => 'usuario'
            ],
            'redirect' => 'user/index.php' // Añadido: ruta específica para redirección
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>
