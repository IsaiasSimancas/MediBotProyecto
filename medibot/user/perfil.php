<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Redirigir a la página de inicio de sesión
    header("Location: ../index.html");
    exit;
}

// Verificar si el usuario tiene el rol correcto (usuario)
if ($_SESSION['user_role'] !== 'usuario') {
    // Redirigir según el rol
    if ($_SESSION['user_role'] === 'administrador' || $_SESSION['user_role'] === 'superusuario') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../index.html");
    }
    exit;
}

// Incluir archivo de conexión
require_once '../config/database.php';

// Obtener datos del usuario
try {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// Obtener items en el carrito del usuario
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM carrito WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $items_carrito = $stmt->fetchColumn();
} catch(PDOException $e) {
    $items_carrito = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmaExpress - Mi Perfil</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .navbar {
            background-color: #0f766e;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            transition: background-color 0.3s;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .profile-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .profile-card {
            background-color: white;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .profile-header {
            background-color: #0f766e;
            color: white;
            padding: 1.5rem;
            position: relative;
        }
        
        .profile-title {
            font-size: 1.5rem;
            margin: 0;
            margin-bottom: 0.5rem;
        }
        
        .profile-subtitle {
            font-size: 1rem;
            opacity: 0.8;
        }
        
        .profile-avatar {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: -50px;
            position: relative;
            z-index: 1;
        }
        
        .avatar-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .avatar-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: #0f766e;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
        }
        
        .avatar-upload input[type="file"] {
            display: none;
        }
        
        .profile-body {
            padding: 2rem;
        }
        
        .profile-info {
            margin-top: 2rem;
        }
        
        .info-item {
            margin-bottom: 1.5rem;
        }
        
        .info-label {
            display: block;
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            font-size: 1rem;
            font-weight: bold;
        }
        
        .tab-nav {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }
        
        .tab-btn {
            padding: 1rem;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .tab-btn.active {
            color: #0f766e;
            border-bottom-color: #0f766e;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #0f766e;
            box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.1);
        }
        
        .form-help {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.2s;
            border: none;
        }
        
        .btn-primary {
            background-color: #0f766e;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0d9488;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #047857;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .footer {
            background-color: #0f766e;
            color: white;
            padding: 3rem 1rem;
            margin-top: 2rem;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .footer-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 0.5rem;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-bottom {
            margin-top: 2rem;
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .cart-badge {
            display: inline-block;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            position: relative;
            top: -10px;
            left: -5px;
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: white;
            border-radius: 0.25rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 10;
        }
        
        .user-dropdown:hover .user-menu {
            display: block;
        }
        
        .user-menu a {
            display: block;
            padding: 0.75rem 1rem;
            color: #333;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .user-menu a:hover {
            background-color: #f5f5f5;
        }
        
        .user-menu hr {
            margin: 0;
            border: none;
            border-top: 1px solid #eee;
        }
        
        .user-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 0.5rem;
        }
        
        .photo-preview {
            margin-top: 10px;
            display: none;
        }
        
        .photo-preview img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0f766e;
        }
        
        @media (min-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr 2fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">FarmaExpress</a>
        
        <div class="navbar-nav">
            <a href="medicamentos.php" class="nav-link">
                <i class="fas fa-pills"></i> Medicamentos
            </a>
            
            <a href="carrito.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($items_carrito > 0): ?>
                <span class="cart-badge"><?php echo $items_carrito; ?></span>
                <?php endif; ?>
            </a>
            
            <div class="user-dropdown">
                <a href="#" class="nav-link">
                    <img src="<?php echo !empty($usuario['foto_perfil']) ? '../' . $usuario['foto_perfil'] : '../img/avatar-placeholder.png'; ?>" alt="Avatar" class="user-avatar">
                    <?php echo htmlspecialchars($usuario['nombre']); ?>
                </a>
                
                <div class="user-menu">
                    <a href="perfil.php">
                        <i class="fas fa-user-circle"></i> Mi Perfil
                    </a>
                    <a href="pedidos.php">
                        <i class="fas fa-clipboard-list"></i> Mis Pedidos
                    </a>
                    <hr>
                    <a href="../api/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <!-- Alertas -->
        <?php
        // Mostrar mensajes de éxito o error si existen
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo '<div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Perfil actualizado correctamente.
            </div>';
        }
        
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($_GET['error']) . '
            </div>';
        }
        ?>
        
        <!-- Perfil -->
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <h1 class="profile-title">Mi Perfil</h1>
                    <p class="profile-subtitle">Gestiona tu información personal</p>
                </div>
                
                <div class="profile-body">
                    <div class="profile-avatar">
                        <img src="<?php echo !empty($usuario['foto_perfil']) ? '../' . $usuario['foto_perfil'] : '../img/avatar-placeholder.png'; ?>" alt="Avatar" class="avatar-img">
                        
                        <!-- Form para cambiar foto de perfil -->
                        <form action="../api/actualizar-foto.php" method="POST" enctype="multipart/form-data">
                            <label for="foto_perfil" class="avatar-upload">
                                <i class="fas fa-camera"></i>
                                <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" onchange="previewImage(this)">
                            </label>
                            
                            <div class="photo-preview" id="photoPreview">
                                <img id="preview" src="#" alt="Vista previa">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="profile-info">
                        <div class="info-item">
                            <span class="info-label">Nombre</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Correo Electrónico</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['email']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Fecha de Registro</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="profile-card">
                <div class="tab-nav">
                    <div class="tab-btn active" data-tab="info">Información Personal</div>
                    <div class="tab-btn" data-tab="password">Cambiar Contraseña</div>
                </div>
                
                <!-- Tab: Información Personal -->
                <div class="tab-content active" id="tab-info">
                    <div class="profile-body">
                        <form action="../api/actualizar-perfil.php" method="POST">
                            <input type="hidden" name="action" value="update_info">
                            
                            <div class="form-group">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" id="nombre" name="nombre" class="form-input" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Tab: Cambiar Contraseña -->
                <div class="tab-content" id="tab-password">
                    <div class="profile-body">
                        <form action="../api/actualizar-perfil.php" method="POST">
                            <input type="hidden" name="action" value="update_password">
                            
                            <div class="form-group">
                                <label for="current_password" class="form-label">Contraseña Actual</label>
                                <input type="password" id="current_password" name="current_password" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password" class="form-label">Nueva Contraseña</label>
                                <input type="password" id="new_password" name="new_password" class="form-input" required>
                                <span class="form-help">La contraseña debe tener al menos 6 caracteres.</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Cambiar Contraseña
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3 class="footer-title">FarmaExpress</h3>
                    <p>Tu farmacia de confianza con los mejores productos y precios.</p>
                </div>
                
                <div>
                    <h3 class="footer-title">Enlaces Rápidos</h3>
                    <ul class="footer-links">
                        <li><a href="dashboard.php">Inicio</a></li>
                        <li><a href="medicamentos.php">Medicamentos</a></li>
                        <li><a href="carrito.php">Carrito de Compras</a></li>
                        <li><a href="pedidos.php">Mis Pedidos</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="footer-title">Contacto</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i> Calle Principal #123</li>
                        <li><i class="fas fa-phone"></i> (123) 456-7890</li>
                        <li><i class="fas fa-envelope"></i> info@farmaexpress.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> FarmaExpress. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Funcionalidad de pestañas
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remover clase active de todos los botones y contenidos
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Agregar clase active al botón clickeado
                this.classList.add('active');
                
                // Mostrar el contenido correspondiente
                const tabId = 'tab-' + this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Previsualización de imagen
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('photoPreview').style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
