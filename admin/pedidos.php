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

// Obtener todos los pedidos
try {
    $stmt = $conn->query("SELECT p.*, u.nombre as nombre_usuario FROM pedidos p 
                          JOIN usuarios u ON p.usuario_id = u.id 
                          ORDER BY p.fecha_pedido DESC");
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medibot - Gestión de Pedidos</title>

    <link rel="shortcut icon" href="../img/logo-medibot.ico" type="Icono Medibot">

    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos específicos para la página de pedidos */
        .pedidos-container {
            padding: 1.5rem;
        }
        
        .pedidos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .pedidos-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d9488;
        }
        
        .pedidos-actions {
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
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-pendiente {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        
        .status-procesando {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-enviado {
            background-color: #f3e8ff;
            color: #7c3aed;
        }
        
        .status-entregado {
            background-color: #d1fae5;
            color: #047857;
        }
        
        .status-cancelado {
            background-color: #fee2e2;
            color: #b91c1c;
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
        
        .action-btn.view {
            color: #0d9488;
        }
        
        .action-btn.edit {
            color: #0369a1;
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
            <a href="pedidos.php" class="sidebar-menu-item active">
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
            <div class="page-title">Gestión de Pedidos</div>
            
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
        
        <!-- Pedidos Content -->
        <div class="pedidos-container">
            <div class="pedidos-header">
                <div class="pedidos-title">Lista de Pedidos</div>
                <div class="pedidos-actions">
                    <button class="btn btn-outline" onclick="exportarPedidos()">
                        <i class="fas fa-file-export mr-2"></i> Exportar
                    </button>
                </div>
            </div>
            
            <!-- Filtros y búsqueda -->
            <div class="filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar pedido...">
                </div>
                
                <select class="filter-select" id="estadoFilter">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="procesando">Procesando</option>
                    <option value="enviado">Enviado</option>
                    <option value="entregado">Entregado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            
            <!-- Tabla de pedidos -->
            <div class="table-card">
                <table class="data-table" id="pedidosTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pedidos) > 0): ?>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td><?php echo $pedido['id']; ?></td>
                                    <td><?php echo $pedido['nombre_usuario']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'status-' . $pedido['estado'];
                                        $statusText = ucfirst($pedido['estado']);
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td>
                                        <button class="action-btn view" onclick="verPedido(<?php echo $pedido['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn edit" onclick="cambiarEstado(<?php echo $pedido['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No hay pedidos registrados</td>
                            </tr>
                        <?php endif; ?>
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
    
    <script>
        // Función para filtrar pedidos
        function filtrarPedidos() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const estadoFilter = document.getElementById('estadoFilter').value.toLowerCase();
            
            const rows = document.querySelectorAll('#pedidosTable tbody tr');
            
            rows.forEach(row => {
                const id = row.cells[0].textContent;
                const cliente = row.cells[1].textContent.toLowerCase();
                const estado = row.cells[4].textContent.trim().toLowerCase();
                
                let mostrar = true;
                
                // Filtrar por búsqueda
                if (searchInput && !id.includes(searchInput) && !cliente.includes(searchInput)) {
                    mostrar = false;
                }
                
                // Filtrar por estado
                if (estadoFilter && !estado.includes(estadoFilter)) {
                    mostrar = false;
                }
                
                row.style.display = mostrar ? '' : 'none';
            });
        }
        
        // Agregar eventos a los filtros
        document.getElementById('searchInput').addEventListener('input', filtrarPedidos);
        document.getElementById('estadoFilter').addEventListener('change', filtrarPedidos);
        
        // Función para ver detalles del pedido
        function verPedido(id) {
            window.location.href = `ver-pedido.php?id=${id}`;
        }
        
        // Función para cambiar estado del pedido
        function cambiarEstado(id) {
            const estados = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
            const estadoActual = prompt('Ingrese el nuevo estado del pedido (pendiente, procesando, enviado, entregado, cancelado):');
            
            if (estadoActual && estados.includes(estadoActual.toLowerCase())) {
                // Aquí iría la lógica para actualizar el estado del pedido
                alert(`Estado del pedido #${id} actualizado a "${estadoActual}".`);
                // Recargar la página o actualizar la tabla
                location.reload();
            } else if (estadoActual) {
                alert('Estado no válido. Los estados permitidos son: pendiente, procesando, enviado, entregado, cancelado.');
            }
        }
        
        // Función para exportar pedidos
        function exportarPedidos() {
            alert('Exportando pedidos a Excel...');
            // Aquí iría la lógica para exportar los pedidos
        }
    </script>
</body>
</html>
