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

// Obtener medicamentos destacados (los 4 más recientes)
try {
    $stmt = $conn->prepare("SELECT * FROM medicamentos ORDER BY fecha_creacion DESC LIMIT 4");
    $stmt->execute();
    $medicamentos_destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// Obtener categorías de medicamentos
try {
    $stmt = $conn->prepare("SELECT DISTINCT categoria FROM medicamentos ORDER BY categoria");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
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
    <title>FarmaExpress - Dashboard</title>
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
        
        .hero {
            background-color: #0f766e;
            color: white;
            padding: 3rem 1rem;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #0f766e;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 0.5rem;
        }
        
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .card {
            background-color: white;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .card-text {
            color: #666;
            margin-bottom: 1rem;
        }
        
        .card-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #0f766e;
            margin-bottom: 1rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: #0f766e;
            color: white;
            border: none;
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
        
        .categories {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .category-badge {
            background-color: white;
            color: #0f766e;
            border: 1px solid #0f766e;
            border-radius: 2rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
        }
        
        .category-badge:hover, .category-badge.active {
            background-color: #0f766e;
            color: white;
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
            
            .hero {
                padding: 2rem 1rem;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
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
    
    <div class="hero">
        <h1>Bienvenido a FarmaExpress</h1>
        <p>Tu farmacia en línea con los mejores medicamentos y al mejor precio.</p>
    </div>
    
    <div class="container">
        <h2 class="section-title">Categorías de Medicamentos</h2>
        
        <div class="categories">
            <a href="medicamentos.php" class="category-badge active">Todos</a>
            <?php foreach ($categorias as $categoria): ?>
            <a href="medicamentos.php?categoria=<?php echo urlencode($categoria); ?>" class="category-badge"><?php echo htmlspecialchars($categoria); ?></a>
            <?php endforeach; ?>
        </div>
        
        <h2 class="section-title">Medicamentos Destacados</h2>
        
        <div class="card-grid">
            <?php foreach ($medicamentos_destacados as $med): ?>
            <div class="card">
                <img src="<?php echo !empty($med['imagen']) ? '../' . $med['imagen'] : '../img/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($med['nombre']); ?>" class="card-img">
                <div class="card-body">
                    <h3 class="card-title"><?php echo htmlspecialchars($med['nombre']); ?></h3>
                    <p class="card-text"><?php echo substr(htmlspecialchars($med['descripcion']), 0, 100) . '...'; ?></p>
                    <p class="card-price">$<?php echo number_format($med['precio'], 2); ?></p>
                    <div class="d-flex">
                        <a href="medicamento.php?id=<?php echo $med['id']; ?>" class="btn btn-outline" style="margin-right: 0.5rem;">Ver Detalles</a>
                        <form action="../api/agregar-carrito.php" method="POST" style="display:inline;">
                            <input type="hidden" name="medicamento_id" value="<?php echo $med['id']; ?>">
                            <input type="hidden" name="cantidad" value="1">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-cart-plus"></i> Añadir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center" style="margin-top: 2rem;">
            <a href="medicamentos.php" class="btn btn-primary">Ver Todos los Medicamentos</a>
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
</body>
</html>
