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

// Obtener datos para el dashboard
try {
    // Total de medicamentos
    $stmt = $conn->query("SELECT COUNT(*) FROM medicamentos");
    $totalMedicamentos = $stmt->fetchColumn();
    
    // Total de usuarios
    $stmt = $conn->query("SELECT COUNT(*) FROM usuarios");
    $totalUsuarios = $stmt->fetchColumn();
    
    // Total de pedidos
    $stmt = $conn->query("SELECT COUNT(*) FROM pedidos");
    $totalPedidos = $stmt->fetchColumn();
    
    // Medicamentos por categoría
    $stmt = $conn->query("SELECT categoria, COUNT(*) as cantidad FROM medicamentos GROUP BY categoria");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Medicamentos con poco stock (menos de 30 unidades)
    $stmt = $conn->query("SELECT id, nombre, stock, categoria FROM medicamentos WHERE stock < 30 ORDER BY stock ASC");
    $pocoStock = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Medicamentos más caros
    $stmt = $conn->query("SELECT id, nombre, precio, categoria FROM medicamentos ORDER BY precio DESC LIMIT 5");
    $masCaros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Valor total del inventario
    $stmt = $conn->query("SELECT SUM(precio * stock) as valor_total FROM medicamentos");
    $valorInventario = $stmt->fetchColumn();
    
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medibot - Panel de Administración</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="shortcut icon" href="../img/logo-medibot.ico" type="medibot-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Estilos específicos para el dashboard */
        .dashboard-container {
            padding: 1.5rem;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .dashboard-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d9488;
        }
        
        .dashboard-actions {
            display: flex;
            gap: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
        }
        
        .stat-info h3 {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        
        .stat-info p {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .chart-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .chart-title {
            font-size: 1rem;
            font-weight: bold;
            color: #1f2937;
        }
        
        .chart-container {
            position: relative;
            height: 250px;
        }
        
        .table-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .table-title {
            font-size: 1rem;
            font-weight: bold;
            color: #1f2937;
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
        
        /* Responsive */
        @media (min-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 768px) {
            .charts-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        /* Responsive fixes for mobile */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .dashboard-actions {
                margin-top: 1rem;
                width: 100%;
                justify-content: space-between;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                font-size: 0.875rem;
            }
            
            .chart-container {
                height: 200px;
            }
        }
        
        @media (max-width: 480px) {
            .stat-info p {
                font-size: 1.25rem;
            }
            
            .stat-icon {
                width: 2.5rem;
                height: 2.5rem;
                font-size: 0.875rem;
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
            <a href="dashboard.php" class="sidebar-menu-item active">
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
            <div class="page-title">Dashboard</div>
            
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
        
        <!-- Dashboard Content -->
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div class="dashboard-title">Resumen del Sistema</div>
                <div class="dashboard-actions">
                    <button class="btn btn-outline" onclick="window.print()">
                        <i class="fas fa-print mr-2"></i> Imprimir
                    </button>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt mr-2"></i> Actualizar
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #0d9488;">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Medicamentos</h3>
                        <p><?php echo $totalMedicamentos; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #0369a1;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Usuarios</h3>
                        <p><?php echo $totalUsuarios; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #7c3aed;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pedidos</h3>
                        <p><?php echo $totalPedidos; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #15803d;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Valor del Inventario</h3>
                        <p>$<?php echo number_format($valorInventario, 2); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Medicamentos por Categoría</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="categoriasChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Medicamentos más Caros</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="preciosChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Tables -->
            <div class="table-card">
                <div class="table-header">
                    <div class="table-title">Medicamentos con Poco Stock</div>
                    <a href="medicamentos.php" class="btn btn-outline btn-sm">Ver Todos</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Stock</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pocoStock) > 0): ?>
                            <?php foreach ($pocoStock as $med): ?>
                                <tr>
                                    <td><?php echo $med['id']; ?></td>
                                    <td><?php echo $med['nombre']; ?></td>
                                    <td><?php echo $med['categoria']; ?></td>
                                    <td><?php echo $med['stock']; ?></td>
                                    <td>
                                        <?php if ($med['stock'] < 10): ?>
                                            <span class="stock-badge stock-low">Crítico</span>
                                        <?php elseif ($med['stock'] < 20): ?>
                                            <span class="stock-badge stock-medium">Bajo</span>
                                        <?php else: ?>
                                            <span class="stock-badge stock-good">Regular</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No hay medicamentos con poco stock</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // Datos para los gráficos
        const categoriasData = {
            labels: [<?php echo implode(', ', array_map(function($cat) { return '"' . $cat['categoria'] . '"'; }, $categorias)); ?>],
            datasets: [{
                label: 'Cantidad',
                data: [<?php echo implode(', ', array_map(function($cat) { return $cat['cantidad']; }, $categorias)); ?>],
                backgroundColor: [
                    '#0d9488',
                    '#0369a1',
                    '#7c3aed',
                    '#15803d',
                    '#b45309',
                    '#be123c',
                    '#4338ca'
                ],
                borderWidth: 0
            }]
        };
        
        const preciosData = {
            labels: [<?php echo implode(', ', array_map(function($med) { return '"' . $med['nombre'] . '"'; }, $masCaros)); ?>],
            datasets: [{
                label: 'Precio ($)',
                data: [<?php echo implode(', ', array_map(function($med) { return $med['precio']; }, $masCaros)); ?>],
                backgroundColor: '#0d9488',
                borderColor: '#0d9488',
                borderWidth: 1
            }]
        };
        
        // Configuración de los gráficos
        window.onload = function() {
            // Gráfico de categorías
            const ctxCategorias = document.getElementById('categoriasChart').getContext('2d');
            new Chart(ctxCategorias, {
                type: 'doughnut',
                data: categoriasData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
            
            // Gráfico de precios
            const ctxPrecios = document.getElementById('preciosChart').getContext('2d');
            new Chart(ctxPrecios, {
                type: 'bar',
                data: preciosData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        };
    </script>
</body>
</html>
