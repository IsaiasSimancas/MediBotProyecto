<?php
// Incluir archivo de conexión
require_once '../config/database.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

try {
    // Obtener parámetros de filtro
    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
    $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : null;
    $ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'nombre';
    $orden = isset($_GET['orden']) ? $_GET['orden'] : 'ASC';
    
    // Construir la consulta SQL base
    $sql = "SELECT * FROM medicamentos WHERE 1=1";
    
    // Añadir filtros si existen
    if ($categoria && $categoria !== 'todos') {
        $sql .= " AND categoria = :categoria";
    }
    
    if ($busqueda) {
        $sql .= " AND (nombre LIKE :busqueda OR descripcion LIKE :busqueda)";
    }
    
    // Añadir ordenamiento
    $sql .= " ORDER BY " . $ordenar . " " . $orden;
    
    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);
    
    // Vincular parámetros si existen
    if ($categoria && $categoria !== 'todos') {
        $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
    }
    
    if ($busqueda) {
        $busquedaParam = "%$busqueda%";
        $stmt->bindParam(':busqueda', $busquedaParam, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    
    // Obtener resultados
    $medicamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Añadir URLs de imágenes completas
    foreach ($medicamentos as &$medicamento) {
        // Verificar si existe la imagen
        if (!empty($medicamento['imagen'])) {
            $rutaRelativa = '../uploads/medicamentos/' . $medicamento['imagen'];
            $rutaAbsoluta = '/uploads/medicamentos/' . $medicamento['imagen'];
            
            // Verificar si el archivo existe
            if (file_exists($rutaRelativa)) {
                $medicamento['imagen_url'] = $rutaAbsoluta;
            } else {
                $medicamento['imagen_url'] = '/img/placeholder.jpg';
            }
        } else {
            $medicamento['imagen_url'] = '/img/placeholder.jpg';
        }
    }
    
    // Devolver resultados como JSON
    echo json_encode([
        'success' => true, 
        'total' => count($medicamentos),
        'products' => $medicamentos
    ]);
    
} catch(PDOException $e) {
    // Devolver error
    echo json_encode([
        'success' => false, 
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}
?>
