-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS database_medibot;

-- Seleccionar la base de datos
USE database_medibot;

-- Crear tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('usuario', 'administrador', 'superusuario') DEFAULT 'usuario',
    token_recuperacion VARCHAR(255) DEFAULT NULL,
    token_expiracion DATETIME DEFAULT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla de medicamentos
CREATE TABLE IF NOT EXISTS medicamentos (
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
);

-- Crear tabla de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT(11) NOT NULL,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'procesando', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Crear tabla de detalles de pedido
CREATE TABLE IF NOT EXISTS detalles_pedido (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT(11) NOT NULL,
    medicamento_id INT(11) NOT NULL,
    cantidad INT(11) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id)
);

-- Crear superusuario por defecto
-- Contraseña: admin123 (hash generado con password_hash en PHP)
INSERT INTO usuarios (nombre, email, password, rol) 
VALUES ('Administrador', 'danielandresvasquezsantiago@gmail.com', '$2y$10$8MILqZd4Y.Ew8/bVfGQSxOUVqnPLEOMnM1urO0OpdS7.IrE5m1.Hy', 'superusuario');

-- Insertar medicamentos de ejemplo
INSERT INTO medicamentos (nombre, descripcion, precio, imagen, categoria, stock, dosificacion, principio_activo, presentacion, laboratorio) VALUES
('Paracetamol 500mg', 'Analgésico y antipirético para aliviar el dolor y reducir la fiebre. Indicado para el tratamiento sintomático del dolor leve a moderado y estados febriles. Cada tableta contiene 500mg de paracetamol como ingrediente activo.', 5.99, 'img/placeholder.jpg', 'Analgésicos', 150, '1-2 tabletas cada 6-8 horas según sea necesario. No exceder 8 tabletas en 24 horas.', 'Paracetamol (Acetaminofén)', 'Caja con 20 tabletas', 'Farmacéutica Nacional'),
('Ibuprofeno 400mg', 'Antiinflamatorio no esteroideo (AINE) para aliviar el dolor, reducir la inflamación y bajar la fiebre. Indicado para dolores musculares, articulares, menstruales, de cabeza y dentales. Cada tableta contiene 400mg de ibuprofeno como ingrediente activo.', 7.50, 'img/placeholder.jpg', 'Antiinflamatorios', 120, '1 tableta cada 6-8 horas según sea necesario. No exceder 3 tabletas en 24 horas.', 'Ibuprofeno', 'Caja con 30 tabletas', 'Laboratorios Médicos'),
('Omeprazol 20mg', 'Inhibidor de la bomba de protones que reduce la producción de ácido en el estómago. Indicado para el tratamiento de úlceras gástricas y duodenales, reflujo gastroesofágico y síndrome de Zollinger-Ellison. Cada cápsula contiene 20mg de omeprazol como ingrediente activo.', 12.75, 'img/placeholder.jpg', 'Gastrointestinal', 80, '1 cápsula diaria, preferiblemente antes del desayuno.', 'Omeprazol', 'Caja con 14 cápsulas', 'Farmacéutica Global'),
('Loratadina 10mg', 'Antihistamínico de segunda generación que alivia los síntomas de alergias como rinitis alérgica y urticaria. No causa somnolencia significativa. Cada tableta contiene 10mg de loratadina como ingrediente activo.', 8.99, 'img/placeholder.jpg', 'Antialérgicos', 100, '1 tableta diaria.', 'Loratadina', 'Caja con 10 tabletas', 'Laboratorios Alergia'),
('Amoxicilina 500mg', 'Antibiótico de amplio espectro del grupo de las penicilinas. Indicado para el tratamiento de infecciones bacterianas del tracto respiratorio, urinario, piel y tejidos blandos. Cada cápsula contiene 500mg de amoxicilina como ingrediente activo.', 15.50, 'img/placeholder.jpg', 'Antibióticos', 60, '1 cápsula cada 8 horas durante 7-10 días o según prescripción médica.', 'Amoxicilina', 'Caja con 21 cápsulas', 'Laboratorios Antibióticos'),
('Vitamina C 1000mg', 'Suplemento vitamínico que ayuda a reforzar el sistema inmunológico y contribuye a la formación de colágeno. Indicado para prevenir deficiencias de vitamina C y como complemento en tratamientos de resfriados. Cada tableta contiene 1000mg de ácido ascórbico como ingrediente activo.', 9.25, 'img/placeholder.jpg', 'Vitaminas', 200, '1 tableta diaria.', 'Ácido Ascórbico', 'Frasco con 30 tabletas', 'Laboratorios Nutricionales');
