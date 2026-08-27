<?php
//======================================================
// CoDevPro Technology
// Archivo: ajax/confirmar_entrega_cliente.php
// Módulo: Mis Pedidos
// Sistema: Inventa
//======================================================

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}


/*======================================================
CABECERA
======================================================*/

header(
    "Content-Type: application/json; charset=UTF-8"
);


/*======================================================
CONEXIÓN
======================================================*/

require_once "../controladores/conexion.php";


/*======================================================
RESPUESTA POR DEFECTO
======================================================*/

$respuesta = [

    "success" => false,

    "mensaje" => "No fue posible confirmar la entrega."

];


/*======================================================
VALIDAR CLIENTE
======================================================*/

$idCliente = isset($_SESSION["idCliente"])
    ? (int)$_SESSION["idCliente"]
    : 0;


if ($idCliente <= 0) {

    $respuesta["mensaje"] =
        "La sesión del cliente ha expirado.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*======================================================
OBTENER PEDIDO
======================================================*/

$idPedido = isset($_POST["id_pedido"])
    ? (int)$_POST["id_pedido"]
    : 0;


if ($idPedido <= 0) {

    $respuesta["mensaje"] =
        "El pedido seleccionado no es válido.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*======================================================
BUSCAR PEDIDO
======================================================*/

$sqlBuscar = "

    SELECT

        id_ticket_ventas,

        idCliente,

        estado_envio

    FROM ticket_ventas

    WHERE

        id_ticket_ventas = ?

        AND

        idCliente = ?

    LIMIT 1

";


$stmtBuscar = mysqli_prepare(
    $conexion,
    $sqlBuscar
);


if (!$stmtBuscar) {

    $respuesta["mensaje"] =
        "No fue posible consultar el pedido.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


mysqli_stmt_bind_param(
    $stmtBuscar,
    "ii",
    $idPedido,
    $idCliente
);


if (!mysqli_stmt_execute($stmtBuscar)) {

    mysqli_stmt_close($stmtBuscar);

    $respuesta["mensaje"] =
        "No fue posible consultar el pedido.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


$resultado = mysqli_stmt_get_result(
    $stmtBuscar
);


$pedido = mysqli_fetch_assoc($resultado);


mysqli_stmt_close($stmtBuscar);


/*======================================================
VALIDAR EXISTENCIA
======================================================*/

if (!$pedido) {

    $respuesta["mensaje"] =
        "El pedido no existe o no pertenece a tu cuenta.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*======================================================
ESTADO ACTUAL
======================================================*/

$estadoActual = strtoupper(
    trim(
        $pedido["estado_envio"] ?? ""
    )
);


/*======================================================
VALIDAR QUE ESTÉ EN CAMINO
======================================================*/

$estadosEnCamino = [

    "ASIGNADO",

    "OBTENIDO",

    "ENVIADO"

];


if (!in_array(
    $estadoActual,
    $estadosEnCamino,
    true
)) {

    $respuesta["mensaje"] =
        "El pedido ya no se encuentra en camino y no puede ser confirmado.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*======================================================
ACTUALIZAR ESTADO
======================================================*/

$sqlActualizar = "

    UPDATE ticket_ventas

    SET

        estado_envio = 'ENTREGADO'

    WHERE

        id_ticket_ventas = ?

        AND

        idCliente = ?

        AND

        estado_envio IN (
            'ASIGNADO',
            'OBTENIDO',
            'ENVIADO'
        )

";


$stmtActualizar = mysqli_prepare(
    $conexion,
    $sqlActualizar
);


if (!$stmtActualizar) {

    $respuesta["mensaje"] =
        "No fue posible actualizar el estado del pedido.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


mysqli_stmt_bind_param(
    $stmtActualizar,
    "ii",
    $idPedido,
    $idCliente
);


if (!mysqli_stmt_execute($stmtActualizar)) {

    mysqli_stmt_close($stmtActualizar);

    $respuesta["mensaje"] =
        "No fue posible confirmar la entrega.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*======================================================
VERIFICAR ACTUALIZACIÓN
======================================================*/

$filasActualizadas =
    mysqli_stmt_affected_rows($stmtActualizar);


mysqli_stmt_close($stmtActualizar);


if ($filasActualizadas <= 0) {

    $respuesta["mensaje"] =
        "El pedido ya fue actualizado o ya no se encuentra en camino.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*======================================================
ÉXITO
======================================================*/

$respuesta = [

    "success" => true,

    "mensaje" =>
    "Entrega confirmada correctamente.",

    "estado" =>
    "ENTREGADO",

    "id_pedido" =>
    $idPedido

];


echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
