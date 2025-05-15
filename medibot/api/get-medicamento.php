<?php
// Incluir archivo de conexión a la base de datos
require_once '../config/database.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Obtener ID del medicamento
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de medicamento no válido'
    ]);
    exit;
}

try {
    // Usar la conexión PDO existente
    // Asumimos que $conn ya está disponible desde database.php
    
    // Consulta para obtener el medicamento
    $sql = "SELECT id, nombre, descripcion, precio, categoria, imagen, stock FROM medicamentos WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Verificar si se encontró el medicamento
    if ($stmt->rowCount() > 0) {
        $medicamento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'medicamento' => $medicamento
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Medicamento no encontrado'
        ]);
    }
    
} catch (PDOException $e) {
    // En caso de error
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener medicamento: ' . $e->getMessage()
    ]);
}
?>
