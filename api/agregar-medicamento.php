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

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/medicamentos.php");
    exit;
}

// Obtener datos del formulario
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
$categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
$stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
$dosificacion = isset($_POST['dosificacion']) ? trim($_POST['dosificacion']) : '';
$principio_activo = isset($_POST['principio_activo']) ? trim($_POST['principio_activo']) : '';
$presentacion = isset($_POST['presentacion']) ? trim($_POST['presentacion']) : '';
$laboratorio = isset($_POST['laboratorio']) ? trim($_POST['laboratorio']) : '';

// Si se seleccionó "otra" categoría, usar el valor especificado
if ($categoria === 'otra' && isset($_POST['otra_categoria']) && !empty($_POST['otra_categoria'])) {
    $categoria = trim($_POST['otra_categoria']);
}

// Validar datos requeridos
if (empty($nombre) || empty($descripcion) || $precio <= 0 || empty($categoria) || $stock < 0) {
    header("Location: ../admin/agregar-medicamento.php?error=Todos los campos marcados con * son obligatorios");
    exit;
}

// Procesar la imagen si se ha subido
$imagen_path = '';
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['imagen']['tmp_name'];
    $file_name = $_FILES['imagen']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validar extensión
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_ext, $allowed_ext)) {
        header("Location: ../admin/agregar-medicamento.php?error=Formato de imagen no válido. Se permiten: jpg, jpeg, png, gif");
        exit;
    }
    
    // Crear directorio si no existe
    $upload_dir = '../uploads/medicamentos/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generar nombre único para la imagen
    $new_file_name = uniqid() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_file_name;
    
    // Mover archivo
    if (move_uploaded_file($file_tmp, $upload_path)) {
        $imagen_path = 'uploads/medicamentos/' . $new_file_name;
    } else {
        header("Location: ../admin/agregar-medicamento.php?error=Error al subir la imagen. Inténtelo de nuevo.");
        exit;
    }
} else {
    // Si no se subió imagen, usar una por defecto
    $imagen_path = 'img/placeholder.jpg';
}

try {
    // Insertar medicamento en la base de datos
    $stmt = $conn->prepare("INSERT INTO medicamentos (nombre, descripcion, precio, imagen, categoria, stock, dosificacion, principio_activo, presentacion, laboratorio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $result = $stmt->execute([
        $nombre,
        $descripcion,
        $precio,
        $imagen_path,
        $categoria,
        $stock,
        $dosificacion,
        $principio_activo,
        $presentacion,
        $laboratorio
    ]);
    
    if ($result) {
        // Redirigir con mensaje de éxito
        header("Location: ../admin/agregar-medicamento.php?success=1");
    } else {
        // Redirigir con mensaje de error
        header("Location: ../admin/agregar-medicamento.php?error=Error al guardar el medicamento");
    }
    
} catch(PDOException $e) {
    // Redirigir con mensaje de error
    header("Location: ../admin/agregar-medicamento.php?error=Error en la base de datos: " . $e->getMessage());
}
?>
