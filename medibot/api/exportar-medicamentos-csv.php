<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado y es superusuario o administrador
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] != 'superusuario' && $_SESSION['user_role'] != 'administrador')) {
    // Redirigir a la página de inicio de sesión
    header("Location: ../index.html");
    exit;
}

// Incluir archivo de conexión
require_once '../config/database.php';

try {
    // Obtener todos los medicamentos
    $stmt = $conn->query("SELECT * FROM medicamentos ORDER BY nombre ASC");
    $medicamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar cabeceras para descargar CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=medicamentos_' . date('Ymd_His') . '.csv');
    
    // Crear el archivo CSV
    $output = fopen('php://output', 'w');
    
    // Establecer el delimitador de campo y el encapsulador
    $delimiter = ',';
    $enclosure = '"';
    
    // Escribir la línea de encabezado UTF-8 BOM para Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Escribir los encabezados
    fputcsv($output, [
        'ID', 
        'Nombre', 
        'Categoría', 
        'Precio', 
        'Stock', 
        'Descripción', 
        'Principio Activo', 
        'Presentación', 
        'Laboratorio', 
        'Dosificación', 
        'Imagen'
    ], $delimiter, $enclosure);
    
    // Escribir los datos
    foreach ($medicamentos as $med) {
        fputcsv($output, [
            $med['id'],
            $med['nombre'],
            $med['categoria'],
            $med['precio'],
            $med['stock'],
            $med['descripcion'],
            $med['principio_activo'],
            $med['presentacion'],
            $med['laboratorio'],
            $med['dosificacion'],
            $med['imagen']
        ], $delimiter, $enclosure);
    }
    
    fclose($output);
    
} catch(PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
    echo "<br><br><a href='../admin/medicamentos.php'>Volver a Medicamentos</a>";
}
?>
