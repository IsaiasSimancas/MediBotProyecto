<?php
// Incluir archivo de conexión a la base de datos
require_once '../config/database.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Parámetros de paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
$offset = ($page - 1) * $limit;

// Parámetros de filtrado
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

try {
    // Usar la conexión PDO existente
    // Asumimos que $conn ya está disponible desde database.php
    
    // Construir la consulta base
    $sql = "SELECT id, nombre, descripcion, precio, categoria, imagen, stock FROM medicamentos WHERE 1=1";
    $countSql = "SELECT COUNT(*) as total FROM medicamentos WHERE 1=1";
    $params = [];
    
    // Agregar filtros si existen
    if (!empty($categoria)) {
        $sql .= " AND categoria = :categoria";
        $countSql .= " AND categoria = :categoria";
        $params[':categoria'] = $categoria;
    }
    
    if (!empty($busqueda)) {
        $sql .= " AND (nombre LIKE :busqueda OR descripcion LIKE :busqueda)";
        $countSql .= " AND (nombre LIKE :busqueda OR descripcion LIKE :busqueda)";
        $params[':busqueda'] = "%$busqueda%";
    }
    
    // Consulta para contar total de registros
    $countStmt = $conn->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    // Calcular total de páginas
    $totalPages = ceil($total / $limit);
    
    // Agregar ordenamiento y límites a la consulta principal
    // IMPORTANTE: Incluir los valores de LIMIT directamente en la consulta
    // en lugar de usar parámetros para evitar problemas de sintaxis
    $sql .= " ORDER BY nombre ASC LIMIT $offset, $limit";
    
    // Ejecutar consulta principal
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    // Obtener resultados
    $medicamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolver resultados en formato JSON
    echo json_encode([
        'success' => true,
        'medicamentos' => $medicamentos,
        'total' => $total,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ]);
    
} catch (PDOException $e) {
    // En caso de error
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener medicamentos: ' . $e->getMessage()
    ]);
}
?>
