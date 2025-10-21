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
  rol ENUM('admin','dueno','cliente') NOT NULL,
  categoria ENUM('Inicial','Medium','Premium') DEFAULT 'Inicial',
  estado ENUM('pendiente','activo') DEFAULT 'activo',
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Tabla: locales
-- =====================================================
CREATE TABLE locales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  rubro VARCHAR(100),
  codigo VARCHAR(10) UNIQUE,
  id_dueno INT,
  FOREIGN KEY (id_dueno) REFERENCES usuarios(id) ON DELETE SET NULL
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
  dias_vigencia VARCHAR(50),
  categoria_minima ENUM('Inicial','Medium','Premium'),
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
  categoria_destino ENUM('Inicial','Medium','Premium'),
  fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_vencimiento DATE
);

-- =====================================================
-- DATOS INICIALES
-- =====================================================

-- ADMINISTRADOR BASE
INSERT INTO usuarios (nombre, email, password, rol, categoria, estado)
VALUES 
('Administrador General', 'admin@ofertopolis.com', MD5('admin123'), 'admin', 'Premium', 'activo');

-- DUEÑOS DE LOCALES
INSERT INTO usuarios (nombre, email, password, rol, categoria, estado)
VALUES
('Dueño Tienda Moda', 'dueno1@ofertopolis.com', MD5('dueno123'), 'dueno', 'Premium', 'activo'),
('Dueño Café Central', 'dueno2@ofertopolis.com', MD5('dueno123'), 'dueno', 'Premium', 'activo');

-- CLIENTES
INSERT INTO usuarios (nombre, email, password, rol, categoria, estado)
VALUES
('María López', 'cliente1@ofertopolis.com', MD5('cliente123'), 'cliente', 'Inicial', 'activo'),
('Juan Pérez', 'cliente2@ofertopolis.com', MD5('cliente123'), 'cliente', 'Medium', 'activo'),
('Lucía Fernández', 'cliente3@ofertopolis.com', MD5('cliente123'), 'cliente', 'Premium', 'activo');

-- =====================================================
-- LOCALES
-- =====================================================
INSERT INTO locales (nombre, rubro, codigo, id_dueno)
VALUES
('Tienda Moda', 'Indumentaria', 'LOC001', 2),
('Café Central', 'Gastronomía', 'LOC002', 3);

-- =====================================================
-- PROMOCIONES
-- =====================================================
INSERT INTO promociones (id_local, titulo, descripcion, fecha_inicio, fecha_fin, dias_vigencia, categoria_minima, estado)
VALUES
(1, '2x1 en Jeans', 'Llevá dos jeans y pagá uno. Válido lunes a miércoles.', '2025-10-01', '2025-12-31', 'Lunes,Martes,Miércoles', 'Inicial', 'aprobada'),
(1, '20% en remeras', 'Descuento del 20% en remeras para clientes Medium y Premium.', '2025-10-15', '2025-12-31', 'Todos', 'Medium', 'aprobada'),
(2, 'Café + Medialuna $1200', 'Promo desayuno. Todos los días de 8 a 11.', '2025-10-01', '2025-12-31', 'Lunes,Martes,Miércoles,Jueves,Viernes', 'Inicial', 'aprobada'),
(2, '3x2 en tortas', 'Pagás dos tortas y te llevás tres.', '2025-10-20', '2025-11-30', 'Viernes,Sábado,Domingo', 'Premium', 'pendiente');

-- =====================================================
-- SOLICITUDES (Clientes solicitando promos)
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
('Nuevo Horario del Shopping', 'A partir de noviembre, abrimos de 9 a 22 hs todos los días.', 'Inicial', '2025-12-31'),
('Evento de Black Friday', 'Descuentos especiales en todos los locales este viernes 29.', 'Medium', '2025-11-30'),
('Exclusivo Premium Night', 'Acceso anticipado a las mejores ofertas solo para usuarios Premium.', 'Premium', '2025-11-15');
