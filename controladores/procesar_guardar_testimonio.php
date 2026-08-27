<?php
//======================================================
// CoDevPro Technology
// controladores/procesar_guardar_testimonio.php
// Valida datos y decide si registrar o editar
//======================================================

require_once "conexion.php";

/*======================================================
VALIDAR SESIÓN
======================================================*/

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Debes iniciar sesión."
    ]);

    exit;
}

$idCliente = intval($_SESSION["idCliente"]);
/*=========================================
OBTENER EL id_user DEL PEDIDO
=========================================*/


/*======================================================
RECIBIR DATOS
======================================================*/

$idTicket     = intval($_POST["id_ticket"] ?? 0);
$idProducto   = intval($_POST["id_producto"] ?? 0);
$calificacion = intval($_POST["calificacion"] ?? 0);
$comentario   = trim($_POST["comentario"] ?? "");

/*======================================================
VALIDACIONES
======================================================*/

if ($idTicket <= 0) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Pedido inválido."
    ]);

    exit;
}

if ($idProducto <= 0) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Producto inválido."
    ]);

    exit;
}

if ($calificacion < 1 || $calificacion > 5) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Selecciona una calificación válida."
    ]);

    exit;
}

if (strlen($comentario) < 5) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "El comentario debe tener al menos 5 caracteres."
    ]);

    exit;
}

if (strlen($comentario) > 500) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "El comentario supera el máximo permitido."
    ]);

    exit;
}

/*======================================================
VALIDAR PEDIDO
======================================================*/

$sql = "

SELECT
    id_ticket_ventas,
    id_user,
    estado_envio
FROM ticket_ventas

WHERE
    id_ticket_ventas = ?
    AND idCliente = ?

LIMIT 1

";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => mysqli_error($conexion)
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idTicket,
    $idCliente
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$pedido = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

if (!$pedido) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "El pedido no existe."
    ]);

    exit;
}

if ($pedido["estado_envio"] != "ENTREGADO") {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Solo puedes calificar pedidos entregados."
    ]);

    exit;
}
$idUser = intval($pedido["id_user"]);
/*======================================================
VERIFICAR SI YA EXISTE TESTIMONIO
======================================================*/

$sql = "

SELECT
    id_testimonio

FROM testimonios

WHERE
    id_ticket_ventas = ?
    AND idProducto = ?
    AND idCliente = ?

LIMIT 1

";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => mysqli_error($conexion)
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "iii",
    $idTicket,
    $idProducto,
    $idCliente
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$testimonio = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

/*======================================================
REGISTRAR O EDITAR
======================================================*/

if ($testimonio) {

    $idTestimonio = intval($testimonio["id_testimonio"]);

    require_once "editar_testimonio.php";
} else {

    require_once "registrar_testimonio.php";
}

/*======================================================
CERRAR CONEXIÓN
======================================================*/

mysqli_close($conexion);
