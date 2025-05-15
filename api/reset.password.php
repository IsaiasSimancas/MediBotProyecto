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
if (!isset($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico es requerido']);
    exit;
}

$email = trim($data['email']);

// Validaciones adicionales
if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico es requerido']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido']);
    exit;
}

try {
    // Verificar si el correo existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() == 0) {
        // Por seguridad, no informamos al usuario que el correo no existe
        echo json_encode(['success' => true, 'message' => 'Si el correo existe, recibirás un enlace para restablecer tu contraseña']);
        exit;
    }
    
    // Generar token único
    $token = bin2hex(random_bytes(32));
    
    // Establecer fecha de expiración (24 horas)
    $expiry = date('Y-m-d H:i:s', time() + 86400);
    
    // Guardar token en la base de datos
    $stmt = $conn->prepare("UPDATE usuarios SET token_recuperacion = ?, token_expiracion = ? WHERE email = ?");
    $result = $stmt->execute([$token, $expiry, $email]);
    
    if ($result) {
        // En un entorno real, aquí enviaríamos un correo electrónico con el enlace de recuperación
        // Por simplicidad, solo simulamos que se envió el correo
        
        // El enlace sería algo como: https://tudominio.com/reset-password-confirm.html?token=TOKEN
        
        echo json_encode(['success' => true, 'message' => 'Se ha enviado un enlace de recuperación a tu correo electrónico']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>
