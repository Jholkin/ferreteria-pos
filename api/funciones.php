<?php
/*
 ___ ___      ___  __   __  _______  _______  _______  _______ 
|   _   |    |   ||  | |  ||       ||       ||       ||       |
|  |_|  |    |   ||  | |  ||  _____||_     _||    ___||  _____|
|       |    |   ||  |_|  || |_____   |   |  |   |___ | |_____ 
|       | ___|   ||       ||_____  |  |   |  |    ___||_____  |
|   _   ||       ||       | _____| |  |   |  |   |___  _____| |
|__| |__||_______||_______||_______|  |___|  |_______||_______|

*/

define("FECHA_HOY",date("Y-m-d") );

define('DIRECTORIO', './logos/');

define("PASSWORD_DEFECTO", "123456789");


function codificar($imagen) {
    $imagen = str_replace('data:image/png;base64,', '', $imagen);
    $imagen = str_replace('data:image/jpeg;base64,', '', $imagen);
    $imagen = str_replace(' ', '+', $imagen);
    $data = base64_decode($imagen);
    $file = DIRECTORIO. 'logo.png';
            
            
    $insertar = file_put_contents($file, $data);
    return $file;
}

function obtenerAjustes(){
	$sentencia = "SELECT * FROM configuracion";
	return selectRegresandoObjeto($sentencia);
}

function registrarAjustes($ajustes){
	$logo = ($ajustes->cambiaLogo) ? codificar($ajustes->logo) : $ajustes->logo;
	$sentencia = (!obtenerAjustes()) ? 
	"INSERT INTO configuracion (nombre, telefono, logo) VALUES (?,?,?)" :
	"UPDATE configuracion SET nombre = ?, telefono = ?, logo = ?";

	$parametros = [$ajustes->nombre, $ajustes->telefono, $logo];
	return (!obtenerAjustes()) ? insertar($sentencia, $parametros) : editar($sentencia, $parametros);
}

/*
 __   __  _______  __    _  _______  _______  _______ 
|  | |  ||       ||  |  | ||       ||   _   ||       |
|  |_|  ||    ___||   |_| ||_     _||  |_|  ||  _____|
|       ||   |___ |       |  |   |  |       || |_____ 
|       ||    ___||  _    |  |   |  |       ||_____  |
 |     | |   |___ | | |   |  |   |  |   _   | _____| |
  |___|  |_______||_|  |__|  |___|  |__| |__||_______|

*/

function filtrarPorUsuarioSiNoEsAdmin($filtros, $usuarioId){
	if($usuarioId){
		$filtros->usuarioId = $usuarioId;
	}else{
		$filtros->usuarioId = null;
	}
	return $filtros;
}

function obtenerVentasPorDiaMes($mes, $anio, $userId){
	$sentencia = "SELECT DATE_FORMAT(fecha, '%Y-%m-%d') AS dia, SUM(total) AS totalVentas FROM ventas 
	WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
	GROUP BY dia";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia .= " AND idUsuario = ?";
		$parametros = [$mes, $anio, $userId];
	} else {
		$parametros = [$mes, $anio];
	}
	return selectPrepare($sentencia, $parametros);
}

function obtenerTotalesVentasPorMes($anio,$userId){
	$parametros = [$anio];
	$sentencia = "SELECT MONTH(fecha) AS mes, SUM(total) AS totalVentas FROM ventas 
        WHERE YEAR(fecha) = ? 
        GROUP BY MONTH(fecha) ORDER BY mes ASC";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia = "SELECT MONTH(fecha) AS mes, SUM(total) AS totalVentas FROM ventas 
		WHERE YEAR(fecha) = ? AND idUsuario = ?
		GROUP BY MONTH(fecha) ORDER BY mes ASC";
		$parametros = [$anio, $userId];
	}
    return selectPrepare($sentencia, $parametros);
}

function calcularTotalIngresos($userId){
	$parametros = [];
	$sentencia = "SELECT (SELECT SUM(v.total) FROM ventas v) + (SELECT SUM(pagado) FROM cuentas_apartados) AS totalIngresos";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia = "SELECT (SELECT IFNULL(SUM(v.total),0) FROM ventas v where v.idUsuario=?) + (SELECT IFNULL(SUM(ca.pagado),0) FROM cuentas_apartados ca where ca.idUsuario=?) AS totalIngresos";
		$parametros = [$userId, $userId];
	}
	return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function calcularTotalIngresosHoy($userId){
	$parametros = [];
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE DATE(fecha) = CURDATE()) + 
	(SELECT IFNULL(SUM(pagado),0) FROM cuentas_apartados WHERE DATE(fecha) = CURDATE()) AS totalIngresos";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia = "SELECT 
		(SELECT IFNULL(SUM(total),0) FROM ventas WHERE DATE(fecha) = CURDATE() AND idUsuario = ?) + 
		(SELECT IFNULL(SUM(pagado),0) FROM cuentas_apartados WHERE DATE(fecha) = CURDATE() AND idUsuario = ?) AS totalIngresos";
		$parametros = [$userId, $userId];
	}
	return selectRegresandoObjeto($sentencia,$parametros)->totalIngresos;
}

function calcularTotalIngresosSemana($userId){
	$parametros = [];
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE WEEK(fecha) = WEEK(NOW())) + 
	(SELECT IFNULL(SUM(pagado),0) FROM cuentas_apartados WHERE WEEK(fecha) = WEEK(NOW())) AS totalIngresos";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia = "SELECT 
		(SELECT IFNULL(SUM(total),0) FROM ventas WHERE WEEK(fecha) = WEEK(NOW()) AND idUsuario = ?) + 
		(SELECT IFNULL(SUM(pagado),0) FROM cuentas_apartados WHERE WEEK(fecha) = WEEK(NOW()) AND idUsuario = ?) AS totalIngresos";
		$parametros = [$userId, $userId];
	}
	return selectRegresandoObjeto($sentencia,$parametros)->totalIngresos;
}

function calcularTotalIngresosMes($userId){
	$parametros = [];
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())) + 
	(SELECT IFNULL(SUM(pagado),0) FROM cuentas_apartados WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())) AS totalIngresos";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia = "SELECT 
		(SELECT IFNULL(SUM(total),0) FROM ventas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE()) AND idUsuario = ?) + 
		(SELECT IFNULL(SUM(pagado),0) FROM cuentas_apartados WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE()) AND idUsuario = ?) AS totalIngresos";
		$parametros = [$userId, $userId];
	}
	return selectRegresandoObjeto($sentencia,$parametros)->totalIngresos;
}

function calcularIngresosPendientes($userId){
	$parametros = [];
	$sentencia = "SELECT IFNULL(SUM(porPagar), 0) AS pendientes FROM cuentas_apartados";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia = "SELECT IFNULL(SUM(porPagar), 0) AS pendientes FROM cuentas_apartados WHERE idUsuario = ?";
		$parametros = [$userId];
	}
	return selectRegresandoObjeto($sentencia,$parametros)->pendientes;
}

function eliminarCotizacion($id){
	$sentenciaEliminarCotizacion = "DELETE FROM cotizaciones WHERE id = ?";
	$cotizacionEliminada = eliminar($sentenciaEliminarCotizacion, $id);

	$sentenciaEliminarProductos = "DELETE FROM productos_vendidos WHERE idReferencia = ? AND tipo = 'cotiza'";
	$productosEliminados = eliminar($sentenciaEliminarProductos, $id);
	return $cotizacionEliminada && $productosEliminados;
}

function abonarACuentaApartado($total, $id){
	$sentencia = "UPDATE cuentas_apartados SET pagado = pagado + ?, porPagar = porPagar - ? WHERE id = ?";
	$parametros = [$total, $total, $id];
	$abono = editar($sentencia, $parametros);
	$verificarSiLiquida = verificarSiLiquidaApartado($id);
	if($abono || $verificarSiLiquida) return true;
}

function verificarSiLiquidaApartado($id){
	$sentencia = "SELECT * FROM cuentas_apartados WHERE id = ?";
	$apartado = selectRegresandoObjeto($sentencia, [$id]);
	$total = $apartado->porPagar;
	if($total <= 0){
		$productos  = obtenerProductosVendidos($id, 'apartado');
		$descontados = descontarProductos($productos);
		if(count($descontados) > 0) return true;
	}
}

function obtenerTotalVentas($filtros){
	$fechaInicio = ($filtros->fechaInicio === "") ? FECHA_HOY : $filtros->fechaInicio;
	$fechaFin = ($filtros->fechaFin === "") ? FECHA_HOY : $filtros->fechaFin;
	if($filtros->usuarioId){
		$sentencia = "SELECT SUM(total) AS totalVentas FROM ventas WHERE DATE(ventas.fecha) >= ? AND  DATE(ventas.fecha) <= ? AND idUsuario = ?";
		$parametros = [$fechaInicio, $fechaFin, $filtros->usuarioId];
	}else{
		$sentencia = "SELECT SUM(total) AS totalVentas FROM ventas WHERE DATE(ventas.fecha) >= ? AND  DATE(ventas.fecha) <= ?";
		$parametros = [$fechaInicio, $fechaFin];
	}
	return selectRegresandoObjeto($sentencia, $parametros)->totalVentas;
}

function obtenerTotalCuentasApartados($filtros, $tipo){
	$sentencia = "SELECT SUM(total) AS total FROM cuentas_apartados WHERE tipo = ?";
	$parametros = [$tipo];
	if (isset($filtros->usuarioId) && $filtros->usuarioId) {
		$sentencia .= " AND idUsuario = ?";
		array_push($parametros, $filtros->usuarioId);
	}
	if($filtros->fechaInicio){
		$sentencia .= " AND (DATE(cuentas_apartados.fecha) >= ? AND  DATE(cuentas_apartados.fecha) <= ?)";
		array_push($parametros, $filtros->fechaInicio);
		array_push($parametros, $filtros->fechaFin);
	}
	return selectRegresandoObjeto($sentencia, $parametros)->total;
}

function obtenerTotalPorPagarCuentasApartados($filtros, $tipo){
	$sentencia = "SELECT SUM(porPagar) AS porPagar FROM cuentas_apartados WHERE tipo = ?";
	$parametros = [$tipo];
	if (isset($filtros->usuarioId) && $filtros->usuarioId) {
		$sentencia .= " AND idUsuario = ?";
		array_push($parametros, $filtros->usuarioId);
	}
	if($filtros->fechaInicio){
		$sentencia .= " AND (DATE(cuentas_apartados.fecha) >= ? AND  DATE(cuentas_apartados.fecha) <= ?)";
		array_push($parametros, $filtros->fechaInicio);
		array_push($parametros, $filtros->fechaFin);
	}
	return selectRegresandoObjeto($sentencia, $parametros)->porPagar;
}

function obtenerPagosCuentasApartados($filtros, $tipo){
	$sentencia = "SELECT SUM(pagado) AS totalPagos FROM cuentas_apartados WHERE tipo = ?";
	$parametros = [$tipo];
	if (isset($filtros->usuarioId) && $filtros->usuarioId) {
		$sentencia .= " AND idUsuario = ?";
		array_push($parametros, $filtros->usuarioId);
	}
	if($filtros->fechaInicio){
		$sentencia .= " AND (DATE(cuentas_apartados.fecha) >= ? AND  DATE(cuentas_apartados.fecha) <= ?)";
		array_push($parametros, $filtros->fechaInicio);
		array_push($parametros, $filtros->fechaFin);
	}
	return selectRegresandoObjeto($sentencia, $parametros)->totalPagos;
}

function obtenerCuentasApartados($filtros, $tipo){
	$sentencia = "SELECT cuentas_apartados.id, cuentas_apartados.fecha, cuentas_apartados.total, cuentas_apartados.pagado, cuentas_apartados.porPagar, IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente, IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario 
		FROM cuentas_apartados
		LEFT JOIN clientes ON clientes.id = cuentas_apartados.idCliente
		LEFT JOIN usuarios ON usuarios.id = cuentas_apartados.idUsuario
		WHERE cuentas_apartados.tipo = ? ";

	$parametros = [$tipo];
	if(isset($filtros->usuarioId) && $filtros->usuarioId){
		$sentencia .= " AND cuentas_apartados.idUsuario = ?";
		array_push($parametros, $filtros->usuarioId);
	}
	if($filtros->fechaInicio){
		$sentencia .= " AND (DATE(cuentas_apartados.fecha) >= ? AND  DATE(cuentas_apartados.fecha) <= ?)";
		array_push($parametros, $filtros->fechaInicio);
		array_push($parametros, $filtros->fechaFin);
	}
	$sentencia .= " ORDER BY cuentas_apartados.id DESC";
	$cuentas =  selectPrepare($sentencia, $parametros);
	return agregarProductosVendidos($cuentas, $tipo);
}

function obtenerCotizaciones($filtros, $tipo){
	$sentencia = "SELECT cotizaciones.id, cotizaciones.fecha, cotizaciones.total, IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente, IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario 
		FROM cotizaciones
		LEFT JOIN clientes ON clientes.id = cotizaciones.idCliente
		LEFT JOIN usuarios ON usuarios.id = cotizaciones.idUsuario 
		WHERE 1 ";
	$parametros = [];
	if (isset($filtros->usuarioId) && $filtros->usuarioId) {
		$sentencia .= " AND cotizaciones.idUsuario = ?";
		array_push($parametros, $filtros->usuarioId);
	}
	if($filtros->fechaInicio){
		$sentencia .= " AND (DATE(cotizaciones.fecha) >= ? AND  DATE(cotizaciones.fecha) <= ?)";
		array_push($parametros, $filtros->fechaInicio);
		array_push($parametros, $filtros->fechaFin);
	}

	$cotizaciones = selectPrepare($sentencia, $parametros);
	return agregarProductosVendidos($cotizaciones, $tipo);
}


function obtenerVentas($filtros){
	$fechaInicio = ($filtros->fechaInicio === "") ? FECHA_HOY : $filtros->fechaInicio;
	$fechaFin = ($filtros->fechaFin === "") ? FECHA_HOY : $filtros->fechaFin;
	
	
	$sentencia = "SELECT ventas.id, ventas.fecha, ventas.total, ventas.pagado, IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente, IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario 
		FROM ventas
		LEFT JOIN clientes ON clientes.id = ventas.idCliente
		LEFT JOIN usuarios ON usuarios.id = ventas.idUsuario
		WHERE DATE(ventas.fecha) >= ? AND  DATE(ventas.fecha) <= ?";

	if($filtros->usuarioId){
		$sentencia .= " AND idUsuario = ?";
	}
	$sentencia .= " ORDER BY ventas.id DESC";
	$parametros = [$fechaInicio, $fechaFin];
	if($filtros->usuarioId){
		array_push($parametros, $filtros->usuarioId);
	}
	$ventas =  selectPrepare($sentencia, $parametros);
	return agregarProductosVendidos($ventas, 'venta');
}

function agregarProductosVendidos($arreglo, $tipo){
	foreach ($arreglo as $item) {
		$item->productos = obtenerProductosVendidos($item->id, $tipo);
	}
	return $arreglo;
}

function obtenerProductosVendidos($id, $tipo) {
	$sentencia = "SELECT productos_vendidos.cantidad, productos_vendidos.precio, productos.nombre, productos.precioCompra, productos.id
	FROM productos_vendidos
	LEFT JOIN productos ON productos.id =  productos_vendidos.idProducto
	WHERE productos_vendidos.idReferencia = ? AND productos_vendidos.tipo = ?";
	$parametros = [$id, $tipo];
	return selectPrepare($sentencia, $parametros);
}

function registrarOActualizarCliente($cliente){
	$clienteExistente = obtenerClientePorDni($cliente->dni);
	if (!$clienteExistente) {
		$clienteExistente = obtenerClientesPorNombre($cliente->nombre);
	}
	if($clienteExistente){
		$cliente->id = $clienteExistente->id;
		editarCliente($cliente);
		return $clienteExistente->id;
	}else{
		registrarCliente($cliente);
		return obtenerClientePorDni($cliente->dni)->id;
	}
}

function terminarVenta($venta){
	date_default_timezone_set('America/Lima');
	$tipo = $venta->tipo;
	$clienteId = (isset($venta->cliente)) ? $venta->cliente : 0;
	if ($clienteId === 0) $venta->cliente = registrarOActualizarCliente($venta->objetoCliente);

	switch ($tipo) {
		case 'venta':
			return vender($venta);
			break;

		case 'cuenta':
			return agregarCuentaApartado($venta);
			break;

		case 'apartado':
			return agregarCuentaApartado($venta);
			break;

		case 'cotiza':
			return agregarCotizacion($venta);
			break;
		
		default:
			return false;
			break;
	}

}

function vender($venta){
	$venta->cliente = (isset($venta->cliente)) ? $venta->cliente : 0;
	$sentencia = "INSERT INTO ventas (fecha, total, pagado, idCliente, idUsuario) VALUES (?,?,?,?,?)";
	$parametros = [date("Y-m-d H:i:s"), $venta->total, $venta->pagado, $venta->cliente, $venta->usuario];
	$registrado = insertar($sentencia, $parametros);
	
	if(!$registrado) return false;

	$idVenta = obtenerUltimoId('ventas');
	$venta->tipo = 'venta';
	$productosRegistrados = registrarProductosVendidosYKardex($venta, $idVenta);
	//$productosEditados = descontarProductos($venta->productos);
	//if(count($productosRegistrados)>0 && count($productosEditados)>0) return true;
	if(count($productosRegistrados)>0) return true;
}

function agregarCuentaApartado($venta){
	$sentencia = "INSERT INTO cuentas_apartados (fecha, total, pagado, porPagar, tipo, idCliente, idUsuario) VALUES (?,?,?,?,?,?,?)";
	$parametros = [date("Y-m-d H:i:s"), $venta->total, $venta->pagado, $venta->porPagar, $venta->tipo, $venta->cliente, $venta->usuario];

	$registrado = insertar($sentencia, $parametros);
	
	if(!$registrado) return false;

	$idVenta = obtenerUltimoId('cuentas_apartados');
	$productosRegistrados = registrarProductosVendidosYKardex($venta, $idVenta);
	//if($venta->tipo === 'cuenta') descontarProductos($venta->productos);
	if(count($productosRegistrados)>0 ) return true;
}

function agregarCotizacion($venta){
	$sentencia = "INSERT INTO cotizaciones(fecha, total, idCliente, idUsuario) VALUES (?,?,?,?)";
	$parametros = [date("Y-m-d H:i:s"), $venta->total, $venta->cliente, $venta->usuario];

	$registrado = insertar($sentencia, $parametros);
	$idCotizacion =  obtenerUltimoId('cotizaciones');

	$productosRegistrados = registrarProductosVendidos($venta->productos, $idCotizacion, $venta->tipo);

	if(count($productosRegistrados)>0 ) return true;
}

function registrarProductosVendidos($productos, $idReferencia, $tipo){
	$sentencia = "INSERT INTO productos_vendidos (cantidad, precio, idProducto, idReferencia, tipo) VALUES(?,?,?,?,?)";
	$resultados = [];

	foreach ($productos as $producto) {
		$parametros = [$producto->cantidad, $producto->precio, $producto->id, $idReferencia, $tipo];
		$productoRegistrado = insertar($sentencia, $parametros);
		if($productoRegistrado) array_push($resultados, 1);
	}

	return $resultados;
}

function registrarProductosVendidosYKardex($venta, $idReferencia) {
	$sentencia = "INSERT INTO productos_vendidos (cantidad, precio, idProducto, idReferencia, tipo) VALUES(?,?,?,?,?)";
	$resultados = [];

	foreach ($venta->productos as $producto) {
		$parametros = [$producto->cantidad, $producto->precio, $producto->id, $idReferencia, $venta->tipo];
		$productoRegistrado = insertar($sentencia, $parametros);
		if($productoRegistrado) array_push($resultados, 1);
		$kardex = (object) [
			'idProducto' => $producto->id,
			'fecha' => date("Y-m-d H:i:s"),
			'tipo' => $venta->tipo,
			'cantidad' => $producto->cantidad,
			'idReferencia' => $idReferencia,
			'idUsuario' => $venta->usuario,
			'observacion' => $venta->tipo . ' realizada',
			'multiplicador' => -1
		];
		insertarKardexProducto($kardex);
	}

	return $resultados;
}

function descontarProductos($productos){
	$sentencia = "UPDATE productos SET existencia = existencia - ? WHERE id = ?";
	$resultados = [];
	foreach ($productos as $producto) {
		$parametros = [$producto->cantidad, $producto->id];
		$resultado = editar($sentencia, $parametros);
		if($resultado) array_push($resultados, 1);
	}
	return $resultados;
}

/*                                                                                                  
 __   __  _______  __   __  _______  ______    ___   _______  _______ 
|  | |  ||       ||  | |  ||   _   ||    _ |  |   | |       ||       |
|  | |  ||  _____||  | |  ||  |_|  ||   | ||  |   | |   _   ||  _____|
|  |_|  || |_____ |  |_|  ||       ||   |_||_ |   | |  | |  || |_____ 
|       ||_____  ||       ||       ||    __  ||   | |  |_|  ||_____  |
|       | _____| ||       ||   _   ||   |  | ||   | |       | _____| |
|_______||_______||_______||__| |__||___|  |_||___| |_______||_______|
                                                                    
*/

function obtenerTotalVentasPorMesUsuario($idUsuario, $anio){
	$sentencia = "SELECT MONTH(fecha) AS mes, SUM(total) AS totalVentas FROM ventas 
        WHERE YEAR(fecha) = ?  AND idUsuario = ?
        GROUP BY MONTH(fecha) ORDER BY mes ASC";
    $parametros = [$anio, $idUsuario];
    return selectPrepare($sentencia, $parametros);
}

function calcularTotalIngresosUsuario($idUsuario){
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE idUsuario = ?) + 
	(SELECT IFNULL(SUM(pagado),0) FROM cuentas_apartados WHERE idUsuario = ?) AS totalIngresos";
	$parametros = [$idUsuario, $idUsuario];
	return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function calcularTotalIngresosHoyUsuario($idUsuario){
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE DATE(fecha) = CURDATE() AND idUsuario = ?) + 
	(SELECT IFNULL(SUM(pagado),0) FROM cuentas_apartados WHERE DATE(fecha) = CURDATE() AND idUsuario = ?) AS totalIngresos";
	$parametros = [$idUsuario, $idUsuario];
	return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function calcularTotalIngresosSemanaUsuario($idUsuario){
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE WEEK(fecha) = WEEK(NOW()) AND idUsuario = ?) + 
	(SELECT IFNULL(SUM(pagado),0) FROM cuentas_apartados WHERE WEEK(fecha) = WEEK(NOW()) AND idUsuario = ?) AS totalIngresos";
	$parametros = [$idUsuario, $idUsuario];
	return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function calcularTotalIngresosMesUsuario($idUsuario){
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE()) AND idUsuario = ?) + 
	(SELECT IFNULL(SUM(pagado),0) FROM cuentas_apartados WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE()) AND idUsuario = ?) AS totalIngresos";
	$parametros = [$idUsuario, $idUsuario];
	return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function obtenerVentasPorUsuario($userId){
	$sentencia = "SELECT usuarios.usuario, SUM(ventas.total) AS totalVentas  FROM ventas
	INNER JOIN usuarios ON usuarios.id = ventas.idUsuario
	GROUP BY usuarios.id";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia .= " HAVING usuarios.id = ?";
		$parametros = [$userId];
		return selectPrepare($sentencia, $parametros);
	}
	return selectQuery($sentencia);
}

function iniciarSesion($usuario){
	$sentencia = "SELECT * FROM usuarios WHERE usuario = ?";
	$parametros = [$usuario->usuario];
	$resultado = selectRegresandoObjeto($sentencia, $parametros);
	if($resultado){
		$loginCorrecto = verificarPassword($resultado->id, $usuario->password);
		if($loginCorrecto){
			$datos = [
				"id" => $resultado->id,
				"usuario" => $resultado->usuario,
				"nombre" => $resultado->nombre,
				"rol" => $resultado->rol
			];	

			return ["estado" => $loginCorrecto, "usuario" => $datos];
		}
	
	}
	return false;

}

function verificarPassword($idUsuario, $password){
	$sentencia = "SELECT password FROM usuarios WHERE id = ?";
	$parametros = [$idUsuario];
	$resultado = selectRegresandoObjeto($sentencia, $parametros);
	$verificar = password_verify($password, $resultado->password);
	if($verificar) return true;
	return false;
}

function cambiarPassword($idUsuario, $password){
	$sentencia = "UPDATE usuarios SET password = ? WHERE id = ?";
	$parametros = [$password, $idUsuario];
	return editar($sentencia, $parametros);
}
function registrarUsuario($usuario){
	$sentencia = "INSERT INTO usuarios (usuario, nombre, telefono, password, rol) VALUES (?,?,?,?,?)";
	$parametros = [$usuario->usuario, $usuario->nombre, $usuario->telefono, $usuario->password, $usuario->rol];
	return insertar($sentencia, $parametros);
}

function obtenerUsuarioPorId($id){
	$sentencia = "SELECT id, usuario, nombre, telefono, rol FROM usuarios WHERE id = ?";
	return selectRegresandoObjeto($sentencia, [$id]);
}

function editarUsuario($usuario){
	$sentencia = "UPDATE usuarios SET usuario = ?, nombre = ?, telefono = ?, rol = ? WHERE id = ?";
	$parametros = [$usuario->usuario, $usuario->nombre, $usuario->telefono, $usuario->rol, $usuario->id];
	return editar($sentencia, $parametros);
}

function eliminarUsuario($id){
	$sentencia = "DELETE FROM usuarios WHERE id = ?";
	return eliminar($sentencia, $id);
}

function obtenerUsuarios(){
	$sentencia = "SELECT id, usuario, nombre, telefono FROM usuarios";
	return selectQuery($sentencia);
}

/*
 _______  ___      ___   _______  __    _  _______  _______  _______ 
|       ||   |    |   | |       ||  |  | ||       ||       ||       |
|       ||   |    |   | |    ___||   |_| ||_     _||    ___||  _____|
|       ||   |    |   | |   |___ |       |  |   |  |   |___ | |_____ 
|      _||   |___ |   | |    ___||  _    |  |   |  |    ___||_____  |
|     |_ |       ||   | |   |___ | | |   |  |   |  |   |___  _____| |
|_______||_______||___| |_______||_|  |__|  |___|  |_______||_______|

*/

function obtenerVentasPorCliente($userId){
	$sentencia = "SELECT clientes.nombre, SUM(ventas.total) AS totalVentas  FROM ventas
	INNER JOIN clientes ON clientes.id = ventas.idCliente";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia .= " WHERE ventas.idUsuario = ?";
		$parametros = [$userId];
	}
	$sentencia .= " GROUP BY clientes.id";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		return selectPrepare($sentencia, $parametros);
	}
	return selectQuery($sentencia);
}

function obtenerClientePorDni($dni){
	$sentencia = "SELECT * FROM clientes WHERE dni = ?";
	$parametros = [$dni];
	return selectRegresandoObjeto($sentencia, $parametros);
}

function obtenerClientesPorNombre($nombre){
	$sentencia = "SELECT * FROM clientes WHERE UPPER(nombre) LIKE ?";
	$parametros = ["%".strtoupper($nombre)."%"];
	return selectPrepare($sentencia, $parametros);
}

function obtenerClientes(){
	$sentencia = "SELECT * FROM clientes";
	return selectQuery($sentencia);
}

function registrarCliente($cliente){
	$sentencia = "INSERT INTO clientes (nombre, dni, telefono) VALUES (?,?,?)";
	$parametros = [$cliente->nombre, $cliente->dni, $cliente->telefono];
	return insertar($sentencia, $parametros);
}

function obtenerClientePorId($id){
	$sentencia = "SELECT * FROM clientes WHERE id = ?";
	return selectRegresandoObjeto($sentencia, [$id]);
}

function editarCliente($cliente){
	$sentencia = "UPDATE clientes SET nombre = ?, dni = ?, telefono = ? WHERE id = ?";
	$parametros = [$cliente->nombre, $cliente->dni, $cliente->telefono, $cliente->id];
	return editar($sentencia, $parametros);
}

function eliminarCliente($id){
	$sentencia = "DELETE FROM clientes WHERE id = ?";
	return eliminar($sentencia, $id);
}

/*
 _______  ______    _______  ______   __   __  _______  _______  _______  _______ 
|       ||    _ |  |       ||      | |  | |  ||       ||       ||       ||       |
|    _  ||   | ||  |   _   ||  _    ||  | |  ||       ||_     _||   _   ||  _____|
|   |_| ||   |_||_ |  | |  || | |   ||  |_|  ||       |  |   |  |  | |  || |_____ 
|    ___||    __  ||  |_|  || |_|   ||       ||      _|  |   |  |  |_|  ||_____  |
|   |    |   |  | ||       ||       ||       ||     |_   |   |  |       | _____| |
|___|    |___|  |_||_______||______| |_______||_______|  |___|  |_______||_______|

*/
function insertarKardexProducto($kardex) {
	date_default_timezone_set('America/Lima');
	$sentencia = "INSERT INTO kardex (idProducto, fecha, tipo, cantidad, idReferencia, idUsuario, observacion, multiplicador) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
	$parametros = [
		$kardex->idProducto,
		$kardex->fecha,
		$kardex->tipo,
		$kardex->cantidad,
		$kardex->idReferencia,
		$kardex->idUsuario,
		$kardex->observacion,
		$kardex->multiplicador
	];
	return insertar($sentencia, $parametros);
}

function obtenerKardexProducto($idProducto, $desde = null, $hasta = null, $tipo = '') {
    $parametros = [$idProducto];
    $where = " WHERE t.idProducto = ? ";

    if ($desde) {
        $where .= " AND DATE(t.fecha) >= ? ";
        $parametros[] = $desde;
    }
    if ($hasta) {
        $where .= " AND DATE(t.fecha) <= ? ";
        $parametros[] = $hasta;
    }
    if ($tipo) {
        $where .= " AND t.tipo = ? ";
        $parametros[] = $tipo;
    }

    $sentencia = "SELECT t.*, u.usuario from kardex t INNER JOIN usuarios u ON u.id = t.idUsuario ";
	$sentencia .= $where;

    $movimientos = selectPrepare($sentencia, $parametros);

    $totalIngresos = 0;
    $totalSalidas = 0;
    $totalVentas = 0;
    $existenciaActual = 0;
	$existencia = 0;

    foreach ($movimientos as $mov) {
		$mov->cantidad = $mov->cantidad * $mov->multiplicador; // Ajustar cantidad por el multiplicador
        $existenciaActual += $mov->cantidad;
		$mov->existencia = $existenciaActual;
        if ($mov->tipo === 'ingreso') $totalIngresos += (int)$mov->cantidad;
        if ($mov->tipo === 'salida') $totalSalidas += (int)$mov->cantidad;
        if ($mov->tipo === 'venta') $totalVentas += (int)$mov->cantidad;
    }

    return [
        "movimientos" => $movimientos,
        "totalIngresos" => $totalIngresos,
        "totalSalidas" => $totalSalidas,
        "totalVentas" => $totalVentas,
        "existenciaActual" => $existenciaActual
    ];
}

function obtenerProductosMasVendidos($limite, $userId){
	$sentencia = "SELECT SUM(productos_vendidos.cantidad * productos_vendidos.precio) AS total, SUM(productos_vendidos.cantidad) AS unidades,
	productos.nombre FROM productos_vendidos INNER JOIN productos ON productos.id = productos_vendidos.idProducto
	WHERE productos_vendidos.tipo = 'venta'
	GROUP BY productos_vendidos.idProducto
	ORDER BY total DESC
	LIMIT ?";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia = "SELECT SUM(productos_vendidos.cantidad * productos_vendidos.precio) AS total, SUM(productos_vendidos.cantidad) AS unidades,
		productos.nombre FROM productos_vendidos INNER JOIN ventas on ventas.id = productos_vendidos.idReferencia INNER JOIN productos ON productos.id = productos_vendidos.idProducto
		WHERE productos_vendidos.tipo = 'venta' AND ventas.idUsuario = ?
		GROUP BY productos_vendidos.idProducto
		ORDER BY total DESC
		LIMIT ?";
		return selectPrepare($sentencia, [$userId, $limite]);
	}
	return selectPrepare($sentencia, [$limite]);
}

function agregarExistenciaProducto($cantidad, $id, $idUsuario = 0){
	/*$sentencia = "UPDATE productos SET existencia =  existencia + ? WHERE id = ?";
	$parametros = [$cantidad, $id];*/

	$kardex = (object) [
		'idProducto' => $id,
		'fecha' => date("Y-m-d H:i:s"),
		'tipo' => 'ingreso',
		'cantidad' => $cantidad,
		'idReferencia' => 0, // No hay referencia para ingresos directos
		'idUsuario' => $idUsuario,
		'observacion' => 'Ingreso de existencia',
		'multiplicador' => 1 // Aumenta la existencia
	];
	//return editar($sentencia, $parametros);
	return insertarKardexProducto($kardex);
}

function restarExistenciaProducto($cantidad, $id, $idUsuario = 0){
	/*$sentencia = "UPDATE productos SET existencia =  existencia - ? WHERE id = ?";
	$parametros = [$cantidad, $id];*/
	$kardex = (object) [
		'idProducto' => $id,
		'fecha' => date("Y-m-d H:i:s"),
		'tipo' => 'salida',
		'cantidad' => $cantidad,
		'idReferencia' => 0, // No hay referencia para salidas directas
		'idUsuario' => $idUsuario,
		'observacion' => 'Salida de existencia',
		'multiplicador' => -1 // Disminuye la existencia
	];
	//return editar($sentencia, $parametros);
	return insertarKardexProducto($kardex);
}

function calcularGananciaInventario(){
	$sentencia = "SELECT SUM((precioVenta * existencia)-(precioCompra * existencia)) AS gananciaInventario FROM productos";
	return selectRegresandoObjeto($sentencia)->gananciaInventario;
}

function calcularTotalInventario(){
	$sentencia = "SELECT SUM(precioVenta * existencia) AS totalInventario FROM productos";
	return selectRegresandoObjeto($sentencia)->totalInventario;
}

function calcularNumeroTotalProductos(){
	$sentencia = "SELECT SUM(existencia) AS numeroProductos FROM productos";
	return selectRegresandoObjeto($sentencia)->numeroProductos;
}

function buscarProductoPorNombreOCodigo($producto){
	$sentencia = "SELECT * FROM productos WHERE (codigo = ? OR nombre LIKE ? OR codigo LIKE ?) LIMIT 10";
	$parametros = [$producto, '%'.$producto.'%', '%'.$producto.'%'];
	return selectPrepare($sentencia, $parametros);
}

function registrarProducto($producto){
	$sentencia = "INSERT INTO productos (codigo, nombre, precioCompra, precioVenta, existencia, vendidoMayoreo, precioMayoreo, cantidadMayoreo, marca, categoria) VALUES(?,?,?,?,?,?,?,?,?,?)";
	$parametros = [$producto->codigo, $producto->nombre, $producto->precioCompra, $producto->precioVenta, $producto->existencia, $producto->vendidoMayoreo, $producto->precioMayoreo, $producto->cantidadMayoreo, $producto->marca, $producto->categoria];
	return insertar($sentencia, $parametros);
}

function obtenerProductos(){
	$sentencia = "SELECT productos.*, IFNULL(categorias.nombreCategoria, 'NO ENCONTRADA') AS nombreCategoria, IFNULL(marcas.nombreMarca, 'NO ENCONTRADA') AS nombreMarca 
	FROM productos
	LEFT JOIN categorias ON categorias.id = productos.categoria
	LEFT JOIN marcas ON marcas.id = productos.marca";
	return selectQuery($sentencia);
}

function obtenerProductoPorId($id){
	$sentencia = "SELECT * FROM productos WHERE id = ?";
	return selectRegresandoObjeto($sentencia, [$id]);
}

function editarProducto($producto){
	$sentencia = "UPDATE productos SET codigo = ?, nombre = ?, precioCompra = ?, precioVenta = ?, existencia = ?, vendidoMayoreo = ?, precioMayoreo = ?, cantidadMayoreo = ?, marca = ?, categoria = ? WHERE id = ?";
	$parametros = [$producto->codigo, $producto->nombre, $producto->precioCompra, $producto->precioVenta, $producto->existencia, $producto->vendidoMayoreo, $producto->precioMayoreo, $producto->cantidadMayoreo, $producto->marca, $producto->categoria, $producto->id];
	return editar($sentencia, $parametros);
}

function eliminarProducto($id){
	$sentencia = "DELETE FROM productos WHERE id = ?";
	return eliminar($sentencia, $id);
}

/*
 __   __  _______  ______    _______ / _______  _______  _______  _______  _______ 
|  |_|  ||   _   ||    _ |  |       | |       ||   _   ||       ||       ||       |
|       ||  |_|  ||   | ||  |       | |       ||  |_|  ||_     _||    ___||    ___|
|       ||       ||   |_||_ |       | |       ||       |  |   |  |   |___ |   | __ 
|       ||       ||    __  ||      _| |      _||       |  |   |  |    ___||   ||  |
| ||_|| ||   _   ||   |  | ||     |_  |     |_ |   _   |  |   |  |   |___ |   |_| |
|_|   |_||__| |__||___|  |_||_______| |_______||__| |__|  |___|  |_______||_______|

*/
//FUNCIONES DE LAS MARCAS

function obtenerTotalesMarca($userId){
	$sentencia = "SELECT marcas.nombreMarca, SUM(productos_vendidos.precio * productos_vendidos.cantidad) AS totalVentas
	FROM productos_vendidos
	INNER JOIN ventas ON ventas.id = productos_vendidos.idReferencia
	INNER JOIN productos ON productos.id = productos_vendidos.idProducto
	INNER JOIN marcas ON marcas.id = productos.marca
	WHERE productos_vendidos.tipo = 'venta'";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia .= " and ventas.idUsuario = ?";
		$parametros = [$userId];
	}
	$sentencia .= " GROUP BY marcas.id";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		return selectPrepare($sentencia, $parametros);
	}
	return selectQuery($sentencia);
}

function obtenerTotalesCategoria($userId){
	$sentencia = "SELECT categorias.nombreCategoria, SUM(productos_vendidos.precio * productos_vendidos.cantidad) AS totalVentas
	FROM productos_vendidos
	INNER JOIN ventas ON ventas.id = productos_vendidos.idReferencia
	INNER JOIN productos ON productos.id = productos_vendidos.idProducto
	INNER JOIN categorias ON categorias.id = productos.categoria
	WHERE productos_vendidos.tipo = 'venta'";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		$sentencia .= " and ventas.idUsuario = ? ";
		$parametros = [$userId];
	}
	$sentencia .= " GROUP BY categorias.id";
	if ($userId !== null && $userId !== 0 && $userId !== "") {
		return selectPrepare($sentencia, $parametros);
	}
	return selectQuery($sentencia);
}

function registrarMarca($marca){
	$existe = verificarSiMarcaEstaRegistrada($marca->nombreMarca);
	if($existe === 'true') return 'existe';

	$sentencia = "INSERT INTO marcas (nombreMarca) VALUES(?)";
	$parametros = [strtoupper($marca->nombreMarca)];
	return insertar($sentencia, $parametros);
}

function obtenerMarcas(){
	$sentencia = "SELECT * FROM marcas";
	return selectQuery($sentencia);
}

function editarMarca($marca){
	$sentencia = "UPDATE marcas SET nombreMarca = ? WHERE id = ?";
	$parametros = [strtoupper($marca->nombreMarca), $marca->id];
	return editar($sentencia, $parametros);
}

function eliminarMarca($id){
	$sentencia = "DELETE FROM marcas WHERE id = ?";
	return eliminar($sentencia, $id);
}

function verificarSiMarcaEstaRegistrada($nombreMarca){
	$sentencia = "SELECT IF(  EXISTS(SELECT nombreMarca FROM marcas  WHERE nombreMarca = ? ),'true','false' ) AS resultado";
	return selectRegresandoObjeto($sentencia, [strtoupper($nombreMarca)])->resultado;
}

//FUNCIONES DE LAS CATEGORÍAS

function registrarCategoria($categoria){
	$existe = verificarSiCategoriaEstaRegistrada($categoria->nombreCategoria);
	if($existe === 'true') return 'existe';

	$sentencia = "INSERT INTO categorias (nombreCategoria) VALUES(?)";
	$parametros = [strtoupper($categoria->nombreCategoria)];
	return insertar($sentencia, $parametros);
}

function obtenerCategorias(){
	$sentencia = "SELECT * FROM categorias";
	return selectQuery($sentencia);
}

function editarCategoria($categoria){
	$sentencia = "UPDATE categorias SET nombreCategoria = ? WHERE id = ?";
	$parametros = [strtoupper($categoria->nombreCategoria), $categoria->id];
	return editar($sentencia, $parametros);
}

function eliminarCategoria($id){
	$sentencia = "DELETE FROM categorias WHERE id = ?";
	return eliminar($sentencia, $id);
}

function verificarSiCategoriaEstaRegistrada($nombreCategoria){
	$sentencia = "SELECT IF(  EXISTS(SELECT nombreCategoria FROM categorias  WHERE nombreCategoria = ? ),'true','false' ) AS resultado";
	return selectRegresandoObjeto($sentencia, [strtoupper($nombreCategoria)])->resultado;
}

/*

 ______   _______ 
|      | |  _    |
|  _    || |_|   |
| | |   ||       |
| |_|   ||  _   | 
|       || |_|   |
|______| |_______|

*/

function obtenerUltimoId($tabla){
	$bd = conectarBD();
	$sql = $bd->query("SELECT id FROM ".  $tabla ." ORDER BY id DESC LIMIT 1");
	return $sql->fetchObject()->id;
}

function insertar($sentencia, $parametros){
	$bd = conectarBD();
	$sql = $bd->prepare($sentencia);
	return $sql->execute($parametros);
}

function editar($sentencia, $parametros){
	$bd = conectarBD();
	$sql = $bd->prepare($sentencia);
	return $sql->execute($parametros);
}

function eliminar($sentencia, $id){
	$bd = conectarBD();
	$sql = $bd->prepare($sentencia);
	return $sql->execute([$id]);
}

function selectRegresandoObjeto($sentencia, $parametros = []){
	$bd = conectarBD();
	$sql = $bd->prepare($sentencia);
	$sql->execute($parametros);
	return $sql->fetchObject();
}

function selectQuery($sentencia){
	$bd = conectarBD();
	$sql = $bd->query($sentencia);
	return $sql->fetchAll();
}

function selectPrepare($sentencia, $parametros){
	$bd = conectarBD();
	$sql = $bd->prepare($sentencia);
	$sql->execute($parametros);
	return $sql->fetchAll();
}

function conectarBD(){
 	$host = "localhost";
	$db   = "pos";
	$user = "root";
	$pass = "root123";
	$charset = 'utf8mb4';

	$options = [
	    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
	    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
	    \PDO::ATTR_EMULATE_PREPARES   => false,
	];
	$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
	try {
	     $pdo = new \PDO($dsn, $user, $pass, $options);
	     return $pdo;
	} catch (\PDOException $e) {
	     throw new \PDOException($e->getMessage(), (int)$e->getCode());
	}
 }