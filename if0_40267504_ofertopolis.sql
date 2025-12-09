-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql100.infinityfree.com
-- Tiempo de generación: 09-12-2025 a las 08:48:29
-- Versión del servidor: 10.6.22-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_40267504_ofertopolis`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contactos`
--

INSERT INTO `contactos` (`id`, `nombre`, `email`, `mensaje`, `fecha`) VALUES
(1, 'Roman Juarez', 'sere23giacomelli@gmail.com', 'Quiero una explicacion sobre las politicas de las categorias de los clientes', '2025-10-27 20:21:02'),
(2, 'Roman Juarez', 'sere23giacomelli@gmail.com', 'Quiero una explicacion sobre las politicas de las categorias de los clientes', '2025-10-27 20:22:41'),
(3, 'Carla', 'sere22giacomelli@gmail.com', 'sssssssssssssss', '2025-10-27 20:30:58'),
(4, 'serena', 'sere23giacomelli@gmail.com', 'ffffff', '2025-10-27 22:54:59'),
(5, 'bruno', 'sere22giacomelli@gmail.com', 'fffffffffffffff', '2025-10-27 23:12:51'),
(6, 'Daniela', 'danyelisabet@gmail.com', 'zzxxxx', '2025-12-01 12:59:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `locales`
--

CREATE TABLE `locales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ubicacion` varchar(50) DEFAULT NULL,
  `rubro` varchar(100) DEFAULT NULL,
  `id_duenio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `locales`
--

INSERT INTO `locales` (`id`, `nombre`, `ubicacion`, `rubro`, `id_duenio`) VALUES
(1, 'Tienda Moda', 'Planta Baja - Local 12', 'indumentaria', 2),
(2, 'Café Central', 'Primer Piso - Local 45', 'gastronomía', 3),
(3, 'Librería Cultural', 'Planta Baja - Local 8', 'librería', 11),
(4, 'Tech Store', 'Primer Piso - Local 23', 'tecnología', NULL),
(5, 'Zapatería Premium', 'Planta Baja - Local 15', 'calzado', 30),
(6, 'Heladería Italiana', 'Planta Baja - Local 5', 'gastronomía', 11),
(7, 'Joyería Elegance', 'Primer Piso - Local 34', 'joyería', 11),
(8, 'Perfumería Aromas', 'Planta Baja - Local 18', 'perfumería', NULL),
(9, 'Deportes Total', 'Segundo Piso - Local 56', 'deportes', NULL),
(10, 'Juguetería Mágica', 'Segundo Piso - Local 62', 'juguetería', NULL),
(11, 'Entornos', 'Zeballos 1341', 'Indumentaria', 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `novedades`
--

CREATE TABLE `novedades` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `contenido` text DEFAULT NULL,
  `categoria_destino` enum('inicial','medium','premium') DEFAULT NULL,
  `fecha_publicacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_vencimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `novedades`
--

INSERT INTO `novedades` (`id`, `titulo`, `contenido`, `categoria_destino`, `fecha_publicacion`, `fecha_vencimiento`) VALUES
(1, 'Nuevo horario del shopping', 'A partir de noviembre, abrimos de 9 a 22 hs todos los días.', 'inicial', '2025-10-26 16:21:19', '2025-12-31'),
(2, 'Evento de black friday', 'Descuentos especiales en todos los locales este viernes 29.', 'medium', '2025-10-26 16:21:19', '2025-11-30'),
(3, 'Exclusivo premium night', 'Acceso anticipado a las mejores ofertas solo para usuarios premium.', 'premium', '2025-10-26 16:21:19', '2025-11-15'),
(4, 'Estacionamiento gratuito', 'Durante todo octubre, estacionamiento sin cargo para clientes.', 'inicial', '2025-10-26 16:21:19', '2025-10-31'),
(5, 'Sorteo mensual', 'Participá del sorteo de $50.000 en compras. Válido para clientes Medium y Premium.', 'medium', '2025-10-26 16:21:19', '2025-11-20'),
(6, 'Nuevo local de tecnología', 'Tech Store ya está abierto en el Primer Piso. Visitanos!', 'inicial', '2025-10-26 16:21:19', NULL),
(7, 'Cyber Monday', 'Ofertas exclusivas online y en tienda este lunes 2 de diciembre.', 'inicial', '2025-10-26 16:21:19', '2025-12-02'),
(8, 'Beneficios Premium', 'Los clientes Premium ahora tienen 10% adicional en todos los locales.', 'premium', '2025-10-26 16:21:19', NULL),
(9, 'Zona infantil renovada', 'Nueva área de juegos para niños en la Planta Baja.', 'inicial', '2025-10-26 16:21:19', NULL),
(10, 'Horario especial festivos', 'Durante diciembre abrimos de 10 a 23 hs todos los días.', 'inicial', '2025-10-26 16:21:19', '2025-12-31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promociones`
--

CREATE TABLE `promociones` (
  `id` int(11) NOT NULL,
  `id_local` int(11) DEFAULT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `dias_vigencia` varchar(100) DEFAULT NULL,
  `categoria_minima` enum('inicial','medium','premium') DEFAULT NULL,
  `estado` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `promociones`
--

INSERT INTO `promociones` (`id`, `id_local`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_fin`, `dias_vigencia`, `categoria_minima`, `estado`) VALUES
(1, 1, '2x1 en jeans', 'Llevá dos jeans y pagá uno. Válido lunes a miércoles.', '2025-10-01', '2025-12-31', 'lunes,martes,miércoles', 'inicial', 'aprobada'),
(2, 1, '20% en remeras', 'Descuento del 20% en remeras para clientes Medium y Premium.', '2025-10-15', '2025-12-31', 'todos', 'medium', 'aprobada'),
(3, 2, 'Café + medialuna $1200', 'Promo desayuno. Todos los días de 8 a 11.', '2025-10-01', '2025-12-31', 'lunes,martes,miércoles,jueves,viernes', 'inicial', 'aprobada'),
(4, 2, '3x2 en tortas', 'Pagás dos tortas y te llevás tres.', '2025-10-20', '2025-11-30', 'viernes,sábado,domingo', 'premium', 'aprobada'),
(5, 3, '15% en libros', 'Descuento en toda la librería por el mes del libro.', '2025-10-25', '2025-11-30', 'todos', 'inicial', 'aprobada'),
(6, 4, 'Black Friday Tech', '30% en celulares y tablets seleccionados.', '2025-11-29', '2025-11-30', 'viernes,sábado', 'medium', 'aprobada'),
(7, 5, '2x1 en zapatillas', 'Compra dos pares de zapatillas por el precio de uno.', '2025-10-20', '2025-12-15', 'todos', 'inicial', 'aprobada'),
(8, 6, 'Helado gratis', 'Comprá 1kg de helado y llevá 1/2kg gratis.', '2025-10-15', '2025-11-15', 'todos', 'inicial', 'aprobada'),
(9, 7, '20% en joyas', 'Descuento especial en toda la joyería.', '2025-10-10', '2025-12-24', 'todos', 'premium', 'aprobada'),
(10, 8, '3x2 en perfumes', 'Llevá tres perfumes y pagá dos.', '2025-10-01', '2025-12-31', 'todos', 'medium', 'aprobada'),
(11, 9, '40% en ropa deportiva', 'Gran descuento en indumentaria deportiva.', '2025-11-01', '2025-11-30', 'todos', 'inicial', 'aprobada'),
(12, 10, 'Juguetes 2x1', 'Comprá dos juguetes y pagá uno.', '2025-11-15', '2025-12-24', 'todos', 'inicial', 'aprobada'),
(13, 1, '30% off en jeans', 'gggg', '2025-10-20', '2025-11-30', 'Miercoles, jueves y domingo', 'medium', 'aprobada'),
(14, 1, 'Entornos', 'Entornos Gráficos', '2025-12-03', '2025-12-23', 'Martes', 'inicial', 'aprobada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_promo` int(11) DEFAULT NULL,
  `estado` enum('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  `fecha_solicitud` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`id`, `id_cliente`, `id_promo`, `estado`, `fecha_solicitud`) VALUES
(1, 4, 1, 'aceptada', '2025-10-26 16:21:19'),
(2, 5, 2, 'pendiente', '2025-10-26 16:21:19'),
(3, 6, 3, 'rechazada', '2025-10-26 16:21:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uso_promociones`
--

CREATE TABLE `uso_promociones` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_promo` int(11) NOT NULL,
  `fecha_uso` date DEFAULT curdate(),
  `estado` enum('enviada','aceptada','rechazada') DEFAULT 'enviada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `uso_promociones`
--

INSERT INTO `uso_promociones` (`id`, `id_cliente`, `id_promo`, `fecha_uso`, `estado`) VALUES
(1, 6, 1, '2025-10-26', 'aceptada'),
(2, 4, 1, '2025-10-27', 'aceptada'),
(3, 4, 3, '2025-10-27', 'aceptada'),
(4, 27, 5, '2025-10-28', 'rechazada'),
(5, 27, 8, '2025-10-28', 'aceptada'),
(6, 29, 12, '2025-12-01', 'enviada'),
(7, 29, 7, '2025-12-02', 'enviada'),
(8, 5, 3, '2025-12-02', 'aceptada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','duenio','cliente') NOT NULL,
  `categoria` enum('inicial','medium','premium') DEFAULT 'inicial',
  `estado` enum('pendiente','activo') DEFAULT 'activo',
  `estado_cuenta` enum('pendiente','activo','denegado') DEFAULT 'pendiente',
  `token_validacion` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `categoria`, `estado`, `estado_cuenta`, `token_validacion`, `fecha_registro`) VALUES
(1, 'Administrador General', 'admin@ofertopolis.com', '0192023a7bbd73250516f069df18b500', 'admin', 'premium', 'activo', 'activo', NULL, '2025-10-26 16:21:19'),
(2, 'Dueño Tienda Moda', 'duenio1@ofertopolis.com', '8a0275c0b572a9425095d3aa5797af3d', 'duenio', 'premium', 'activo', 'activo', NULL, '2025-10-26 16:21:19'),
(3, 'Dueño Café Central', 'duenio2@ofertopolis.com', '8a0275c0b572a9425095d3aa5797af3d', 'duenio', 'premium', 'activo', 'activo', NULL, '2025-10-26 16:21:19'),
(4, 'María López', 'cliente1@ofertopolis.com', '7159bbe0c8ca2a67230a26b72dea7557', 'cliente', 'inicial', 'activo', 'activo', NULL, '2025-10-26 16:21:19'),
(5, 'Juan Pérez', 'cliente2@ofertopolis.com', '7159bbe0c8ca2a67230a26b72dea7557', 'cliente', 'inicial', 'activo', 'activo', NULL, '2025-10-26 16:21:19'),
(6, 'Lucía Fernández', 'cliente3@ofertopolis.com', '7159bbe0c8ca2a67230a26b72dea7557', 'cliente', 'inicial', 'activo', 'activo', NULL, '2025-10-26 16:21:19'),
(7, 'sere duenia', 'duenio20@ofertopolis.com', 'd18741c36c0a2d1e19155b03af96b431', 'duenio', 'inicial', 'activo', 'activo', NULL, '2025-10-27 13:45:18'),
(9, 'serena', 'duenio21@ofertopolis.com', 'd18741c36c0a2d1e19155b03af96b431', 'duenio', 'inicial', 'activo', 'activo', NULL, '2025-10-27 14:09:15'),
(10, 'duenia 22', 'duenio22@ofertopolis.com', 'd18741c36c0a2d1e19155b03af96b431', 'duenio', 'inicial', 'activo', 'activo', NULL, '2025-10-27 14:19:59'),
(11, 'duenia 23', 'duenia23@ofertopolis.com', 'd18741c36c0a2d1e19155b03af96b431', 'duenio', 'inicial', 'activo', 'activo', NULL, '2025-10-27 14:21:16'),
(17, 'Claudia Romero', 'sere23giacomelli@gmail.com', 'd18741c36c0a2d1e19155b03af96b431', 'cliente', 'inicial', 'activo', 'activo', NULL, '2025-10-27 20:25:31'),
(26, 'Bruno', 'brucascardo@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'cliente', 'inicial', 'activo', 'activo', NULL, '2025-10-27 23:11:26'),
(27, 'aixa ijia', 'aixaijia0105@gmail.com', '49d76ab8c140f4ffc64bf07d3aa370e1', 'cliente', 'inicial', 'activo', 'activo', NULL, '2025-10-28 15:58:56'),
(28, 'Celina Juarez', 'sere24giacomelli@gmail.com', 'd18741c36c0a2d1e19155b03af96b431', 'cliente', 'inicial', 'activo', 'activo', NULL, '2025-11-03 00:39:36'),
(29, 'Daniela Díaz', 'danyelisabet@gmail.com', '211021d2b119d78fe0e0d4d29eeff687', 'cliente', 'inicial', 'activo', 'activo', NULL, '2025-12-01 13:01:01'),
(30, 'Juan', 'juanfraa032@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'duenio', 'inicial', 'activo', 'activo', NULL, '2025-12-02 11:49:33');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `locales`
--
ALTER TABLE `locales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_duenio` (`id_duenio`);

--
-- Indices de la tabla `novedades`
--
ALTER TABLE `novedades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_local` (`id_local`);

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_promo` (`id_promo`);

--
-- Indices de la tabla `uso_promociones`
--
ALTER TABLE `uso_promociones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_promo` (`id_promo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `locales`
--
ALTER TABLE `locales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `novedades`
--
ALTER TABLE `novedades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `uso_promociones`
--
ALTER TABLE `uso_promociones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `locales`
--
ALTER TABLE `locales`
  ADD CONSTRAINT `locales_ibfk_1` FOREIGN KEY (`id_duenio`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD CONSTRAINT `promociones_ibfk_1` FOREIGN KEY (`id_local`) REFERENCES `locales` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `solicitudes_ibfk_2` FOREIGN KEY (`id_promo`) REFERENCES `promociones` (`id`);

--
-- Filtros para la tabla `uso_promociones`
--
ALTER TABLE `uso_promociones`
  ADD CONSTRAINT `uso_promociones_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `uso_promociones_ibfk_2` FOREIGN KEY (`id_promo`) REFERENCES `promociones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
