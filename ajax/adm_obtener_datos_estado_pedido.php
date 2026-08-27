<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_obtener_datos_estado_pedido.php
// Módulo: Gestión de Pedidos de Clientes
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../controladores/conexion.php";

header("Content-Type: application/json; charset=UTF-8");


//=====================================================
// OBTENER USUARIO DE LA SESIÓN
//=====================================================

$idUser = intval($_SESSION["idUser"] ?? 0);

if ($idUser <= 0) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Sesión no válida."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// OBTENER ID DEL PEDIDO
//=====================================================

$idPedido = intval($_GET["id"] ?? 0);

if ($idPedido <= 0) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "ID de pedido no válido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// CONSULTAR PEDIDO
//
// IMPORTANTE:
//
// El repartidor se obtiene directamente de
// ticket_ventas.id_empleado.
//
// No dependemos del botón de la tabla.
//=====================================================

$sql = "SELECT

            tv.id_ticket_ventas,
            tv.id_user,
            tv.estado_envio,
            tv.id_empleado,
            tv.observacion_envio,

            e.id_empleado AS empleado_id,
            e.nombre AS empleado_nombre,
            e.apellido AS empleado_apellido,
            e.celular AS empleado_celular,
            e.email AS empleado_email,
            e.estado AS empleado_estado,

            r.id_rol,
            r.nombre AS empleado_rol

        FROM ticket_ventas tv

        LEFT JOIN empleados e
            ON e.id_empleado = tv.id_empleado
            AND e.id_user = tv.id_user

        LEFT JOIN rol r
            ON r.id_rol = e.id_rol
            AND r.id_user = e.id_user

        WHERE tv.id_ticket_ventas = ?
        AND tv.id_user = ?

        LIMIT 1";


//=====================================================
// PREPARAR
//=====================================================

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

if (!$stmt) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se pudo preparar la consulta."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// PARÁMETROS
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idPedido,
    $idUser
);


//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se pudo ejecutar la consulta."
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmt);

    exit;
}


//=====================================================
// RESULTADO
//=====================================================

$resultado =
    mysqli_stmt_get_result($stmt);


if (!$resultado) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se pudo obtener el resultado."
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmt);

    exit;
}


$pedido =
    mysqli_fetch_assoc($resultado);


//=====================================================
// PEDIDO NO ENCONTRADO
//=====================================================

if (!$pedido) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "El pedido no existe o no pertenece al usuario actual."
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmt);

    exit;
}


//=====================================================
// DATOS DEL REPARTIDOR
//=====================================================

$idEmpleado =
    intval($pedido["id_empleado"] ?? 0);


$repartidor = null;


if ($idEmpleado > 0) {

    $nombreCompleto =
        trim(
            ($pedido["empleado_nombre"] ?? "") .
            " " .
            ($pedido["empleado_apellido"] ?? "")
        );


    $repartidor = [

        "id" =>
            $idEmpleado,

        "nombre" =>
            $pedido["empleado_nombre"] ?? "",

        "apellido" =>
            $pedido["empleado_apellido"] ?? "",

        "nombre_completo" =>
            $nombreCompleto,

        "celular" =>
            $pedido["empleado_celular"] ?? "",

        "email" =>
            $pedido["empleado_email"] ?? "",

        "estado" =>
            $pedido["empleado_estado"] ?? "",

        "id_rol" =>
            intval($pedido["id_rol"] ?? 0),

        "rol" =>
            $pedido["empleado_rol"] ?? ""

    ];

}


//=====================================================
// CERRAR
//=====================================================

mysqli_stmt_close($stmt);


//=====================================================
// RESPUESTA
//=====================================================

echo json_encode([

    "estado" => "ok",

    "mensaje" => "Datos del pedido obtenidos correctamente.",

    "pedido" => [

        "id" =>
            intval($pedido["id_ticket_ventas"]),

        "estado" =>
            $pedido["estado_envio"] ?? "",

        "id_empleado" =>
            $idEmpleado,

        "observacion" =>
            $pedido["observacion_envio"] ?? ""

    ],

    "repartidor" =>
        $repartidor

], JSON_UNESCAPED_UNICODE);