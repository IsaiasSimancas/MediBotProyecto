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

// Obtener categorías de medicamentos
try {
    $stmt = $conn->prepare("SELECT DISTINCT categoria FROM medicamentos ORDER BY categoria");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// Filtrar por categoría si se proporciona
$filtro_categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

// Consulta de medicamentos con filtros
try {
    $sql = "SELECT * FROM medicamentos";
    $params = [];
    
    if (!empty($filtro_categoria)) {
        $sql .= " WHERE categoria = ?";
        $params[] = $filtro_categoria;
    }
    
    $sql .= " ORDER BY nombre ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
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
    <title>FarmaExpress - Medicamentos</title>
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
        
        .search-container {
            display: flex;
            align-items: center;
            background-color: white;
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem;
        }
        
        .search-input {
            border: none;
            padding: 0.5rem;
            outline: none;
            width: 250px;
        }
        
        .search-btn {
            background: none;
            border: none;
            color: #0f766e;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.5rem;
            color: #0f766e;
            margin-bottom: 0.5rem;
        }
        
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .filter-badge {
            background-color: white;
            color: #0f766e;
            border: 1px solid #0f766e;
            border-radius: 2rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .filter-badge:hover, .filter-badge.active {
            background-color: #0f766e;
            color: white;
        }
        
        .medicamentos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .medicamento-card {
            background-color: white;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .medicamento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .medicamento-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .medicamento-body {
            padding: 1.5rem;
        }
        
        .medicamento-categoria {
            display: inline-block;
            font-size: 0.75rem;
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
        }
        
        .medicamento-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #0f766e;
        }
        
        .medicamento-description {
            color: #6b7280;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .medicamento-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #0f766e;
            margin-bottom: 1rem;
        }
        
        .medicamento-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.2s;
            border: none;
            font-size: 0.875rem;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .btn-primary {
            background-color: #0f766e;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0d9488;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #0f766e;
            color: #0f766e;
        }
        
        .btn-outline:hover {
            background-color: #0f766e;
            color: white;
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
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            color: #9ca3af;
            margin-bottom: 1rem;
        }
        
        .empty-state-title {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .empty-state-message {
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 1rem 0;
            }
            
            .navbar-brand {
                margin-bottom: 1rem;
            }
            
            .navbar-nav {
                width: 100%;
                justify-content: space-around;
                padding: 0 1rem;
            }
            
            .search-container {
                margin: 1rem 0;
                width: 100%;
            }
            
            .search-input {
                width: 100%;
            }
            
            .medicamentos-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">FarmaExpress</a>
        
        <form action="buscar.php" method="GET" class="search-container">
            <input type="text" name="q" placeholder="Buscar medicamentos..." class="search-input">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </form>
        
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
        <div class="page-header">
            <h1 class="page-title">Medicamentos</h1>
            <p>Encuentra los medicamentos que necesitas en nuestra amplia selección</p>
        </div>
        
        <div class="filter-container">
            <a href="medicamentos.php" class="filter-badge <?php echo empty($filtro_categoria) ? 'active' : ''; ?>">Todos</a>
            <?php foreach ($categorias as $categoria): ?>
            <a href="medicamentos.php?categoria=<?php echo urlencode($categoria); ?>" class="filter-badge <?php echo ($filtro_categoria === $categoria) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($categoria); ?>
            </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($medicamentos) > 0): ?>
        <div class="medicamentos-grid">
            <?php foreach ($medicamentos as $med): ?>
            <div class="medicamento-card">
                <img src="<?php echo !empty($med['imagen']) ? '../' . $med['imagen'] : '../img/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($med['nombre']); ?>" class="medicamento-img">
                <div class="medicamento-body">
                    <span class="medicamento-categoria"><?php echo htmlspecialchars($med['categoria']); ?></span>
                    <h3 class="medicamento-title"><?php echo htmlspecialchars($med['nombre']); ?></h3>
                    <p class="medicamento-description"><?php echo htmlspecialchars($med['descripcion']); ?></p>
                    <p class="medicamento-price">$<?php echo number_format($med['precio'], 2); ?></p>
                    <div class="medicamento-actions">
                        <a href="medicamento.php?id=<?php echo $med['id']; ?>" class="btn btn-outline" style="flex: 1;">Ver Detalles</a>
                        <form action="../api/agregar-carrito.php" method="POST" style="flex: 1;">
                            <input type="hidden" name="medicamento_id" value="<?php echo $med['id']; ?>">
                            <input type="hidden" name="cantidad" value="1">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-cart-plus"></i> Añadir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-pills"></i>
            </div>
            <h3 class="empty-state-title">No se encontraron medicamentos</h3>
            <p class="empty-state-message">No hay medicamentos disponibles en esta categoría o con los filtros seleccionados.</p>
            <a href="medicamentos.php" class="btn btn-primary">Ver todos los medicamentos</a>
        </div>
        <?php endif; ?>
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
</body>
</html>
