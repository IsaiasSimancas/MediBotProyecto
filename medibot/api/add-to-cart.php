<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de conexión a la base de datos
require_once '../config/database.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Verificar si el usuario está logueado
$logueado = false;
$usuario_id = 0;

if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
    $logueado = true;
    $usuario_id = $_SESSION['usuario_id'];
} elseif (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $logueado = true;
    $usuario_id = $_SESSION['user_id'];
}

if (!$logueado) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no logueado'
    ]);
    exit;
}

// Obtener datos del POST (JSON)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verificar datos recibidos
if (!isset($data['producto_id']) || !isset($data['cantidad'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit;
}

$producto_id = (int)$data['producto_id'];
$cantidad = (int)$data['cantidad'];

// Validar datos
if ($producto_id <= 0 || $cantidad <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos'
    ]);
    exit;
}

try {
    // Usar la conexión PDO existente
    // Asumimos que $conn ya está disponible desde database.php
    
    // Verificar si el producto existe y tiene stock suficiente
    $sql = "SELECT id, stock FROM medicamentos WHERE id = :producto_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':producto_id', $producto_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Producto no encontrado'
        ]);
        exit;
    }
    
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($producto['stock'] < $cantidad) {
        echo json_encode([
            'success' => false,
            'message' => 'Stock insuficiente'
        ]);
        exit;
    }
    
    // Iniciar transacción
    $conn->beginTransaction();
    
    // Verificar si el usuario tiene un carrito activo
    $sql = "SELECT id FROM carritos WHERE usuario_id = :usuario_id AND estado = 'activo'";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $carrito_id = 0;
    
    if ($stmt->rowCount() > 0) {
        // El usuario ya tiene un carrito activo
        $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
        $carrito_id = $carrito['id'];
    } else {
        // Crear un nuevo carrito para el usuario
        $sql = "INSERT INTO carritos (usuario_id, fecha_creacion, estado) VALUES (:usuario_id, NOW(), 'activo')";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $carrito_id = $conn->lastInsertId();
    }
    
    // Verificar si el producto ya está en el carrito
    $sql = "SELECT id, cantidad FROM carrito_items WHERE carrito_id = :carrito_id AND producto_id = :producto_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':carrito_id', $carrito_id, PDO::PARAM_INT);
    $stmt->bindValue(':producto_id', $producto_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // El producto ya está en el carrito, actualizar cantidad
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        $nueva_cantidad = $item['cantidad'] + $cantidad;
        
        // Verificar que la nueva cantidad no exceda el stock
        if ($nueva_cantidad > $producto['stock']) {
            $nueva_cantidad = $producto['stock'];
        }
        
        $sql = "UPDATE carrito_items SET cantidad = :cantidad WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':cantidad', $nueva_cantidad, PDO::PARAM_INT);
        $stmt->bindValue(':id', $item['id'], PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // El producto no está en el carrito, agregarlo
        $sql = "INSERT INTO carrito_items (carrito_id, producto_id, cantidad, fecha_agregado) 
                VALUES (:carrito_id, :producto_id, :cantidad, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':carrito_id', $carrito_id, PDO::PARAM_INT);
        $stmt->bindValue(':producto_id', $producto_id, PDO::PARAM_INT);
        $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    // Confirmar transacción
    $conn->commit();
    
    // Obtener cantidad total de items en el carrito
    $sql = "SELECT SUM(cantidad) as total FROM carrito_items WHERE carrito_id = :carrito_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':carrito_id', $carrito_id, PDO::PARAM_INT);
    $stmt->execute();
    $total = $stmt->fetchColumn();
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Producto agregado al carrito',
        'cartCount' => $total
    ]);
    
} catch (PDOException $e) {
    // En caso de error, revertir transacción
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al agregar al carrito: ' . $e->getMessage()
    ]);
}
?>
