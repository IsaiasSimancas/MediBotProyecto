<?php
// Este archivo crea las tablas necesarias en la base de datos

// Incluir archivo de conexión
require_once 'database.php';

try {
    // Crear tabla de usuarios
    $sql = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        rol ENUM('usuario', 'administrador', 'superusuario') DEFAULT 'usuario',
        token_recuperacion VARCHAR(255) DEFAULT NULL,
        token_expiracion DATETIME DEFAULT NULL,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Tabla 'usuarios' creada o ya existente.<br>";
    
    // Crear tabla de medicamentos
    $sql = "CREATE TABLE IF NOT EXISTS medicamentos (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT NOT NULL,
        precio DECIMAL(10,2) NOT NULL,
        imagen VARCHAR(255) DEFAULT NULL,
        categoria VARCHAR(50) NOT NULL,
        stock INT(11) NOT NULL DEFAULT 0,
        dosificacion TEXT,
        principio_activo VARCHAR(100),
        presentacion VARCHAR(100),
        laboratorio VARCHAR(100),
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Tabla 'medicamentos' creada o ya existente.<br>";
    
    // Crear tabla de pedidos
    $sql = "CREATE TABLE IF NOT EXISTS pedidos (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT(11) NOT NULL,
        fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        estado ENUM('pendiente', 'procesando', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
        total DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )";
    
    $conn->exec($sql);
    echo "Tabla 'pedidos' creada o ya existente.<br>";
    
    // Crear tabla de detalles de pedido
    $sql = "CREATE TABLE IF NOT EXISTS detalles_pedido (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT(11) NOT NULL,
        medicamento_id INT(11) NOT NULL,
        cantidad INT(11) NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
        FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id)
    )";
    
    $conn->exec($sql);
    echo "Tabla 'detalles_pedido' creada o ya existente.<br>";
    
    // Crear superusuario por defecto
    $nombre = "Administrador";
    $email = "danielandresvasquezsantiago@gmail.com";
    $password = password_hash("admin123", PASSWORD_DEFAULT); // Contraseña encriptada
    $rol = "superusuario";
    
    // Verificar si ya existe el superusuario
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() == 0) {
        // Insertar superusuario
        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nombre, $email, $password, $rol]);
        
        echo "Superusuario creado correctamente.<br>";
    } else {
        echo "El superusuario ya existe.<br>";
    }
    
    // Datos de ejemplo para los medicamentos
    $medicamentos = [
        [
            'nombre' => 'Paracetamol 500mg',
            'descripcion' => 'Analgésico y antipirético para aliviar el dolor y reducir la fiebre. Indicado para el tratamiento sintomático del dolor leve a moderado y estados febriles. Cada tableta contiene 500mg de paracetamol como ingrediente activo.',
            'precio' => 5.99,
            'imagen' => 'img/placeholder.jpg',
            'categoria' => 'Analgésicos',
            'stock' => 150,
            'dosificacion' => '1-2 tabletas cada 6-8 horas según sea necesario. No exceder 8 tabletas en 24 horas.',
            'principio_activo' => 'Paracetamol (Acetaminofén)',
            'presentacion' => 'Caja con 20 tabletas',
            'laboratorio' => 'Farmacéutica Nacional'
        ],
        [
            'nombre' => 'Ibuprofeno 400mg',
            'descripcion' => 'Antiinflamatorio no esteroideo (AINE) para aliviar el dolor, reducir la inflamación y bajar la fiebre. Indicado para dolores musculares, articulares, menstruales, de cabeza y dentales. Cada tableta contiene 400mg de ibuprofeno como ingrediente activo.',
            'precio' => 7.50,
            'imagen' => 'img/placeholder.jpg',
            'categoria' => 'Antiinflamatorios',
            'stock' => 120,
            'dosificacion' => '1 tableta cada 6-8 horas según sea necesario. No exceder 3 tabletas en 24 horas.',
            'principio_activo' => 'Ibuprofeno',
            'presentacion' => 'Caja con 30 tabletas',
            'laboratorio' => 'Laboratorios Médicos'
        ],
        [
            'nombre' => 'Omeprazol 20mg',
            'descripcion' => 'Inhibidor de la bomba de protones que reduce la producción de ácido en el estómago. Indicado para el tratamiento de úlceras gástricas y duodenales, reflujo gastroesofágico y síndrome de Zollinger-Ellison. Cada cápsula contiene 20mg de omeprazol como ingrediente activo.',
            'precio' => 12.75,
            'imagen' => 'img/placeholder.jpg',
            'categoria' => 'Gastrointestinal',
            'stock' => 80,
            'dosificacion' => '1 cápsula diaria, preferiblemente antes del desayuno.',
            'principio_activo' => 'Omeprazol',
            'presentacion' => 'Caja con 14 cápsulas',
            'laboratorio' => 'Farmacéutica Global'
        ],
        [
            'nombre' => 'Loratadina 10mg',
            'descripcion' => 'Antihistamínico de segunda generación que alivia los síntomas de alergias como rinitis alérgica y urticaria. No causa somnolencia significativa. Cada tableta contiene 10mg de loratadina como ingrediente activo.',
            'precio' => 8.99,
            'imagen' => 'img/placeholder.jpg',
            'categoria' => 'Antialérgicos',
            'stock' => 100,
            'dosificacion' => '1 tableta diaria.',
            'principio_activo' => 'Loratadina',
            'presentacion' => 'Caja con 10 tabletas',
            'laboratorio' => 'Laboratorios Alergia'
        ],
        [
            'nombre' => 'Amoxicilina 500mg',
            'descripcion' => 'Antibiótico de amplio espectro del grupo de las penicilinas. Indicado para el tratamiento de infecciones bacterianas del tracto respiratorio, urinario, piel y tejidos blandos. Cada cápsula contiene 500mg de amoxicilina como ingrediente activo.',
            'precio' => 15.50,
            'imagen' => 'img/placeholder.jpg',
            'categoria' => 'Antibióticos',
            'stock' => 60,
            'dosificacion' => '1 cápsula cada 8 horas durante 7-10 días o según prescripción médica.',
            'principio_activo' => 'Amoxicilina',
            'presentacion' => 'Caja con 21 cápsulas',
            'laboratorio' => 'Laboratorios Antibióticos'
        ],
        [
            'nombre' => 'Vitamina C 1000mg',
            'descripcion' => 'Suplemento vitamínico que ayuda a reforzar el sistema inmunológico y contribuye a la formación de colágeno. Indicado para prevenir deficiencias de vitamina C y como complemento en tratamientos de resfriados. Cada tableta contiene 1000mg de ácido ascórbico como ingrediente activo.',
            'precio' => 9.25,
            'imagen' => 'img/placeholder.jpg',
            'categoria' => 'Vitaminas',
            'stock' => 200,
            'dosificacion' => '1 tableta diaria.',
            'principio_activo' => 'Ácido Ascórbico',
            'presentacion' => 'Frasco con 30 tabletas',
            'laboratorio' => 'Laboratorios Nutricionales'
        ]
    ];
    
    // Verificar si ya existen medicamentos
    $stmt = $conn->prepare("SELECT COUNT(*) FROM medicamentos");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insertar medicamentos de ejemplo
        $sql = "INSERT INTO medicamentos (nombre, descripcion, precio, imagen, categoria, stock, dosificacion, principio_activo, presentacion, laboratorio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        foreach ($medicamentos as $med) {
            $stmt->execute([
                $med['nombre'],
                $med['descripcion'],
                $med['precio'],
                $med['imagen'],
                $med['categoria'],
                $med['stock'],
                $med['dosificacion'],
                $med['principio_activo'],
                $med['presentacion'],
                $med['laboratorio']
            ]);
        }
        
        echo "Medicamentos de ejemplo insertados correctamente.<br>";
    } else {
        echo "Ya existen medicamentos en la base de datos.<br>";
    }
    
    echo "<br><strong>Configuración de la base de datos completada correctamente.</strong>";
    
} catch(PDOException $e) {
    die("Error en la configuración: " . $e->getMessage());
}
?>
