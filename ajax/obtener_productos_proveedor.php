<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_productos_proveedor.php
// Módulo: Productos del Proveedor
// Sistema: Inventa
//=====================================================

declare(strict_types=1);


//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=UTF-8");


//=====================================================
// FUNCIÓN RESPUESTA
//=====================================================

function responderJSON(
    bool $success,
    string $mensaje = "",
    array $datos = [],
    int $codigoHTTP = 200
): void {

    http_response_code($codigoHTTP);

    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "mensaje" => $mensaje
            ],
            $datos
        ),
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;


if ($idUser <= 0) {

    responderJSON(
        false,
        "Sesión no válida. Debe iniciar sesión nuevamente.",
        [],
        401
    );
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !($conexion instanceof mysqli)) {

    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos.",
        [],
        500
    );
}


//=====================================================
// CONFIGURAR CHARSET
//=====================================================

$conexion->set_charset("utf8mb4");


//=====================================================
// OBTENER PARÁMETROS
//=====================================================

$pagina = isset($_POST["pagina"])
    ? (int) $_POST["pagina"]
    : 1;


$limite = isset($_POST["limite"])
    ? (int) $_POST["limite"]
    : 10;


//-----------------------------------------------------
// Normalizar paginación
//-----------------------------------------------------

if ($pagina < 1) {
    $pagina = 1;
}

if ($limite < 1) {
    $limite = 10;
}


// Evitamos límites excesivamente grandes.

if ($limite > 100) {
    $limite = 100;
}


$buscar = isset($_POST["buscar"])
    ? trim((string) $_POST["buscar"])
    : "";


$proveedor = isset($_POST["proveedor"])
    ? trim((string) $_POST["proveedor"])
    : "";


$categoria = isset($_POST["categoria"])
    ? trim((string) $_POST["categoria"])
    : "";


$marca = isset($_POST["marca"])
    ? trim((string) $_POST["marca"])
    : "";


$stock = isset($_POST["stock"])
    ? trim((string) $_POST["stock"])
    : "todos";


$fecha = isset($_POST["fecha"])
    ? trim((string) $_POST["fecha"])
    : "";


$estado = isset($_POST["estado"])
    ? trim((string) $_POST["estado"])
    : "todos";


//=====================================================
// VALIDAR FILTROS NUMÉRICOS
//=====================================================

if ($proveedor !== "" && !ctype_digit($proveedor)) {
    $proveedor = "";
}


if ($categoria !== "" && !ctype_digit($categoria)) {
    $categoria = "";
}


if ($marca !== "" && !ctype_digit($marca)) {
    $marca = "";
}


//=====================================================
// VALIDAR FECHA
//=====================================================

if ($fecha !== "") {

    $fechaObjeto = DateTime::createFromFormat("Y-m-d", $fecha);

    $fechaValida =
        $fechaObjeto &&
        $fechaObjeto->format("Y-m-d") === $fecha;


    if (!$fechaValida) {
        $fecha = "";
    }
}


//=====================================================
// NORMALIZAR FILTRO STOCK
//=====================================================

$stocksPermitidos = [
    "todos",
    "disponible",
    "bajo",
    "agotado"
];


if (!in_array($stock, $stocksPermitidos, true)) {
    $stock = "todos";
}


//=====================================================
// NORMALIZAR FILTRO ESTADO
//=====================================================

$estadosPermitidos = [
    "todos",
    "activo",
    "inactivo"
];


if (!in_array($estado, $estadosPermitidos, true)) {
    $estado = "todos";
}


//=====================================================
// CONSTRUIR CONDICIONES
//=====================================================
//
// Todas las consultas parten de:
//
// p.id_user = ?
// p.Eliminado = 0
//
// Esto evita que un usuario pueda visualizar
// productos pertenecientes a otra cuenta.
//=====================================================

$where = [];

$parametros = [];

$tipos = "";


//-----------------------------------------------------
// USUARIO
//-----------------------------------------------------

$where[] = "p.id_user = ?";

$parametros[] = $idUser;

$tipos .= "i";


//-----------------------------------------------------
// PRODUCTOS NO ELIMINADOS
//-----------------------------------------------------

$where[] = "p.Eliminado = 0";


//=====================================================
// FILTRO BUSCAR
//=====================================================

if ($buscar !== "") {

    $where[] = "
        (
            p.nombre LIKE ?
            OR p.codigo LIKE ?
        )
    ";

    $buscarLike = "%" . $buscar . "%";

    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;

    $tipos .= "ss";
}


//=====================================================
// FILTRO PROVEEDOR
//=====================================================

if ($proveedor !== "") {

    $where[] = "p.id_provedor = ?";

    $parametros[] = (int) $proveedor;

    $tipos .= "i";
}


//=====================================================
// FILTRO CATEGORÍA
//=====================================================

if ($categoria !== "") {

    $where[] = "p.id_categorias = ?";

    $parametros[] = (int) $categoria;

    $tipos .= "i";
}


//=====================================================
// FILTRO MARCA
//=====================================================

if ($marca !== "") {

    $where[] = "p.id_marca = ?";

    $parametros[] = (int) $marca;

    $tipos .= "i";
}


//=====================================================
// FILTRO STOCK
//=====================================================

switch ($stock) {

    case "disponible":

        $where[] = "p.stock > 5";

        break;


    case "bajo":

        $where[] = "p.stock BETWEEN 1 AND 5";

        break;


    case "agotado":

        $where[] = "p.stock <= 0";

        break;


    case "todos":

    default:

        break;
}


//=====================================================
// FILTRO FECHA
//=====================================================

if ($fecha !== "") {

    $where[] = "p.fecha_registro = ?";

    $parametros[] = $fecha;

    $tipos .= "s";
}


//=====================================================
// FILTRO ESTADO
//=====================================================
//
// En producto:
//
// Eliminado = 0 -> ACTIVO
// Eliminado != 0 -> INACTIVO
//
// Pero como la consulta principal excluye Eliminado != 0,
// para que el filtro "inactivo" tenga sentido debemos
// modificar esta condición.
//=====================================================

if ($estado === "inactivo") {

    // Eliminamos la condición fija p.Eliminado = 0
    // para poder consultar productos inactivos.

    foreach ($where as $indice => $condicion) {

        if ($condicion === "p.Eliminado = 0") {

            unset($where[$indice]);

            break;
        }
    }

    $where[] = "p.Eliminado != 0";
}


if ($estado === "activo") {

    $where[] = "p.Eliminado = 0";
}


// Reindexar.

$where = array_values($where);


//=====================================================
// WHERE FINAL
//=====================================================

$whereSQL = implode("\n AND ", $where);


//=====================================================
// CONSULTAR TOTAL DE REGISTROS
//=====================================================

$sqlTotal = "
    SELECT COUNT(*) AS total
    FROM producto p

    WHERE
        {$whereSQL}
";


//=====================================================
// PREPARAR TOTAL
//=====================================================

$stmtTotal = $conexion->prepare($sqlTotal);


if (!$stmtTotal) {

    responderJSON(
        false,
        "No se pudo preparar la consulta de cantidad de productos.",
        [],
        500
    );
}


//=====================================================
// BIND TOTAL
//=====================================================

if (!empty($parametros)) {

    $stmtTotal->bind_param(
        $tipos,
        ...$parametros
    );
}


//=====================================================
// EJECUTAR TOTAL
//=====================================================

if (!$stmtTotal->execute()) {

    $stmtTotal->close();

    responderJSON(
        false,
        "No se pudo obtener el total de productos.",
        [],
        500
    );
}


//=====================================================
// OBTENER TOTAL
//=====================================================

$resultadoTotal = $stmtTotal->get_result();

$filaTotal = $resultadoTotal->fetch_assoc();

$totalRegistros = isset($filaTotal["total"])
    ? (int) $filaTotal["total"]
    : 0;


$stmtTotal->close();


//=====================================================
// CALCULAR PAGINACIÓN
//=====================================================

$totalPaginas = $totalRegistros > 0
    ? (int) ceil($totalRegistros / $limite)
    : 1;


//-----------------------------------------------------
// Corregir página fuera de rango
//-----------------------------------------------------

if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
}


$offset = ($pagina - 1) * $limite;


//=====================================================
// CONSULTA DE PRODUCTOS
//=====================================================
//
// Se obtienen:
//
// - Producto
// - Categoría
// - Marca
// - Proveedor
//
// Las relaciones usan LEFT JOIN para que el producto
// continúe apareciendo aunque alguna relación haya sido
// eliminada o no exista.
//=====================================================

$sqlProductos = "
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

        c.nombre AS categoria,

        m.nombre AS marca,

        pr.nombre AS proveedor

    FROM producto p

    LEFT JOIN categorias c
        ON c.id_categorias = p.id_categorias
        AND c.id_user = p.id_user

    LEFT JOIN marcas m
        ON m.id_marca = p.id_marca
        AND m.id_user = p.id_user

    LEFT JOIN provedores pr
        ON pr.id_provedor = p.id_provedor
        AND pr.id_user = p.id_user

    WHERE
        {$whereSQL}

    ORDER BY
        p.fecha_registro DESC,
        p.idProducto DESC

    LIMIT ? OFFSET ?
";


//=====================================================
// PREPARAR PRODUCTOS
//=====================================================

$stmtProductos = $conexion->prepare($sqlProductos);


if (!$stmtProductos) {

    responderJSON(
        false,
        "No se pudo preparar la consulta de productos.",
        [],
        500
    );
}


//=====================================================
// PARÁMETROS PRODUCTOS
//=====================================================
//
// Agregamos LIMIT y OFFSET al final.
//=====================================================

$parametrosProductos = $parametros;

$parametrosProductos[] = $limite;

$parametrosProductos[] = $offset;


$tiposProductos = $tipos . "ii";


//=====================================================
// BIND PRODUCTOS
//=====================================================

$stmtProductos->bind_param(
    $tiposProductos,
    ...$parametrosProductos
);


//=====================================================
// EJECUTAR PRODUCTOS
//=====================================================

if (!$stmtProductos->execute()) {

    $stmtProductos->close();

    responderJSON(
        false,
        "No se pudieron obtener los productos.",
        [],
        500
    );
}


//=====================================================
// RESULTADO
//=====================================================

$resultadoProductos = $stmtProductos->get_result();


//=====================================================
// ARRAY PRODUCTOS
//=====================================================

$productos = [];


//=====================================================
// RECORRER PRODUCTOS
//=====================================================

while ($producto = $resultadoProductos->fetch_assoc()) {

    $idProducto = (int) $producto["idProducto"];


    //-------------------------------------------------
    // NOMBRE
    //-------------------------------------------------

    $nombre = $producto["nombre"] !== null
        ? $producto["nombre"]
        : "Sin nombre";


    //-------------------------------------------------
    // CATEGORÍA
    //-------------------------------------------------

    $categoriaNombre = !empty($producto["categoria"])
        ? $producto["categoria"]
        : "Sin categoría";


    //-------------------------------------------------
    // MARCA
    //-------------------------------------------------

    $marcaNombre = !empty($producto["marca"])
        ? $producto["marca"]
        : "Sin marca";


    //-------------------------------------------------
    // PROVEEDOR
    //-------------------------------------------------

    $proveedorNombre = !empty($producto["proveedor"])
        ? $producto["proveedor"]
        : "Sin proveedor";


    //-------------------------------------------------
    // ESTADO
    //-------------------------------------------------

    $estadoProducto = ((int) $producto["Eliminado"] === 0)
        ? "ACTIVO"
        : "INACTIVO";


    //-------------------------------------------------
    // IMAGEN
    //-------------------------------------------------
    //
    // No enviamos el LONGBlob de imagenes por JSON.
    //
    // El JavaScript recibirá:
    //
    // mostrar_imagen.php?id=ID_PRODUCTO
    //
    // y ese archivo se encargará de devolver la
    // imagen correspondiente.
    //
    //-------------------------------------------------

    $imagen = "mostrar_imagen.php?id=" . $idProducto;


    //-------------------------------------------------
    // FECHA
    //-------------------------------------------------

    $fechaRegistro = $producto["fecha_registro"]
        ? $producto["fecha_registro"]
        : "";


    //-------------------------------------------------
    // AGREGAR PRODUCTO
    //-------------------------------------------------

    $productos[] = [

        "idProducto" => $idProducto,

        "codigo" => $producto["codigo"] ?? "",

        "nombre" => $nombre,

        "tipo" => $producto["tipo"] ?? "",

        "categoria" => $categoriaNombre,

        "marca" => $marcaNombre,

        "proveedor" => $proveedorNombre,

        "id_categorias" => isset($producto["id_categorias"])
            ? (int) $producto["id_categorias"]
            : 0,

        "id_marca" => isset($producto["id_marca"])
            ? (int) $producto["id_marca"]
            : 0,

        "id_provedor" => isset($producto["id_provedor"])
            ? (int) $producto["id_provedor"]
            : 0,

        "costo_compra" => isset($producto["costo_compra"])
            ? (float) $producto["costo_compra"]
            : 0,

        "precio" => isset($producto["precio"])
            ? (float) $producto["precio"]
            : 0,

        "precio_anterior" => isset($producto["precio_anterior"])
            ? (float) $producto["precio_anterior"]
            : 0,

        "descuento" => isset($producto["descuento"])
            ? (int) $producto["descuento"]
            : 0,

        "stock" => isset($producto["stock"])
            ? (int) $producto["stock"]
            : 0,

        "oferta" => isset($producto["oferta"])
            ? (int) $producto["oferta"]
            : 0,

        "destacado" => isset($producto["destacado"])
            ? (int) $producto["destacado"]
            : 0,

        "nuevo" => isset($producto["nuevo"])
            ? (int) $producto["nuevo"]
            : 0,

        "envio_gratis" => isset($producto["envio_gratis"])
            ? (int) $producto["envio_gratis"]
            : 0,

        "id_sucursal" => isset($producto["id_sucursal"])
            ? (int) $producto["id_sucursal"]
            : 0,

        "fecha_registro" => $fechaRegistro,

        "fecha_actualizado" => $producto["fecha_actualizado"] ?? "",

        "descripcion" => $producto["descripcion"] ?? "",

        "estado" => $estadoProducto,

        "Eliminado" => isset($producto["Eliminado"])
            ? (int) $producto["Eliminado"]
            : 0,

        "imagen" => $imagen

    ];
}


//=====================================================
// CERRAR STATEMENT
//=====================================================

$stmtProductos->close();


//=====================================================
// INFORMACIÓN PAGINACIÓN
//=====================================================

$desde = $totalRegistros > 0
    ? (($pagina - 1) * $limite) + 1
    : 0;


$hasta = $totalRegistros > 0
    ? min($pagina * $limite, $totalRegistros)
    : 0;


//=====================================================
// RESPUESTA FINAL
//=====================================================

responderJSON(
    true,
    "Productos obtenidos correctamente.",
    [

        "productos" => $productos,

        "total_registros" => $totalRegistros,

        "total_paginas" => $totalPaginas,

        "pagina" => $pagina,

        "limite" => $limite,

        "desde" => $desde,

        "hasta" => $hasta

    ]
);
