<?php
//======================================================
// CoDevPro Technology
// ajax/eliminar_notificaciones_leidas.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


/*======================================================
=            VALIDAR CONEXIÓN
======================================================*/

if (!isset($conexion) || !$conexion) {

    echo json_encode([

        "estado" => "error",
        "mensaje" => "No se pudo conectar con la base de datos."

    ]);

    exit;
}


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
=            ELIMINAR NOTIFICACIONES LEÍDAS
======================================================*/

$sql = "

UPDATE notificaciones_cliente

SET Eliminado = 1

WHERE idCliente = ?
AND leido = 1
AND Eliminado = 0

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
=            BIND PARAM
======================================================*/

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idCliente
);


/*======================================================
=            EJECUTAR CONSULTA
======================================================*/

$correcto = mysqli_stmt_execute($stmt);


/*======================================================
=            VALIDAR EJECUCIÓN
======================================================*/

if (!$correcto) {

    echo json_encode([

        "estado" => "error",
        "mensaje" => "No se pudieron eliminar las notificaciones."

    ]);

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);

    exit;
}


/*======================================================
=            OBTENER FILAS AFECTADAS
======================================================*/

$cantidadEliminadas = mysqli_stmt_affected_rows($stmt);


/*======================================================
=            RESPUESTA EXITOSA
======================================================*/

echo json_encode([

    "estado" => "ok",
    "mensaje" => "Notificaciones eliminadas correctamente.",
    "cantidad" => $cantidadEliminadas

], JSON_UNESCAPED_UNICODE);


/*======================================================
=            CERRAR RECURSOS
======================================================*/

mysqli_stmt_close($stmt);

mysqli_close($conexion);

exit;
