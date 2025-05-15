<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado y es superusuario
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'superusuario') {
    // Redirigir a la página de inicio de sesión
    header("Location: ../index.html");
    exit;
}

// Incluir archivo de conexión
require_once '../config/database.php';

// Obtener todos los usuarios
try {
    $stmt = $conn->query("SELECT * FROM usuarios ORDER BY nombre ASC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medibot - Gestión de Usuarios</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="shortcut icon" href="../img/logo-medibot.ico" type="medibot-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos específicos para la página de usuarios */
        .usuarios-container {
            padding: 1.5rem;
        }
        
        .usuarios-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .usuarios-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d9488;
        }
        
        .usuarios-actions {
            display: flex;
            gap: 1rem;
        }
        
        .table-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 500;
            color: #6b7280;
        }
        
        .data-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
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
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.375rem;
            background-color: transparent;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .action-btn:hover {
            background-color: #f3f4f6;
        }
        
        .action-btn.edit {
            color: #0d9488;
        }
        
        .action-btn.delete {
            color: #dc2626;
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
            transition: transform 0.3s ease-in-out;
        }

        .sidebar.show {
            transform: translateX(0);
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
        
        /* Filtros y búsqueda */
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .search-box {
            flex: 1;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.625rem 1rem 0.625rem 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .filter-select {
            padding: 0.625rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            min-width: 150px;
        }
        
        /* Paginación */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }
        
        .pagination-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.375rem;
            margin: 0 0.25rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .pagination-item:hover {
            background-color: #f3f4f6;
        }
        
        .pagination-item.active {
            background-color: #0d9488;
            color: white;
        }

        /* Mobile styles */
        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
                transform: translateX(-250px);
                transition: transform 0.3s ease-in-out;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .top-bar {
                padding: 1rem;
            }

            .mobile-menu-btn {
                background: none;
                border: none;
                color: #4a5568;
                font-size: 1.5rem;
                cursor: pointer;
                margin-right: 1rem;
            }

            .top-bar > div {
                display: flex;
                align-items: center;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 50;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
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
            <a href="usuarios.php" class="sidebar-menu-item active">
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
            <div>
                <button class="mobile-menu-btn" id="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title">Gestión de Usuarios</div>
            </div>
            
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
        
        <!-- Usuarios Content -->
        <div class="usuarios-container">
            <div class="usuarios-header">
                <div class="usuarios-title">Lista de Usuarios</div>
                <div class="usuarios-actions">
                    <button class="btn btn-outline" onclick="exportarUsuarios()">
                        <i class="fas fa-file-export mr-2"></i> Exportar
                    </button>
                    <button class="btn btn-primary" onclick="location.href='agregar-usuario.php'">
                        <i class="fas fa-plus mr-2"></i> Nuevo Usuario
                    </button>
                </div>
            </div>
            
            <!-- Filtros y búsqueda -->
            <div class="filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar usuario...">
                </div>
                
                <select class="filter-select" id="rolFilter">
                    <option value="">Todos los roles</option>
                    <option value="usuario">Usuario</option>
                    <option value="administrador">Administrador</option>
                    <option value="superusuario">Superusuario</option>
                </select>
            </div>
            
            <!-- Tabla de usuarios -->
            <div class="table-card">
                <table class="data-table" id="usuariosTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['nombre']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <?php if ($user['rol'] === 'usuario'): ?>
                                        <span class="role-badge role-usuario">Usuario</span>
                                    <?php elseif ($user['rol'] === 'administrador'): ?>
                                        <span class="role-badge role-administrador">Administrador</span>
                                    <?php else: ?>
                                        <span class="role-badge role-superusuario">Superusuario</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['fecha_registro'])); ?></td>
                                <td>
                                    <button class="action-btn edit" onclick="editarUsuario(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $user['id']; ?>, '<?php echo $user['nombre']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="pagination">
                <div class="pagination-item">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="pagination-item active">1</div>
                <div class="pagination-item">2</div>
                <div class="pagination-item">3</div>
                <div class="pagination-item">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Add sidebar overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    
    <script>
        // Mobile menu functionality
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebar-overlay').classList.toggle('show');
        });
        
        document.getElementById('sidebar-overlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('show');
            this.classList.remove('show');
        });

        // Función para filtrar usuarios
        function filtrarUsuarios() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const rolFilter = document.getElementById('rolFilter').value;
            
            const rows = document.querySelectorAll('#usuariosTable tbody tr');
            
            rows.forEach(row => {
                const nombre = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                const rol = row.cells[3].textContent.trim().toLowerCase();
                
                let mostrar = true;
                
                // Filtrar por búsqueda
                if (searchInput && !nombre.includes(searchInput) && !email.includes(searchInput)) {
                    mostrar = false;
                }
                
                // Filtrar por rol
                if (rolFilter && !rol.includes(rolFilter.toLowerCase())) {
                    mostrar = false;
                }
                
                row.style.display = mostrar ? '' : 'none';
            });
        }
        
        // Agregar eventos a los filtros
        document.getElementById('searchInput').addEventListener('input', filtrarUsuarios);
        document.getElementById('rolFilter').addEventListener('change', filtrarUsuarios);
        
        // Función para editar usuario
        function editarUsuario(id) {
            window.location.href = `editar-usuario.php?id=${id}`;
        }
        
        // Función para confirmar eliminación
        function confirmarEliminar(id, nombre) {
            if (confirm(`¿Está seguro que desea eliminar al usuario "${nombre}"?`)) {
                // Aquí iría la lógica para eliminar el usuario
                alert(`Usuario "${nombre}" eliminado correctamente.`);
                // Recargar la página o actualizar la tabla
                location.reload();
            }
        }
        
        // Función para exportar usuarios
        function exportarUsuarios() {
            alert('Exportando usuarios a Excel...');
            // Aquí iría la lógica para exportar los usuarios
        }
    </script>
</body>
</html>
