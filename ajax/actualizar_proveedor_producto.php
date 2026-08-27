<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_proveedor_producto.php
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
// JSON
//=====================================================

header(
    "Content-Type: application/json; charset=UTF-8"
);


//=====================================================
// RESPUESTA JSON
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


if (
    !isset($conexion) ||
    !($conexion instanceof mysqli)
) {

    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos.",
        [],
        500
    );
}


$conexion->set_charset("utf8mb4");


//=====================================================
// RECIBIR DATOS
//=====================================================

$idProducto = isset($_POST["idProducto"])
    ? (int) $_POST["idProducto"]
    : 0;


$idProveedor = isset($_POST["idProveedor"])
    ? (int) $_POST["idProveedor"]
    : 0;


//=====================================================
// VALIDAR PRODUCTO
//=====================================================

if ($idProducto <= 0) {

    responderJSON(
        false,
        "El producto seleccionado no es válido.",
        [],
        400
    );
}


//=====================================================
// VALIDAR PROVEEDOR
//=====================================================

if ($idProveedor <= 0) {

    responderJSON(
        false,
        "Debes seleccionar un proveedor.",
        [],
        400
    );
}


//=====================================================
// VALIDAR PRODUCTO
//=====================================================
//
// Verificamos que el producto pertenezca al usuario
// que tiene la sesión iniciada.
//=====================================================

$sqlProducto = "

    SELECT
        idProducto,
        id_provedor

    FROM producto

    WHERE
        idProducto = ?
        AND id_user = ?

    LIMIT 1

";


$stmtProducto =
    $conexion->prepare($sqlProducto);


if (!$stmtProducto) {

    responderJSON(
        false,
        "No se pudo validar el producto.",
        [],
        500
    );
}


$stmtProducto->bind_param(
    "ii",
    $idProducto,
    $idUser
);


if (!$stmtProducto->execute()) {

    $stmtProducto->close();

    responderJSON(
        false,
        "No se pudo validar el producto.",
        [],
        500
    );
}


$resultadoProducto =
    $stmtProducto->get_result();


$producto =
    $resultadoProducto->fetch_assoc();


$stmtProducto->close();


if (!$producto) {

    responderJSON(
        false,
        "El producto no existe o no pertenece al usuario.",
        [],
        404
    );
}


//=====================================================
// VALIDAR PROVEEDOR
//=====================================================
//
// El proveedor también debe pertenecer al mismo
// usuario.
//=====================================================

$sqlProveedor = "

    SELECT

        id_provedor,

        nombre

    FROM provedores

    WHERE
        id_provedor = ?
        AND id_user = ?
        AND Eliminado = 0

    LIMIT 1

";


$stmtProveedor =
    $conexion->prepare($sqlProveedor);


if (!$stmtProveedor) {

    responderJSON(
        false,
        "No se pudo validar el proveedor.",
        [],
        500
    );
}


$stmtProveedor->bind_param(
    "ii",
    $idProveedor,
    $idUser
);


if (!$stmtProveedor->execute()) {

    $stmtProveedor->close();

    responderJSON(
        false,
        "No se pudo validar el proveedor.",
        [],
        500
    );
}


$resultadoProveedor =
    $stmtProveedor->get_result();


$proveedor =
    $resultadoProveedor->fetch_assoc();


$stmtProveedor->close();


if (!$proveedor) {

    responderJSON(
        false,
        "El proveedor seleccionado no existe, está inactivo o no pertenece al usuario.",
        [],
        404
    );
}


//=====================================================
// VERIFICAR SI YA ES EL PROVEEDOR ACTUAL
//=====================================================

$idProveedorAnterior =
    !empty($producto["id_provedor"])
    ? (int) $producto["id_provedor"]
    : 0;


if (
    $idProveedorAnterior ===
    $idProveedor
) {

    responderJSON(
        true,
        "El producto ya tiene asignado este proveedor.",
        [
            "idProducto" =>
            $idProducto,

            "idProveedor" =>
            $idProveedor,

            "proveedor" =>
            $proveedor["nombre"]
        ]
    );
}


//=====================================================
// ACTUALIZAR
//=====================================================

$sqlActualizar = "

    UPDATE producto

    SET

        id_provedor = ?,

        fecha_actualizado = NOW()

    WHERE

        idProducto = ?

        AND id_user = ?

    LIMIT 1

";


$stmtActualizar =
    $conexion->prepare(
        $sqlActualizar
    );


if (!$stmtActualizar) {

    responderJSON(
        false,
        "No se pudo preparar la actualización.",
        [],
        500
    );
}


$stmtActualizar->bind_param(
    "iii",
    $idProveedor,
    $idProducto,
    $idUser
);


//=====================================================
// EJECUTAR UPDATE
//=====================================================

if (!$stmtActualizar->execute()) {

    $error =
        $stmtActualizar->error;

    $stmtActualizar->close();

    responderJSON(
        false,
        "No se pudo actualizar el proveedor: " .
            $error,
        [],
        500
    );
}


$filasAfectadas =
    $stmtActualizar->affected_rows;


$stmtActualizar->close();


//=====================================================
// RESPUESTA
//=====================================================

responderJSON(
    true,
    "El proveedor del producto se actualizó correctamente.",
    [
        "idProducto" =>
        $idProducto,

        "idProveedor" =>
        $idProveedor,

        "proveedor" =>
        $proveedor["nombre"],

        "filas_afectadas" =>
        $filasAfectadas
    ]
);
