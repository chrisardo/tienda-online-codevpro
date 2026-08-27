<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_oferta_descuento.php
// Módulo: Ofertas y Descuentos
// Sistema: Inventa
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// RESPUESTA BASE
//=====================================================

$respuesta = [
    "success" => false,
    "mensaje" => "",
    "datos"   => null
];

//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON($respuesta, $codigoHTTP = 200)
{
    http_response_code($codigoHTTP);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    $respuesta["mensaje"] =
        "No se pudo establecer conexión con la base de datos.";

    responderJSON($respuesta, 500);
}

//=====================================================
// OBTENER ID DEL USUARIO
//=====================================================

$idUser = 0;

//-----------------------------------------------------
// SESIÓN PRINCIPAL
//-----------------------------------------------------

if (isset($_SESSION["id"]) && intval($_SESSION["id"]) > 0) {

    $idUser = intval($_SESSION["id"]);
} elseif (
    isset($_SESSION["id_user"]) &&
    intval($_SESSION["id_user"]) > 0
) {

    $idUser = intval($_SESSION["id_user"]);
} elseif (
    isset($_SESSION["idUser"]) &&
    intval($_SESSION["idUser"]) > 0
) {

    $idUser = intval($_SESSION["idUser"]);
}

//=====================================================
// VALIDAR USUARIO
//=====================================================

if ($idUser <= 0) {

    $respuesta["mensaje"] =
        "La sesión del usuario no es válida o ha expirado.";

    responderJSON($respuesta, 401);
}

//=====================================================
// VALIDAR MÉTODO HTTP
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    $respuesta["mensaje"] =
        "Método de solicitud no permitido.";

    responderJSON($respuesta, 405);
}

//=====================================================
// OBTENER ID DEL PRODUCTO
//=====================================================

$idProducto = 0;

if (isset($_GET["idProducto"])) {

    $idProducto = intval($_GET["idProducto"]);
}

//=====================================================
// VALIDAR ID PRODUCTO
//=====================================================

if ($idProducto <= 0) {

    $respuesta["mensaje"] =
        "El producto seleccionado no es válido.";

    responderJSON($respuesta, 400);
}

//=====================================================
// CONSULTA DEL PRODUCTO
//=====================================================
//
// Se valida:
//
// 1. Que el producto exista.
// 2. Que pertenezca al usuario actual.
// 3. Que no esté eliminado.
// 4. Que categoría, marca y sucursal pertenezcan
//    también al usuario actual cuando existan.
// 5. Se utilizan LEFT JOIN para permitir valores NULL.
//
//=====================================================

$sql = "

    SELECT

        p.idProducto,

        p.codigo,

        p.nombre,

        p.precio,

        p.precio_anterior,

        p.descuento,

        p.oferta,

        p.destacado,

        p.nuevo,

        p.stock,

        p.envio_gratis,

        p.id_sucursal,

        p.id_user,

        p.id_categorias,

        p.fecha_registro,

        p.descripcion,

        p.id_provedor,

        p.Eliminado,

        p.id_marca,

        p.costo_compra,

        p.fecha_actualizado,

        p.tipo,

        COALESCE(c.nombre, '') AS categoria,

        COALESCE(m.nombre, '') AS marca,

        COALESCE(s.nombre, '') AS sucursal

    FROM producto p

    LEFT JOIN categorias c
        ON c.id_categorias = p.id_categorias
        AND c.id_user = p.id_user
        AND c.Eliminado = 0

    LEFT JOIN marcas m
        ON m.id_marca = p.id_marca
        AND m.id_user = p.id_user
        AND m.Eliminado = 0

    LEFT JOIN sucursal s
        ON s.id_sucursal = p.id_sucursal
        AND s.id_user = p.id_user
        AND s.Eliminado = 0

    WHERE

        p.idProducto = ?

        AND p.id_user = ?

        AND p.Eliminado = 0

    LIMIT 1

";

//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    error_log(
        "obtener_oferta_descuento.php - Error prepare: " .
            mysqli_error($conexion)
    );

    $respuesta["mensaje"] =
        "No se pudo preparar la consulta del producto.";

    responderJSON($respuesta, 500);
}

//=====================================================
// ASOCIAR PARÁMETROS
//=====================================================

if (!mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idProducto,
    $idUser
)) {

    error_log(
        "obtener_oferta_descuento.php - Error bind_param: " .
            mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close($stmt);

    $respuesta["mensaje"] =
        "No se pudieron asociar los parámetros de la consulta.";

    responderJSON($respuesta, 500);
}

//=====================================================
// EJECUTAR CONSULTA
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    $errorSQL = mysqli_stmt_error($stmt);

    error_log(
        "obtener_oferta_descuento.php - Error SQL: " .
            $errorSQL
    );

    mysqli_stmt_close($stmt);

    $respuesta["mensaje"] =
        "No se pudo consultar la información del producto.";

    responderJSON($respuesta, 500);
}

//=====================================================
// ASOCIAR RESULTADOS
//=====================================================
//
// Se utiliza bind_result() para mantener compatibilidad
// con servidores que no tengan mysqlnd.
//
//=====================================================

mysqli_stmt_bind_result(

    $stmt,

    $idProductoDB,
    $codigoDB,
    $nombreDB,
    $precioDB,
    $precioAnteriorDB,
    $descuentoDB,
    $ofertaDB,
    $destacadoDB,
    $nuevoDB,
    $stockDB,
    $envioGratisDB,
    $idSucursalDB,
    $idUserDB,
    $idCategoriasDB,
    $fechaRegistroDB,
    $descripcionDB,
    $idProveedorDB,
    $eliminadoDB,
    $idMarcaDB,
    $costoCompraDB,
    $fechaActualizadoDB,
    $tipoDB,
    $categoriaDB,
    $marcaDB,
    $sucursalDB
);

//=====================================================
// VERIFICAR SI EXISTE EL PRODUCTO
//=====================================================

if (!mysqli_stmt_fetch($stmt)) {

    mysqli_stmt_close($stmt);

    $respuesta["mensaje"] =
        "No se encontró el producto seleccionado.";

    responderJSON($respuesta, 404);
}

//=====================================================
// CERRAR STATEMENT
//=====================================================

mysqli_stmt_close($stmt);

//=====================================================
// NORMALIZAR DATOS
//=====================================================

//-----------------------------------------------------
// ID PRODUCTO
//-----------------------------------------------------

$idProductoDB = intval($idProductoDB);

//-----------------------------------------------------
// CÓDIGO
//-----------------------------------------------------

$codigoDB = $codigoDB !== null
    ? trim((string)$codigoDB)
    : "";

//-----------------------------------------------------
// NOMBRE
//-----------------------------------------------------

$nombreDB = $nombreDB !== null
    ? trim((string)$nombreDB)
    : "";

//-----------------------------------------------------
// PRECIO
//-----------------------------------------------------

$precioDB = $precioDB !== null
    ? floatval($precioDB)
    : 0;

//-----------------------------------------------------
// PRECIO ANTERIOR
//-----------------------------------------------------

$precioAnteriorDB = $precioAnteriorDB !== null
    ? floatval($precioAnteriorDB)
    : 0;

//-----------------------------------------------------
// DESCUENTO
//-----------------------------------------------------

$descuentoDB = $descuentoDB !== null
    ? intval($descuentoDB)
    : 0;

//-----------------------------------------------------
// OFERTA
//-----------------------------------------------------

$ofertaDB = $ofertaDB !== null
    ? intval($ofertaDB)
    : 0;

//-----------------------------------------------------
// DESTACADO
//-----------------------------------------------------

$destacadoDB = $destacadoDB !== null
    ? intval($destacadoDB)
    : 0;

//-----------------------------------------------------
// NUEVO
//-----------------------------------------------------

$nuevoDB = $nuevoDB !== null
    ? intval($nuevoDB)
    : 0;

//-----------------------------------------------------
// STOCK
//-----------------------------------------------------

$stockDB = $stockDB !== null
    ? intval($stockDB)
    : 0;

//-----------------------------------------------------
// ENVÍO GRATIS
//-----------------------------------------------------

$envioGratisDB = $envioGratisDB !== null
    ? intval($envioGratisDB)
    : 0;

//-----------------------------------------------------
// ID SUCURSAL
//-----------------------------------------------------

$idSucursalDB = $idSucursalDB !== null
    ? intval($idSucursalDB)
    : 0;

//-----------------------------------------------------
// ID USUARIO
//-----------------------------------------------------

$idUserDB = $idUserDB !== null
    ? intval($idUserDB)
    : 0;

//-----------------------------------------------------
// ID CATEGORÍA
//-----------------------------------------------------

$idCategoriasDB = $idCategoriasDB !== null
    ? intval($idCategoriasDB)
    : 0;

//-----------------------------------------------------
// FECHA REGISTRO
//-----------------------------------------------------

$fechaRegistroDB = $fechaRegistroDB !== null
    ? (string)$fechaRegistroDB
    : "";

//-----------------------------------------------------
// DESCRIPCIÓN
//-----------------------------------------------------

$descripcionDB = $descripcionDB !== null
    ? trim((string)$descripcionDB)
    : "";

//-----------------------------------------------------
// ID PROVEEDOR
//-----------------------------------------------------

$idProveedorDB = $idProveedorDB !== null
    ? intval($idProveedorDB)
    : 0;

//-----------------------------------------------------
// ELIMINADO
//-----------------------------------------------------

$eliminadoDB = $eliminadoDB !== null
    ? intval($eliminadoDB)
    : 0;

//-----------------------------------------------------
// ID MARCA
//-----------------------------------------------------

$idMarcaDB = $idMarcaDB !== null
    ? intval($idMarcaDB)
    : 0;

//-----------------------------------------------------
// COSTO COMPRA
//-----------------------------------------------------

$costoCompraDB = $costoCompraDB !== null
    ? floatval($costoCompraDB)
    : 0;

//-----------------------------------------------------
// FECHA ACTUALIZADO
//-----------------------------------------------------

$fechaActualizadoDB = $fechaActualizadoDB !== null
    ? (string)$fechaActualizadoDB
    : "";

//-----------------------------------------------------
// TIPO
//-----------------------------------------------------

$tipoDB = $tipoDB !== null
    ? trim((string)$tipoDB)
    : "";

//-----------------------------------------------------
// CATEGORÍA
//-----------------------------------------------------

$categoriaDB = $categoriaDB !== null
    ? trim((string)$categoriaDB)
    : "";

//-----------------------------------------------------
// MARCA
//-----------------------------------------------------

$marcaDB = $marcaDB !== null
    ? trim((string)$marcaDB)
    : "";

//-----------------------------------------------------
// SUCURSAL
//-----------------------------------------------------

$sucursalDB = $sucursalDB !== null
    ? trim((string)$sucursalDB)
    : "";

//=====================================================
// VALIDAR DESCUENTO
//=====================================================
//
// La columna descuento es INT.
//
// Por seguridad, el porcentaje queda limitado
// entre 0 y 100.
//
//=====================================================

if ($descuentoDB < 0) {
    $descuentoDB = 0;
}

if ($descuentoDB > 100) {
    $descuentoDB = 100;
}

//=====================================================
// CALCULAR MONTO DE DESCUENTO
//=====================================================

$montoDescuento = 0;

//=====================================================
// CALCULAR PRECIO FINAL
//=====================================================

$precioFinal = $precioDB;

//-----------------------------------------------------
// SI EL PRODUCTO TIENE DESCUENTO
//-----------------------------------------------------

if ($descuentoDB > 0) {

    $montoDescuento =
        $precioDB * ($descuentoDB / 100);

    $precioFinal =
        $precioDB - $montoDescuento;
}

//=====================================================
// VALIDAR PRECIO FINAL
//=====================================================

if ($precioFinal < 0) {

    $precioFinal = 0;
}

//=====================================================
// VALIDAR MONTO DESCUENTO
//=====================================================

if ($montoDescuento < 0) {

    $montoDescuento = 0;
}

//=====================================================
// REDONDEAR VALORES MONETARIOS
//=====================================================

$precioDB =
    round($precioDB, 2);

$precioAnteriorDB =
    round($precioAnteriorDB, 2);

$costoCompraDB =
    round($costoCompraDB, 2);

$montoDescuento =
    round($montoDescuento, 2);

$precioFinal =
    round($precioFinal, 2);

//=====================================================
// CONSTRUIR DATOS DEL PRODUCTO
//=====================================================

$producto = [

    //-------------------------------------------------
    // IDENTIFICACIÓN
    //-------------------------------------------------

    "idProducto" => $idProductoDB,

    "codigo" => $codigoDB,

    "nombre" => $nombreDB,

    //-------------------------------------------------
    // PRECIOS
    //-------------------------------------------------

    "precio" => $precioDB,

    "precio_anterior" => $precioAnteriorDB,

    "costo_compra" => $costoCompraDB,

    //-------------------------------------------------
    // OFERTA
    //-------------------------------------------------

    "oferta" => $ofertaDB,

    "descuento" => $descuentoDB,

    //-------------------------------------------------
    // INFORMACIÓN CALCULADA
    //-------------------------------------------------

    "monto_descuento" => $montoDescuento,

    "precio_final" => $precioFinal,

    //-------------------------------------------------
    // STOCK
    //-------------------------------------------------

    "stock" => $stockDB,

    //-------------------------------------------------
    // OTROS ESTADOS
    //-------------------------------------------------

    "destacado" => $destacadoDB,

    "nuevo" => $nuevoDB,

    "envio_gratis" => $envioGratisDB,

    //-------------------------------------------------
    // RELACIONES
    //-------------------------------------------------

    "id_sucursal" => $idSucursalDB,

    "id_categorias" => $idCategoriasDB,

    "id_marca" => $idMarcaDB,

    "id_provedor" => $idProveedorDB,

    //-------------------------------------------------
    // NOMBRES RELACIONADOS
    //-------------------------------------------------

    "categoria" => $categoriaDB,

    "marca" => $marcaDB,

    "sucursal" => $sucursalDB,

    //-------------------------------------------------
    // INFORMACIÓN GENERAL
    //-------------------------------------------------

    "descripcion" => $descripcionDB,

    "tipo" => $tipoDB,

    "fecha_registro" => $fechaRegistroDB,

    "fecha_actualizado" => $fechaActualizadoDB

];

//=====================================================
// RESPUESTA EXITOSA
//=====================================================

$respuesta["success"] = true;

$respuesta["mensaje"] =
    "Producto obtenido correctamente.";

$respuesta["datos"] =
    $producto;

//=====================================================
// ENVIAR RESPUESTA JSON
//=====================================================

responderJSON($respuesta, 200);
