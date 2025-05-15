<?php
// Incluir archivo de conexión
require_once '../config/database.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de producto no proporcionado']);
    exit;
}

$id = intval($_GET['id']);

try {
    // Obtener el medicamento por ID
    $stmt = $conn->prepare("SELECT * FROM medicamentos WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }
    
    $medicamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'product' => $medicamento]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>
