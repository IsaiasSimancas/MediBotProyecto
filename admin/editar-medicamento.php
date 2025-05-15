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

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: medicamentos.php");
    exit;
}

$id = intval($_GET['id']);

// Obtener datos del medicamento
try {
    $stmt = $conn->prepare("SELECT * FROM medicamentos WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() == 0) {
        header("Location: medicamentos.php");
        exit;
    }
    
    $medicamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener categorías existentes
    $stmt = $conn->query("SELECT DISTINCT categoria FROM medicamentos ORDER BY categoria");
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch(PDOException $e) {
    header("Location: medicamentos.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medibot - Editar Medicamento</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="shortcut icon" href="../img/logo-medibot.ico" type="medibot-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos específicos para el formulario */
        .form-container {
            padding: 1.5rem;
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .form-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d9488;
        }
        
        .form-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
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
        
        .form-textarea {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            min-height: 100px;
            resize: vertical;
        }
        
        .form-textarea:focus {
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
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .image-preview {
            width: 100%;
            height: 200px;
            border: 2px dashed #d1d5db;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.5rem;
            overflow: hidden;
            position: relative;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .current-image {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
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
        
        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Estilos para responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                overflow-x: hidden;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .form-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-header .btn {
                margin-top: 1rem;
                width: 100%;
            }
            
            .form-grid {
                grid-template-columns: 1fr !important;
            }
            
            .form-footer {
                flex-direction: column;
            }
            
            .form-footer .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-dropdown {
                margin-top: 1rem;
                align-self: flex-end;
            }
        }

        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            background-color: #0d9488;
            color: white;
            border: none;
            border-radius: 0.375rem;
            width: 2.5rem;
            height: 2.5rem;
            font-size: 1.25rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
            
            .sidebar {
                display: none;
            }
            
            .sidebar.active {
                display: block;
            }
        }
    </style>
</head>
<body>
    <button id="sidebarToggle" class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>
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
            <a href="medicamentos.php" class="sidebar-menu-item active">
                <i class="fas fa-pills"></i> Medicamentos
            </a>
            <a href="usuarios.php" class="sidebar-menu-item">
                <i class="fas fa-users"></i> Usuarios
            </a>
            <a href="pedidos.php" class="sidebar-menu-item">
                <i class="fas fa-shopping-cart"></i> Pedidos
            </a>
            
            <div class="sidebar-menu-title">Configuración</div>
            <a href="perfil.php" class="sidebar-menu-item">
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
            <div class="page-title">Editar Medicamento</div>
            
            <div class="user-dropdown">
                <div class="user-dropdown-btn">
                    <img src="../img/administrador.png" alt="Avatar">
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
        
        <!-- Form Content -->
        <div class="form-container">
            <div class="form-header">
                <div class="form-title">Editar Medicamento</div>
                <a href="medicamentos.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a Medicamentos
                </a>
            </div>
            
            <?php
            // Mostrar mensajes de éxito o error si existen
            if (isset($_GET['success']) && $_GET['success'] == 1) {
                echo '<div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i> Medicamento actualizado correctamente.
                </div>';
            }
            
            if (isset($_GET['error'])) {
                echo '<div class="alert alert-error">
                    <i class="fas fa-exclamation-circle mr-2"></i> ' . htmlspecialchars($_GET['error']) . '
                </div>';
            }
            ?>
            
            <div class="form-card">
                <form action="../api/actualizar-medicamento.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $medicamento['id']; ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre del Medicamento *</label>
                            <input type="text" id="nombre" name="nombre" class="form-input" value="<?php echo htmlspecialchars($medicamento['nombre']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="categoria" class="form-label">Categoría *</label>
                            <select id="categoria" name="categoria" class="form-select" required>
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo htmlspecialchars($categoria); ?>" <?php echo ($medicamento['categoria'] == $categoria) ? 'selected' : ''; ?>><?php echo htmlspecialchars($categoria); ?></option>
                                <?php endforeach; ?>
                                <option value="otra">Otra (especificar)</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="otra-categoria-container" style="display: none;">
                            <label for="otra_categoria" class="form-label">Especificar Categoría *</label>
                            <input type="text" id="otra_categoria" name="otra_categoria" class="form-input">
                        </div>
                        
                        <div class="form-group">
                            <label for="precio" class="form-label">Precio *</label>
                            <input type="number" id="precio" name="precio" class="form-input" step="0.01" min="0" value="<?php echo $medicamento['precio']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock" class="form-label">Stock *</label>
                            <input type="number" id="stock" name="stock" class="form-input" min="0" value="<?php echo $medicamento['stock']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="principio_activo" class="form-label">Principio Activo</label>
                            <input type="text" id="principio_activo" name="principio_activo" class="form-input" value="<?php echo htmlspecialchars($medicamento['principio_activo']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="presentacion" class="form-label">Presentación</label>
                            <input type="text" id="presentacion" name="presentacion" class="form-input" value="<?php echo htmlspecialchars($medicamento['presentacion']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="laboratorio" class="form-label">Laboratorio</label>
                            <input type="text" id="laboratorio" name="laboratorio" class="form-input" value="<?php echo htmlspecialchars($medicamento['laboratorio']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion" class="form-label">Descripción *</label>
                        <textarea id="descripcion" name="descripcion" class="form-textarea" required><?php echo htmlspecialchars($medicamento['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="dosificacion" class="form-label">Dosificación</label>
                        <textarea id="dosificacion" name="dosificacion" class="form-textarea"><?php echo htmlspecialchars($medicamento['dosificacion']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen" class="form-label">Imagen del Medicamento</label>
                        <input type="file" id="imagen" name="imagen" class="form-input" accept="image/*">
                        <div class="current-image">
                            Imagen actual: <?php echo $medicamento['imagen']; ?>
                        </div>
                        <div class="image-preview" id="imagePreview">
                            <?php if (!empty($medicamento['imagen'])): ?>
                                <img src="../<?php echo $medicamento['imagen']; ?>" alt="<?php echo htmlspecialchars($medicamento['nombre']); ?>">
                            <?php else: ?>
                                <div class="image-preview-placeholder">
                                    <i class="fas fa-image"></i>
                                    <p>No hay imagen disponible</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <button type="button" class="btn btn-outline" onclick="location.href='medicamentos.php'">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Actualizar Medicamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Mostrar/ocultar campo de otra categoría
        document.getElementById('categoria').addEventListener('change', function() {
            const otraCategoriaContainer = document.getElementById('otra-categoria-container');
            if (this.value === 'otra') {
                otraCategoriaContainer.style.display = 'block';
                document.getElementById('otra_categoria').setAttribute('required', 'required');
            } else {
                otraCategoriaContainer.style.display = 'none';
                document.getElementById('otra_categoria').removeAttribute('required');
            }
        });
        
        // Vista previa de la imagen
        document.getElementById('imagen').addEventListener('change', function() {
            const imagePreview = document.getElementById('imagePreview');
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Vista previa">`;
                }
                
                reader.readAsDataURL(file);
            }
        });

        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        });
    </script>
</body>
</html>
