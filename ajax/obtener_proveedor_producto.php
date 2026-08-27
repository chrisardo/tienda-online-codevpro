<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_proveedor_producto.php
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
// FUNCIÓN RESPUESTA JSON
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
// OBTENER ID PRODUCTO
//=====================================================

$idProducto = isset($_POST["idProducto"])
    ? (int) $_POST["idProducto"]
    : 0;


//=====================================================
// VALIDAR ID PRODUCTO
//=====================================================

if ($idProducto <= 0) {

    responderJSON(
        false,
        "El ID del producto no es válido.",
        [],
        400
    );
}


//=====================================================
// CONSULTAR PRODUCTO
//=====================================================
//
// IMPORTANTE:
//
// Se valida:
//
// 1. Que el producto exista.
// 2. Que pertenezca al usuario actual.
// 3. Se obtiene el proveedor actualmente asignado.
//
//=====================================================

$sql = "
    SELECT

        p.idProducto,

        p.nombre,

        p.codigo,

        p.id_provedor,

        pr.nombre AS proveedor

    FROM producto p

    LEFT JOIN provedores pr
        ON pr.id_provedor = p.id_provedor
        AND pr.id_user = p.id_user

    WHERE
        p.idProducto = ?
        AND p.id_user = ?

    LIMIT 1
";


//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = $conexion->prepare($sql);


if (!$stmt) {

    responderJSON(
        false,
        "No se pudo preparar la consulta del producto.",
        [],
        500
    );
}


//=====================================================
// BIND
//=====================================================

$stmt->bind_param(
    "ii",
    $idProducto,
    $idUser
);


//=====================================================
// EJECUTAR
//=====================================================

if (!$stmt->execute()) {

    $stmt->close();

    responderJSON(
        false,
        "No se pudo consultar el producto.",
        [],
        500
    );
}


//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = $stmt->get_result();


//=====================================================
// VALIDAR PRODUCTO
//=====================================================

if ($resultado->num_rows === 0) {

    $stmt->close();

    responderJSON(
        false,
        "El producto no existe o no pertenece a su cuenta.",
        [],
        404
    );
}


//=====================================================
// OBTENER PRODUCTO
//=====================================================

$producto = $resultado->fetch_assoc();


//=====================================================
// CERRAR STATEMENT
//=====================================================

$stmt->close();


//=====================================================
// DATOS DEL PROVEEDOR
//=====================================================

$idProveedor = isset($producto["id_provedor"])
    ? (int) $producto["id_provedor"]
    : 0;


$nombreProveedor = !empty($producto["proveedor"])
    ? $producto["proveedor"]
    : "";


//=====================================================
// RESPUESTA FINAL
//=====================================================

responderJSON(
    true,
    "Proveedor del producto obtenido correctamente.",
    [

        "producto" => [

            "idProducto" => (int) $producto["idProducto"],

            "nombre" => $producto["nombre"] ?? "",

            "codigo" => $producto["codigo"] ?? "",

            "id_provedor" => $idProveedor,

            "proveedor" => $nombreProveedor,

            "tiene_proveedor" => $idProveedor > 0

        ],

        "idProducto" => (int) $producto["idProducto"],

        "id_provedor" => $idProveedor,

        "proveedor" => $nombreProveedor,

        "tiene_proveedor" => $idProveedor > 0

    ]
);
