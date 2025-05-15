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

// Obtener todos los medicamentos
try {
    $stmt = $conn->query("SELECT * FROM medicamentos ORDER BY nombre ASC");
    $medicamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medibot - Gestión de Medicamentos</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="shortcut icon" href="../img/logo-medibot.ico" type="medibot-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos específicos para la página de medicamentos */
        .medicamentos-container {
            padding: 1.5rem;
        }
        
        .medicamentos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .medicamentos-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d9488;
        }
        
        .medicamentos-actions {
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
        
        .stock-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .stock-low {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .stock-medium {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .stock-good {
            background-color: #d1fae5;
            color: #047857;
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
            
            .medicamentos-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .medicamentos-actions {
                margin-top: 1rem;
                width: 100%;
                justify-content: space-between;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .search-box, .filter-select {
                margin-bottom: 0.5rem;
                width: 100%;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
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

        /* Indicador de carga */
        .loading-indicator {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            font-size: 1.5rem;
            color: #0d9488;
        }

        .loading-indicator i {
            margin-right: 0.5rem;
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

        /* Modal de exportación */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            border-radius: 0.5rem;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.25rem;
            font-weight: bold;
            color: #0d9488;
            margin: 0;
        }

        .close-modal {
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .export-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
        }

        .export-options .btn {
            width: 100%;
        }

        /* Mobile styles */
        .mobile-menu-btn {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0;
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

        .sidebar.show {
            display: block;
            z-index: 100;
        }

        .sidebar-overlay.show {
            display: block;
        }

        @media (min-width: 769px) {
            .sidebar {
                display: block !important;
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
            <div>
                <button class="mobile-menu-btn" id="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title">Gestión de Medicamentos</div>
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
        
        <!-- Medicamentos Content -->
        <div class="medicamentos-container">
            <div class="medicamentos-header">
                <div class="medicamentos-title">Lista de Medicamentos</div>
                <div class="medicamentos-actions">
                    <button class="btn btn-outline" onclick="exportarMedicamentos()">
                        <i class="fas fa-file-export mr-2"></i> Exportar
                    </button>
                    <button class="btn btn-primary" onclick="location.href='agregar-medicamento.php'">
                        <i class="fas fa-plus mr-2"></i> Nuevo Medicamento
                    </button>
                </div>
            </div>
            
            <!-- Filtros y búsqueda -->
            <div class="filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar medicamento...">
                </div>
                
                <select class="filter-select" id="categoriaFilter">
                    <option value="">Todas las categorías</option>
                    <option value="Analgésicos">Analgésicos</option>
                    <option value="Antiinflamatorios">Antiinflamatorios</option>
                    <option value="Antibióticos">Antibióticos</option>
                    <option value="Gastrointestinal">Gastrointestinal</option>
                    <option value="Vitaminas">Vitaminas</option>
                    <option value="Antialérgicos">Antialérgicos</option>
                </select>
                
                <select class="filter-select" id="stockFilter">
                    <option value="">Todos los stocks</option>
                    <option value="bajo">Stock bajo</option>
                    <option value="medio">Stock medio</option>
                    <option value="alto">Stock alto</option>
                </select>
            </div>
            
            <!-- Tabla de medicamentos -->
            <div class="table-card">
                <table class="data-table" id="medicamentosTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicamentos as $med): ?>
                            <tr>
                                <td><?php echo $med['id']; ?></td>
                                <td><?php echo $med['nombre']; ?></td>
                                <td><?php echo $med['categoria']; ?></td>
                                <td>$<?php echo number_format($med['precio'], 2); ?></td>
                                <td><?php echo $med['stock']; ?></td>
                                <td>
                                    <?php if ($med['stock'] < 10): ?>
                                        <span class="stock-badge stock-low">Crítico</span>
                                    <?php elseif ($med['stock'] < 30): ?>
                                        <span class="stock-badge stock-medium">Bajo</span>
                                    <?php else: ?>
                                        <span class="stock-badge stock-good">Bueno</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="action-btn edit" onclick="editarMedicamento(<?php echo $med['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="confirmarEliminar(<?php echo $med['id']; ?>, '<?php echo $med['nombre']; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
    
    <!-- Modal de Exportación -->
    <div id="exportModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Exportar Medicamentos</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Seleccione el formato de exportación:</p>
                <div class="export-options">
                    <button class="btn btn-primary" onclick="exportarPDF()">
                        <i class="fas fa-file-pdf mr-2"></i> Exportar a PDF
                    </button>
                    <button class="btn btn-outline" onclick="exportarCSV()">
                        <i class="fas fa-file-csv mr-2"></i> Exportar a CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add sidebar overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    
    <script>
        // Función para filtrar medicamentos
        function filtrarMedicamentos() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const categoriaFilter = document.getElementById('categoriaFilter').value;
            const stockFilter = document.getElementById('stockFilter').value;
            
            const rows = document.querySelectorAll('#medicamentosTable tbody tr');
            
            rows.forEach(row => {
                const nombre = row.cells[1].textContent.toLowerCase();
                const categoria = row.cells[2].textContent;
                const stock = parseInt(row.cells[4].textContent);
                const estadoStock = row.cells[5].textContent.trim();
                
                let mostrar = true;
                
                // Filtrar por búsqueda
                if (searchInput && !nombre.includes(searchInput)) {
                    mostrar = false;
                }
                
                // Filtrar por categoría
                if (categoriaFilter && categoria !== categoriaFilter) {
                    mostrar = false;
                }
                
                // Filtrar por stock
                if (stockFilter) {
                    if (stockFilter === 'bajo' && stock >= 30) {
                        mostrar = false;
                    } else if (stockFilter === 'medio' && (stock < 10 || stock >= 50)) {
                        mostrar = false;
                    } else if (stockFilter === 'alto' && stock < 50) {
                        mostrar = false;
                    }
                }
                
                row.style.display = mostrar ? '' : 'none';
            });
        }
        
        // Agregar eventos a los filtros
        document.getElementById('searchInput').addEventListener('input', filtrarMedicamentos);
        document.getElementById('categoriaFilter').addEventListener('change', filtrarMedicamentos);
        document.getElementById('stockFilter').addEventListener('change', filtrarMedicamentos);
        
        // Función para editar medicamento
        function editarMedicamento(id) {
            window.location.href = `editar-medicamento.php?id=${id}`;
        }
        
        // Función para confirmar eliminación
        function confirmarEliminar(id, nombre) {
            if (confirm(`¿Está seguro que desea eliminar el medicamento "${nombre}"?`)) {
                // Aquí iría la lógica para eliminar el medicamento
                alert(`Medicamento "${nombre}" eliminado correctamente.`);
                // Recargar la página o actualizar la tabla
                location.reload();
            }
        }
        
        // Función para mostrar modal de exportación
        function exportarMedicamentos() {
            document.getElementById('exportModal').style.display = 'flex';
        }

        // Función para exportar a PDF
        function exportarPDF() {
            // Mostrar indicador de carga
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'loading-indicator';
            loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando PDF...';
            document.body.appendChild(loadingIndicator);
            
            // Cerrar modal
            document.getElementById('exportModal').style.display = 'none';
            
            // Redirigir a la página de exportación
            window.location.href = '../api/exportar-medicamentos.php';
        }

        // Función para exportar a CSV
        function exportarCSV() {
            // Mostrar indicador de carga
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'loading-indicator';
            loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando CSV...';
            document.body.appendChild(loadingIndicator);
            
            // Cerrar modal
            document.getElementById('exportModal').style.display = 'none';
            
            // Redirigir a la página de exportación
            window.location.href = '../api/exportar-medicamentos-csv.php';
        }

        // Cerrar modal al hacer clic en la X
        document.querySelector('.close-modal').addEventListener('click', function() {
            document.getElementById('exportModal').style.display = 'none';
        });

        // Cerrar modal al hacer clic fuera del contenido
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('exportModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        });

        // Mobile menu functionality
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebar-overlay').classList.toggle('show');
        });
        
        document.getElementById('sidebar-overlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('show');
            this.classList.remove('show');
        });
    </script>
</body>
</html>
