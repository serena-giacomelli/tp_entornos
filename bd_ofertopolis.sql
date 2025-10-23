-- =====================================================
-- Base de Datos: ofertopolis
-- Proyecto: Entornos Gráficos - UTN FRSR
-- Desarrolladores: Alaniz, Giacomelli
-- =====================================================

DROP DATABASE IF EXISTS ofertopolis;
CREATE DATABASE ofertopolis CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ofertopolis;

-- =====================================================
-- Tabla: usuarios
-- =====================================================
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','duenio','cliente') NOT NULL,
  categoria ENUM('inicial','medium','premium') DEFAULT 'inicial',
  estado ENUM('pendiente','activo') DEFAULT 'activo',
  estado_cuenta ENUM('pendiente', 'activo', 'denegado') DEFAULT 'pendiente',
  token_validacion VARCHAR(100) DEFAULT NULL,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Tabla: locales
-- =====================================================
CREATE TABLE locales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  rubro VARCHAR(100),
  id_duenio INT,
  FOREIGN KEY (id_duenio) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- =====================================================
-- Tabla: promociones
-- =====================================================
CREATE TABLE promociones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_local INT,
  titulo VARCHAR(150),
  descripcion TEXT,
  fecha_inicio DATE,
  fecha_fin DATE,
  dias_vigencia VARCHAR(100),
  categoria_minima ENUM('inicial','medium','premium'),
  estado ENUM('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  FOREIGN KEY (id_local) REFERENCES locales(id) ON DELETE CASCADE
);

-- =====================================================
-- Tabla: solicitudes
-- =====================================================
CREATE TABLE solicitudes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT,
  id_promo INT,
  estado ENUM('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_cliente) REFERENCES usuarios(id),
  FOREIGN KEY (id_promo) REFERENCES promociones(id)
);

-- =====================================================
-- Tabla: novedades
-- =====================================================
CREATE TABLE novedades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150),
  contenido TEXT,
  categoria_destino ENUM('inicial','medium','premium'),
  fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_vencimiento DATE
);

-- =====================================================
-- Tabla: uso_promociones
-- =====================================================
CREATE TABLE uso_promociones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_promo INT NOT NULL,
  fecha_uso DATE DEFAULT (CURRENT_DATE),
  estado ENUM('enviada', 'aceptada', 'rechazada') DEFAULT 'enviada',
  FOREIGN KEY (id_cliente) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (id_promo) REFERENCES promociones(id) ON DELETE CASCADE
);

-- =====================================================
-- Tabla: contactos
-- =====================================================
CREATE TABLE contactos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  mensaje TEXT NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- DATOS INICIALES
-- =====================================================

-- ADMINISTRADOR BASE
INSERT INTO usuarios (nombre, email, password, rol, categoria, estado, estado_cuenta)
VALUES 
('Administrador General', 'admin@ofertopolis.com', MD5('admin123'), 'admin', 'premium', 'activo', 'activo');

-- DUEÑOS DE LOCALES
INSERT INTO usuarios (nombre, email, password, rol, categoria, estado, estado_cuenta)
VALUES
('Dueño Tienda Moda', 'duenio1@ofertopolis.com', MD5('duenio123'), 'duenio', 'premium', 'activo', 'activo'),
('Dueño Café Central', 'duenio2@ofertopolis.com', MD5('duenio123'), 'duenio', 'premium', 'activo', 'activo');

-- CLIENTES
INSERT INTO usuarios (nombre, email, password, rol, categoria, estado, estado_cuenta)
VALUES
('María López', 'cliente1@ofertopolis.com', MD5('cliente123'), 'cliente', 'inicial', 'activo', 'activo'),
('Juan Pérez', 'cliente2@ofertopolis.com', MD5('cliente123'), 'cliente', 'medium', 'activo', 'activo'),
('Lucía Fernández', 'cliente3@ofertopolis.com', MD5('cliente123'), 'cliente', 'premium', 'activo', 'activo');

-- =====================================================
-- LOCALES
-- =====================================================
INSERT INTO locales (nombre, rubro, id_duenio)
VALUES
('Tienda Moda', 'indumentaria', 2),
('Café Central', 'gastronomía', 3);

-- =====================================================
-- PROMOCIONES
-- =====================================================
INSERT INTO promociones (id_local, titulo, descripcion, fecha_inicio, fecha_fin, dias_vigencia, categoria_minima, estado)
VALUES
(1, '2x1 en jeans', 'Llevá dos jeans y pagá uno. Válido lunes a miércoles.', '2025-10-01', '2025-12-31', 'lunes,martes,miércoles', 'inicial', 'aprobada'),
(1, '20% en remeras', 'Descuento del 20% en remeras para clientes Medium y Premium.', '2025-10-15', '2025-12-31', 'todos', 'medium', 'aprobada'),
(2, 'Café + medialuna $1200', 'Promo desayuno. Todos los días de 8 a 11.', '2025-10-01', '2025-12-31', 'lunes,martes,miércoles,jueves,viernes', 'inicial', 'aprobada'),
(2, '3x2 en tortas', 'Pagás dos tortas y te llevás tres.', '2025-10-20', '2025-11-30', 'viernes,sábado,domingo', 'premium', 'pendiente');

-- =====================================================
-- SOLICITUDES
-- =====================================================
INSERT INTO solicitudes (id_cliente, id_promo, estado)
VALUES
(4, 1, 'aceptada'),
(5, 2, 'pendiente'),
(6, 3, 'rechazada');

-- =====================================================
-- NOVEDADES
-- =====================================================
INSERT INTO novedades (titulo, contenido, categoria_destino, fecha_vencimiento)
VALUES
('Nuevo horario del shopping', 'A partir de noviembre, abrimos de 9 a 22 hs todos los días.', 'inicial', '2025-12-31'),
('Evento de black friday', 'Descuentos especiales en todos los locales este viernes 29.', 'medium', '2025-11-30'),
('Exclusivo premium night', 'Acceso anticipado a las mejores ofertas solo para usuarios premium.', 'premium', '2025-11-15');
