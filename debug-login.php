<?php
// Este script ayuda a depurar problemas de inicio de sesión

// Incluir archivo de conexión
require_once 'config/database.php';

// Configurar cabeceras para una mejor visualización
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Depuración de Inicio de Sesión</h1>";

// Función para probar el inicio de sesión
function testLogin($email, $password) {
    global $conn;
    
    echo "<h2>Probando inicio de sesión con:</h2>";
    echo "<p>Email: $email</p>";
    echo "<p>Contraseña: $password</p>";
    
    try {
        // Buscar usuario por email
        $stmt = $conn->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() == 0) {
            echo "<p style='color:red'>Error: Usuario no encontrado</p>";
            return;
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Usuario encontrado:</p>";
        echo "<ul>";
        echo "<li>ID: {$user['id']}</li>";
        echo "<li>Nombre: {$user['nombre']}</li>";
        echo "<li>Email: {$user['email']}</li>";
        echo "<li>Rol: {$user['rol']}</li>";
        echo "</ul>";
        
        // Verificar contraseña
        if (password_verify($password, $user['password'])) {
            echo "<p style='color:green'>Contraseña correcta</p>";
            echo "<p>El inicio de sesión debería funcionar correctamente.</p>";
        } else {
            echo "<p style='color:red'>Error: Contraseña incorrecta</p>";
            echo "<p>Hash almacenado: {$user['password']}</p>";
            
            // Crear un nuevo hash para la contraseña
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            echo "<p>Nuevo hash para '$password': $newHash</p>";
            
            echo "<p>¿Desea actualizar la contraseña? <a href='?update=1&email=$email&password=$password'>Actualizar ahora</a></p>";
        }
        
    } catch(PDOException $e) {
        echo "<p style='color:red'>Error de base de datos: " . $e->getMessage() . "</p>";
    }
}

// Actualizar contraseña si se solicita
if (isset($_GET['update']) && $_GET['update'] == 1 && isset($_GET['email']) && isset($_GET['password'])) {
    $email = $_GET['email'];
    $password = $_GET['password'];
    
    try {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
        $result = $stmt->execute([$newHash, $email]);
        
        if ($result) {
            echo "<p style='color:green'>Contraseña actualizada correctamente.</p>";
        } else {
            echo "<p style='color:red'>Error al actualizar la contraseña.</p>";
        }
    } catch(PDOException $e) {
        echo "<p style='color:red'>Error de base de datos: " . $e->getMessage() . "</p>";
    }
}

// Probar con las credenciales de administrador
testLogin('admin@farmaexpress.com', 'admin123');

echo "<hr>";
echo "<h2>Información adicional:</h2>";

// Verificar la tabla usuarios
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "<p>Tabla 'usuarios': <strong style='color:green'>OK</strong></p>";
        
        // Contar usuarios
        $stmt = $conn->query("SELECT COUNT(*) FROM usuarios");
        $count = $stmt->fetchColumn();
        echo "<p>Total de usuarios en la base de datos: $count</p>";
        
        // Listar usuarios
        $stmt = $conn->query("SELECT id, nombre, email, rol FROM usuarios");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Lista de usuarios:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th></tr>";
        
        foreach ($usuarios as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['nombre']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['rol']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Tabla 'usuarios': <strong style='color:red'>NO EXISTE</strong></p>";
    }
} catch(PDOException $e) {
    echo "<p style='color:red'>Error al verificar la tabla: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.html' style='display: inline-block; padding: 10px 20px; background-color: #0d9488; color: white; text-decoration: none; border-radius: 5px;'>Volver a la página de inicio de sesión</a></p>";
?>
