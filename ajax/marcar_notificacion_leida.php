<?php
//======================================================
// CoDevPro Technology
// ajax/marcar_notificacion_leida.php
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
=            VALIDAR ID
======================================================*/

if (!isset($_POST["id_notificacion"])) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Notificación no válida."
    ]);

    exit;
}


$idCliente = (int) $_SESSION["idCliente"];

$idNotificacion = (int) $_POST["id_notificacion"];


/*======================================================
=            ACTUALIZAR NOTIFICACIÓN
======================================================*/

$sql = "

UPDATE notificaciones_cliente

SET leido = 1

WHERE id_notificacion = ?
AND idCliente = ?

";


$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idNotificacion,
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
