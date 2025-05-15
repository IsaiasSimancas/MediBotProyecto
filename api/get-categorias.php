<?php
// Incluir archivo de conexión a la base de datos
require_once '../config/database.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

try {
    // Usar la conexión PDO existente
    // Asumimos que $conn ya está disponible desde database.php
    
    // Consulta para obtener categorías únicas
    $sql = "SELECT DISTINCT categoria as nombre FROM medicamentos ORDER BY categoria";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Obtener resultados
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolver resultados en formato JSON
    echo json_encode([
        'success' => true,
        'categorias' => $categorias
    ]);
    
} catch (PDOException $e) {
    // En caso de error
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener categorías: ' . $e->getMessage()
    ]);
}
?>
