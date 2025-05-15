<?php
// Iniciar sesión para manejar el estado del usuario
session_start();

// Incluir archivo de conexión a la base de datos
require_once 'config/database.php';

// Determinar si el usuario está logueado
$logueado = isset($_SESSION['usuario_id']);
$es_admin = isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1;

// Parámetros de paginación y filtrado
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$productos_por_pagina = 8;
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Medibot</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/producto.css">
    <link rel="shortcut icon" href="img/logo-medibot.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/chatbot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <div class="logo-container">
                <a href="producto.php" class="logo">
                    <img src="img/logofarmacia1.png" alt="Logo Farmacia">
                    <!-- <span>Medibot</span> -->
                </a>
            </div>
            
            <div class="search-container">
                <form action="productos.php" method="GET" id="search-form">
                    <input type="text" name="busqueda" placeholder="Buscar medicamentos..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <nav class="nav-container">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <ul class="nav-menu" id="nav-menu">
                    <li><a href="producto.php">Inicio</a></li>
                    <!-- <li><a href="productos.php" class="active">Productos</a></li> -->
                    <?php if ($logueado): ?>
                        <li><a href=""><i class="fas fa-shopping-cart"></i> Carrito</a></li>
                        <li><a href="user/perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                        <li><a href="api/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
                    <?php else: ?>
                        <!-- <li><a href="login.php" class="btn-login">Iniciar Sesión</a></li> -->
                        <li><a href="index.html" class="btn-register">Iniciar Sesión</a></li>
                    <?php endif; ?>
                    
                    <?php if ($es_admin): ?>
                        <li><a href="admin/dashboard.php" class="btn-admin"><i class="fas fa-cog"></i> Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Nuestros Productos</h1>
            
            <div class="filtros-container">
                <div class="categorias">
                    <h3>Categorías</h3>
                    <ul id="lista-categorias">
                        <li><a href="productos.php" class="<?php echo empty($categoria) ? 'active' : ''; ?>">Todas</a></li>
                        <!-- Las categorías se cargarán dinámicamente con JavaScript -->
                        <li class="loading-item"><span>Cargando categorías...</span></li>
                    </ul>
                </div>
                
                <div class="productos-grid" id="productos-container">
                    <!-- Aquí se cargarán los productos dinámicamente -->
                    <div class="loading-container">
                        <div class="loading-spinner"></div>
                        <p>Cargando productos...</p>
                    </div>
                </div>
            </div>
            
            <div class="paginacion" id="paginacion">
                <!-- La paginación se generará dinámicamente -->
            </div>
        </div>
    </main>

    <!-- Modal para detalles del producto -->
    <div id="producto-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div id="modal-producto-detalle">
                <!-- Contenido del modal cargado dinámicamente -->
            </div>
        </div>
    </div>

    <script src="js/productos.js"></script>
    <script src="js/chatbot.js"></script>
</body>
</html>
