<?php
//======================================================
// CoDevPro Technology
// ajax/marcar_todas_notificaciones_leidas.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


/*======================================================
=            VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Debe iniciar sesión."
    ]);

    exit;
}


/*======================================================
=            OBTENER ID DEL CLIENTE
======================================================*/

$idCliente = (int) $_SESSION["idCliente"];


/*======================================================
=            ACTUALIZAR NOTIFICACIONES
======================================================*/

$sql = "

UPDATE notificaciones_cliente

SET leido = 1

WHERE idCliente = ?
AND Eliminado = 0

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
    "i",
    $idCliente
);


$correcto = mysqli_stmt_execute($stmt);


/*======================================================
=            RESPUESTA
======================================================*/

if ($correcto) {

    echo json_encode([

        "estado" => "ok"

    ]);
} else {

    echo json_encode([

        "estado" => "error"

    ]);
}


mysqli_stmt_close($stmt);

mysqli_close($conexion);

exit;
