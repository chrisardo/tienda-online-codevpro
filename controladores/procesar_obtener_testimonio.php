<?php
//======================================================
// CoDevPro Technology
// controladores/procesar_obtener_testimonio.php
// Valida datos antes de obtener el testimonio
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
        "estado"  => "error",
        "mensaje" => "Debes iniciar sesión."
    ]);

    exit;
}

$idCliente = intval($_SESSION["idCliente"]);

/*======================================================
RECIBIR DATOS
======================================================*/

$idTicket   = intval($_GET["id_ticket"] ?? 0);
$idProducto = intval($_GET["id_producto"] ?? 0);

/*======================================================
VALIDAR DATOS
======================================================*/

if ($idTicket <= 0) {

    echo json_encode([
        "estado"  => "error",
        "mensaje" => "Pedido inválido."
    ]);

    exit;
}

if ($idProducto <= 0) {

    echo json_encode([
        "estado"  => "error",
        "mensaje" => "Producto inválido."
    ]);

    exit;
}

/*======================================================
VALIDAR QUE EL PEDIDO PERTENECE AL CLIENTE
======================================================*/

$sql = "

SELECT
    id_ticket_ventas
FROM ticket_ventas
WHERE
    id_ticket_ventas = ?
AND idCliente = ?
LIMIT 1

";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo json_encode([
        "estado"  => "error",
        "mensaje" => "Error al validar el pedido."
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

if (!mysqli_fetch_assoc($resultado)) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "estado"  => "error",
        "mensaje" => "El pedido no existe o no te pertenece."
    ]);

    exit;
}

mysqli_stmt_close($stmt);

/*======================================================
OBTENER TESTIMONIO
======================================================*/

require_once "obtener_testimonio.php";

/*======================================================
CERRAR CONEXIÓN
======================================================*/

mysqli_close($conexion);
