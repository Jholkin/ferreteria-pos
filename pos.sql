SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombreCategoria` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombreCategoria`) VALUES
(1, 'HERRAMIENTAS ELÉCTRICAS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `telefono`) VALUES
(1, 'Marcelo Seas Retis', '987654321'),
(2, 'Jhon Chuuino Sifuentes', '978654321'),
(3, 'Talia Tan Vasuez', '987645321'),
(4, 'Elizabeth Lastra Cierto', '988654321');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `nombre` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`nombre`, `telefono`, `logo`) VALUES
('Ferretería la Alternativa', '01 8765267', './logos/logo.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL,
  `total` decimal(9,2) NOT NULL,
  `idCliente` bigint(20) DEFAULT NULL,
  `idUsuario` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `cotizaciones`
--

INSERT INTO `cotizaciones` (`id`, `fecha`, `total`, `idCliente`, `idUsuario`) VALUES
(1, '2024-10-15 16:33:23', '1162.00', 2, 1),
(2, '2024-10-15 16:36:43', '1000.00', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_apartados`
--

CREATE TABLE `cuentas_apartados` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL,
  `total` decimal(9,2) NOT NULL,
  `pagado` decimal(9,2) NOT NULL,
  `porPagar` decimal(9,2) NOT NULL,
  `tipo` enum('apartado','cuenta') NOT NULL,
  `idCliente` bigint(20) DEFAULT NULL,
  `idUsuario` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `cuentas_apartados`
--

INSERT INTO `cuentas_apartados` (`id`, `fecha`, `total`, `pagado`, `porPagar`, `tipo`, `idCliente`, `idUsuario`) VALUES
(1, '2024-10-20 17:30:03', '1000.00', '1000.00', '0.00', 'cuenta', 2, 1),
(2, '2024-10-20 17:36:09', '1000.00', '1000.00', '0.00', 'apartado', 1, 1);
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombreMarca` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id`, `nombreMarca`) VALUES
(1, 'CELIMA'),
(2, 'PAVCO'),
(3, 'FERRO'),
(4, 'KALLPA'),
(5, 'BOSCH'),
(6, 'MAKITA'),
(7, 'TRUPER'),
(10, 'STANLEY');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `precioCompra` decimal(8,2) NOT NULL,
  `precioVenta` decimal(8,2) NOT NULL,
  `existencia` int(11) NOT NULL,
  `vendidoMayoreo` tinyint(1) DEFAULT NULL,
  `precioMayoreo` decimal(8,2) DEFAULT NULL,
  `cantidadMayoreo` decimal(8,2) DEFAULT NULL,
  `marca` bigint(20) UNSIGNED DEFAULT NULL,
  `categoria` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `precioCompra`, `precioVenta`, `existencia`, `vendidoMayoreo`, `precioMayoreo`, `cantidadMayoreo`, `marca`, `categoria`) VALUES
(1, '00001', 'Taladro percutor', '300.00', '500.00', 15, 0, '450.00', '5', 5, 1), 
(2, '00002', 'Sierra circular', '250.00', '400.00', 10, 0, '380.00', '4', 6, 1), 
(3, '00003', 'Martillo de uña', '20.00', '35.00', 50, 0, '30.00', '10', 7, 2), 
(4, '00004', 'Llave ajustable', '25.00', '45.00', 40, 0, '40.00', '10', 7, 2), 
(5, '00005', 'Tubo PVC 1 pulgada', '5.00', '12.00', 100, 0, '10.00', '20', 2, 3), 
(6, '00006', 'Cemento Portland', '18.00', '25.00', 200, 0, '22.00', '50', 1, 3);
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_vendidos`
--

CREATE TABLE `productos_vendidos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(5,2) NOT NULL,
  `precio` decimal(8,2) NOT NULL,
  `idProducto` bigint(20) NOT NULL,
  `idReferencia` bigint(20) NOT NULL,
  `tipo` enum('apartado','cuenta','venta','cotiza') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `productos_vendidos`
--

INSERT INTO `productos_vendidos` (`id`, `cantidad`, `precio`, `idProducto`, `idReferencia`, `tipo`) VALUES
(1, '1.00', '500.00', 1, 1, 'cuenta'),
(2, '1.00', '400.00', 2, 1, 'venta');
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `nombre`, `telefono`, `password`) VALUES
(1, 'jheyson_hr', 'Jheyson Jhordy Huarauya Riquelme', '987654432', '$2y$10$gzUnjFNk5SUh3yGh2OXYeuIDtkzqUCGnJUnThqXjWoVU0/C9QCJb.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL,
  `total` decimal(9,2) NOT NULL,
  `pagado` decimal(9,2) NOT NULL,
  `idCliente` bigint(20) DEFAULT NULL,
  `idUsuario` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `fecha`, `total`, `pagado`, `idCliente`, `idUsuario`) VALUES
(1, '2024-10-20 17:30:52', '500.00', '500.00', 1, 1);
--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cuentas_apartados`
--
ALTER TABLE `cuentas_apartados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos_vendidos`
--
ALTER TABLE `productos_vendidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `cuentas_apartados`
--
ALTER TABLE `cuentas_apartados`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `productos_vendidos`
--
ALTER TABLE `productos_vendidos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
