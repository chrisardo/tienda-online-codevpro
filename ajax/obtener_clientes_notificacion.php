<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_clientes_notificacion.php
// Módulo: Notificaciones
// Sistema: Inventa
//=====================================================

"use strict";

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN
|--------------------------------------------------------------------------
*/

header("Content-Type: application/json; charset=utf-8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| DESACTIVAR SALIDA DE ERRORES EN PANTALLA
|--------------------------------------------------------------------------
| Los errores PHP no deben mezclarse con el JSON.
|--------------------------------------------------------------------------
*/

ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");

/*
|--------------------------------------------------------------------------
| CONEXIÓN
|--------------------------------------------------------------------------
*/

require_once "../controladores/conexion.php";

/*
|--------------------------------------------------------------------------
| FUNCIÓN RESPUESTA JSON
|--------------------------------------------------------------------------
*/

function responderJSON(
    bool $success,
    string $mensaje = "",
    array $datos = [],
    int $codigoHTTP = 200
) {

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

/*
|--------------------------------------------------------------------------
| VALIDAR CONEXIÓN
|--------------------------------------------------------------------------
*/

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

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;


if ($idUser <= 0) {

    http_response_code(401);

    echo json_encode([

        "success" => false,

        "estado" => false,

        "mensaje" =>
        "La sesión de usuario no es válida."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$sql = "
    SELECT
        c.idCliente,
        c.nombre,
        c.dni_o_ruc,
        c.estado
    FROM clientes AS c
    WHERE
        c.id_user = ?
        AND c.Eliminado = 0
        AND c.estado = 'ACTIVO'
    ORDER BY
        c.nombre ASC,
        c.idCliente ASC
";

/*
|--------------------------------------------------------------------------
| PREPARAR CONSULTA
|--------------------------------------------------------------------------
*/

$stmt =
    mysqli_prepare(
        $conexion,
        $sql
    );

if (!$stmt) {

    responderJSON(
        false,
        "No se pudo preparar la consulta de clientes.",
        [],
        500
    );
}

/*
|--------------------------------------------------------------------------
| VINCULAR ID USUARIO
|--------------------------------------------------------------------------
*/

if (
    !mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    )
) {

    mysqli_stmt_close($stmt);

    responderJSON(
        false,
        "No se pudieron vincular los parámetros de la consulta.",
        [],
        500
    );
}

/*
|--------------------------------------------------------------------------
| EJECUTAR
|--------------------------------------------------------------------------
*/

if (
    !mysqli_stmt_execute($stmt)
) {

    $error =
        mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);

    error_log(
        "Error obtener_clientes_notificacion.php: " .
            $error
    );

    responderJSON(
        false,
        "No se pudieron consultar los clientes.",
        [],
        500
    );
}

/*
|--------------------------------------------------------------------------
| OBTENER RESULTADO
|--------------------------------------------------------------------------
*/

$resultado =
    mysqli_stmt_get_result($stmt);

if ($resultado === false) {

    $error =
        mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);

    error_log(
        "Error obteniendo resultado de clientes: " .
            $error
    );

    responderJSON(
        false,
        "No se pudo obtener el resultado de la consulta.",
        [],
        500
    );
}

/*
|--------------------------------------------------------------------------
| CONSTRUIR LISTA
|--------------------------------------------------------------------------
*/

$clientes = [];

while (
    $fila =
    mysqli_fetch_assoc($resultado)
) {

    /*
    |--------------------------------------------------------------------------
    | ID
    |--------------------------------------------------------------------------
    */

    $idCliente =
        isset($fila["idCliente"])
        ? (int) $fila["idCliente"]
        : 0;

    /*
    |--------------------------------------------------------------------------
    | NOMBRE
    |--------------------------------------------------------------------------
    */

    $nombre =
        isset($fila["nombre"])
        ? trim(
            (string) $fila["nombre"]
        )
        : "";

    /*
    |--------------------------------------------------------------------------
    | DNI / RUC
    |--------------------------------------------------------------------------
    */

    $dni =
        isset($fila["dni_o_ruc"])
        ? trim(
            (string) $fila["dni_o_ruc"]
        )
        : "";

    /*
    |--------------------------------------------------------------------------
    | VALIDAR ID
    |--------------------------------------------------------------------------
    */

    if ($idCliente <= 0) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAR NOMBRE
    |--------------------------------------------------------------------------
    */

    if ($nombre === "") {
        $nombre = "Cliente sin nombre";
    }

    /*
    |--------------------------------------------------------------------------
    | AGREGAR CLIENTE
    |--------------------------------------------------------------------------
    */

    $clientes[] = [

        "idCliente" =>
        $idCliente,

        "id_cliente" =>
        $idCliente,

        "id" =>
        $idCliente,

        "nombre" =>
        $nombre,

        "nombre_completo" =>
        $nombre,

        "cliente" =>
        $nombre,

        "dni" =>
        $dni,

        "dni_o_ruc" =>
        $dni,

        "estado" =>
        isset($fila["estado"])
            ? $fila["estado"]
            : "ACTIVO"

    ];
}

/*
|--------------------------------------------------------------------------
| CERRAR
|--------------------------------------------------------------------------
*/

mysqli_free_result($resultado);

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| RESPUESTA
|--------------------------------------------------------------------------
*/

responderJSON(
    true,
    "Clientes obtenidos correctamente.",
    [
        "clientes" => $clientes,
        "total" => count($clientes)
    ]
);
