<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Redirigir a la página de inicio de sesión
    header("Location: ../index.html");
    exit;
}

// Incluir archivo de conexión
require_once '../config/database.php';

// Obtener datos del usuario
try {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    if ($stmt->rowCount() == 0) {
        // Si el usuario no existe, cerrar sesión y redirigir
        session_unset();
        session_destroy();
        header("Location: ../index.html");
        exit;
    }
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medibot - Mi Perfil</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="shortcut icon" href="../img/logo-medibot.ico" type="medibot-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos específicos para el perfil */
        .perfil-container {
            padding: 1.5rem;
        }
        
        .perfil-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .perfil-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d9488;
        }
        
        .perfil-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .perfil-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        .perfil-avatar {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 1.5rem;
            position: sticky;
            top: 2rem;
            height: fit-content;
        }
        
        .avatar-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 4px solid #0d9488;
        }
        
        .avatar-upload {
            margin-top: 1rem;
            text-align: center;
        }
        
        .avatar-upload-btn {
            background-color: #0d9488;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.875rem;
            display: inline-block;
        }
        
        .avatar-upload-btn:hover {
            background-color: #0f766e;
        }
        
        .file-input {
            display: none;
        }
        
        .avatar-name {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .avatar-role {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .role-usuario {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        
        .role-administrador {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .role-superusuario {
            background-color: #f3e8ff;
            color: #7c3aed;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        
        .form-input {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #0d9488;
            box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            background-color: white;
        }
        
        .form-select:focus {
            outline: none;
            border-color: #0d9488;
            box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.1);
        }
        
        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #047857;
        }
        
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        
        .sidebar {
            width: 250px;
            background-color: #0f766e;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            z-index: 50;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-logo {
            font-size: 1.25rem;
            font-weight: bold;
        }
        
        .sidebar-menu {
            padding: 1.5rem 0;
        }
        
        .sidebar-menu-title {
            padding: 0 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu-item {
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.2s;
        }
        
        .sidebar-menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid white;
        }
        
        .sidebar-menu-item i {
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            background-color: #f3f4f6;
        }
        
        .top-bar {
            background-color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .user-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .user-dropdown-btn {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .user-dropdown-btn img {
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            margin-right: 0.5rem;
        }
        
        .user-dropdown-content {
            position: absolute;
            right: 0;
            top: 100%;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 0.375rem;
            padding: 0.5rem 0;
            z-index: 10;
            display: none;
        }
        
        .user-dropdown-item {
            padding: 0.5rem 1rem;
            display: block;
            color: #4b5563;
        }
        
        .user-dropdown-item:hover {
            background-color: #f3f4f6;
        }
        
        .user-dropdown:hover .user-dropdown-content {
            display: block;
        }
        
        .tab-container {
            margin-bottom: 1.5rem;
        }
        
        .tab-buttons {
            display: flex;
            flex-wrap: wrap;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }
        
        .tab-button {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .tab-button.active {
            color: #0d9488;
            border-bottom-color: #0d9488;
        }
        
        .tab-content {
            display: none;
            min-height: 350px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Responsive styles */
        @media (max-width: 1024px) {
            .perfil-grid {
                grid-template-columns: 1fr 1.5fr;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .perfil-grid {
                grid-template-columns: 1fr;
            }
            
            .perfil-avatar {
                position: static;
                margin-bottom: 2rem;
            }
            
            .tab-button {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
            
            .form-footer {
                flex-direction: column;
            }
            
            .form-footer button {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .perfil-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .tab-buttons {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 0.5rem;
                width: 100%;
            }
            
            .tab-button {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }
            
            .form-input, .form-select {
                font-size: 16px; /* Prevents zoom on mobile */
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">Medibot</div>
        </div>
        
        <div class="sidebar-menu">
            <div class="sidebar-menu-title">General</div>
            <a href="dashboard.php" class="sidebar-menu-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="medicamentos.php" class="sidebar-menu-item">
                <i class="fas fa-pills"></i> Medicamentos
            </a>
            <a href="usuarios.php" class="sidebar-menu-item">
                <i class="fas fa-users"></i> Usuarios
            </a>
            <a href="pedidos.php" class="sidebar-menu-item">
                <i class="fas fa-shopping-cart"></i> Pedidos
            </a>
            
            <div class="sidebar-menu-title">Configuración</div>
            <a href="perfil.php" class="sidebar-menu-item active">
                <i class="fas fa-user-cog"></i> Mi Perfil
            </a>
            <a href="../api/logout.php" class="sidebar-menu-item">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="page-title">Mi Perfil</div>
            
            <div class="user-dropdown">
                <div class="user-dropdown-btn">
                    <?php 
                    // Mostrar la imagen de perfil del usuario o una predeterminada
                    $avatar_url = isset($usuario['imagen_perfil']) && !empty($usuario['imagen_perfil']) 
                        ? "../" . $usuario['imagen_perfil'] 
                        : "../img/administrador.png";
                    ?>
                    <img src="<?php echo $avatar_url; ?>" alt="Avatar">
                    <span><?php echo $_SESSION['user_name']; ?></span>
                    <i class="fas fa-chevron-down ml-2"></i>
                </div>
                <div class="user-dropdown-content">
                    <a href="perfil.php" class="user-dropdown-item">
                        <i class="fas fa-user-circle mr-2"></i> Mi Perfil
                    </a>
                    <a href="../api/logout.php" class="user-dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Perfil Content -->
        <div class="perfil-container">
            <div class="perfil-header">
                <div class="perfil-title">Mi Perfil</div>
            </div>
            
            <?php
            // Mostrar mensajes de éxito o error si existen
            if (isset($_GET['success']) && $_GET['success'] == 1) {
                echo '<div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i> Perfil actualizado correctamente.
                </div>';
            }
            
            if (isset($_GET['error'])) {
                echo '<div class="alert alert-error">
                    <i class="fas fa-exclamation-circle mr-2"></i> ' . htmlspecialchars($_GET['error']) . '
                </div>';
            }
            ?>
            
            <div class="perfil-card">
                <div class="perfil-grid">
                    <div class="perfil-avatar">
                        <?php 
                        // Mostrar la imagen de perfil del usuario o una predeterminada
                        $avatar_url = isset($usuario['imagen_perfil']) && !empty($usuario['imagen_perfil']) 
                            ? "../" . $usuario['imagen_perfil'] 
                            : "../img/administrador.png";
                        ?>
                        <img src="<?php echo $avatar_url; ?>" alt="Avatar" class="avatar-img">
                        
                        <!-- Formulario para cambiar la imagen de perfil -->
                        <div class="avatar-upload">
                            <form action="../api/actualizar-perfil.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_image">
                                <input type="file" name="profile_image" id="profile_image" class="file-input" accept="image/*">
                                <label for="profile_image" class="avatar-upload-btn">
                                    <i class="fas fa-camera mr-2"></i> Cambiar foto
                                </label>
                                <button type="submit" class="btn btn-primary mt-2" style="display: none;" id="submit-image">
                                    <i class="fas fa-save mr-2"></i> Guardar
                                </button>
                            </form>
                        </div>
                        
                        <div class="avatar-name"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                        <?php
                        $roleClass = 'role-' . $usuario['rol'];
                        $roleText = ucfirst($usuario['rol']);
                        ?>
                        <span class="avatar-role <?php echo $roleClass; ?>"><?php echo $roleText; ?></span>
                        
                        <div class="mt-4">
                            <p>Fecha de registro: <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></p>
                            <p>Correo electrónico: <?php echo htmlspecialchars($usuario['email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="perfil-details">
                        <div class="tab-container">
                            <div class="tab-buttons">
                                <div class="tab-button active" data-tab="info">Información Personal</div>
                                <div class="tab-button" data-tab="password">Cambiar Contraseña</div>
                                <?php if ($_SESSION['user_role'] === 'superusuario'): ?>
                                <div class="tab-button" data-tab="role">Cambiar Rol</div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Tab: Información Personal -->
                            <div class="tab-content active" id="tab-info">
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
                                    
                                    <div class="form-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Tab: Cambiar Contraseña -->
                            <div class="tab-content" id="tab-password">
                                <form action="../api/actualizar-perfil.php" method="POST">
                                    <input type="hidden" name="action" value="update_password">
                                    
                                    <div class="form-group">
                                        <label for="current_password" class="form-label">Contraseña Actual</label>
                                        <input type="password" id="current_password" name="current_password" class="form-input" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password" class="form-label">Nueva Contraseña</label>
                                        <input type="password" id="new_password" name="new_password" class="form-input" required>
                                        <small class="text-gray-500">La contraseña debe tener al menos 6 caracteres.</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                                    </div>
                                    
                                    <div class="form-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-key mr-2"></i> Cambiar Contraseña
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <?php if ($_SESSION['user_role'] === 'superusuario'): ?>
                            <!-- Tab: Cambiar Rol (solo para superusuarios) -->
                            <div class="tab-content" id="tab-role">
                                <form action="../api/actualizar-perfil.php" method="POST">
                                    <input type="hidden" name="action" value="update_role">
                                    
                                    <div class="form-group">
                                        <label for="role" class="form-label">Seleccionar Rol</label>
                                        <select id="role" name="role" class="form-select" required>
                                            <option value="usuario" <?php echo ($usuario['rol'] === 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                                            <option value="administrador" <?php echo ($usuario['rol'] === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                                            <option value="superusuario" <?php echo ($usuario['rol'] === 'superusuario') ? 'selected' : ''; ?>>Superusuario</option>
                                        </select>
                                        <small class="text-gray-500">Cambiar tu rol puede afectar tus permisos en el sistema.</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="role_password" class="form-label">Confirmar Contraseña</label>
                                        <input type="password" id="role_password" name="password" class="form-input" required>
                                        <small class="text-gray-500">Por seguridad, ingresa tu contraseña actual para confirmar el cambio de rol.</small>
                                    </div>
                                    
                                    <div class="form-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-user-shield mr-2"></i> Actualizar Rol
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Funcionalidad de pestañas
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                // Remover clase active de todos los botones y contenidos
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Agregar clase active al botón clickeado
                this.classList.add('active');
                
                // Mostrar el contenido correspondiente
                const tabId = 'tab-' + this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Mostrar el botón de guardar cuando se selecciona una imagen
        document.getElementById('profile_image').addEventListener('change', function() {
            if(this.files && this.files[0]) {
                document.getElementById('submit-image').style.display = 'inline-block';
                
                // Previsualizar la imagen
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.avatar-img').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>
