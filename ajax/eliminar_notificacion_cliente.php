<?php
//======================================================
// CoDevPro Technology
// ajax/eliminar_notificacion_cliente.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


/*======================================================
=            VALIDAR SESIÓN DEL CLIENTE
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Debe iniciar sesión."
    ]);

    exit;
}


/*======================================================
=            VALIDAR ID DE LA NOTIFICACIÓN
======================================================*/

if (!isset($_POST["id_notificacion"])) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se recibió la notificación."
    ]);

    exit;
}


/*======================================================
=            OBTENER DATOS
======================================================*/

$idCliente = (int) $_SESSION["idCliente"];

$idNotificacion = (int) $_POST["id_notificacion"];


/*======================================================
=            CONSULTA SQL
======================================================*/

$sql = "

UPDATE notificaciones_cliente

SET Eliminado = 1

WHERE id_notificacion = ?
AND idCliente = ?

";


$stmt = mysqli_prepare($conexion, $sql);


/*======================================================
=            VALIDAR PREPARE
======================================================*/

if (!$stmt) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => mysqli_error($conexion)
    ]);

    exit;
}


/*======================================================
=            ASIGNAR PARÁMETROS
======================================================*/

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idNotificacion,
    $idCliente
);


/*======================================================
=            EJECUTAR CONSULTA
======================================================*/

$correcto = mysqli_stmt_execute($stmt);


/*======================================================
=            VALIDAR RESULTADO
======================================================*/

if ($correcto) {

    echo json_encode([
        "estado" => "ok",
        "mensaje" => "Notificación eliminada correctamente."
    ]);
} else {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se pudo eliminar la notificación."
    ]);
}


/*======================================================
=            CERRAR CONEXIONES
======================================================*/

mysqli_stmt_close($stmt);

mysqli_close($conexion);

exit;
