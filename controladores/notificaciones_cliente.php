<?php
//======================================================
// CoDevPro Technology
// controladores/notificaciones_cliente.php
//======================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "tipos_notificaciones.php";

/*======================================================
=            CREAR NOTIFICACIÓN
======================================================*/

function crearNotificacionCliente(
    $conexion,
    $idCliente,
    $titulo,
    $mensaje,
    $tipo,
    $url = "#"
) {
    /*======================================================
=            OBTENER EL TIPO
======================================================*/

    $informacionTipo = obtenerTipoNotificacion($tipo);


    /*======================================================
=            OBTENER ICONO Y COLOR
======================================================*/

    $icono = $informacionTipo["icono"];

    $color = $informacionTipo["color"];
    $sql = "

        INSERT INTO notificaciones_cliente(

    idCliente,
    titulo,
    mensaje,
    icono,
    color,
    url,
    leido,
    fecha,
    Eliminado,
    tipo

)

        VALUES(

?, ?, ?, ?, ?, ?, 0, NOW(), 0, ?

)

    ";


    $stmt = mysqli_prepare($conexion, $sql);


    if (!$stmt) {

        return false;
    }


    mysqli_stmt_bind_param(

        $stmt,
        "issssss",
        $idCliente,
        $titulo,
        $mensaje,
        $icono,
        $color,
        $url,
        $tipo

    );


    $resultado = mysqli_stmt_execute($stmt);


    mysqli_stmt_close($stmt);


    return $resultado;
}


/*======================================================
=            ELIMINAR NOTIFICACIÓN (LÓGICO)
======================================================*/

function eliminarNotificacionCliente(
    $conexion,
    $idNotificacion,
    $idCliente
) {

    $sql = "

        UPDATE notificaciones_cliente

        SET Eliminado = 1

        WHERE id_notificacion = ?
        AND idCliente = ?

    ";


    $stmt = mysqli_prepare($conexion, $sql);


    if (!$stmt) {

        return false;
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idNotificacion,
        $idCliente
    );


    $resultado = mysqli_stmt_execute($stmt);


    mysqli_stmt_close($stmt);


    return $resultado;
}


/*======================================================
=            MARCAR COMO LEÍDA
======================================================*/

function marcarNotificacionLeida(
    $conexion,
    $idNotificacion,
    $idCliente
) {

    $sql = "

        UPDATE notificaciones_cliente

        SET leido = 1

        WHERE id_notificacion = ?
        AND idCliente = ?

    ";


    $stmt = mysqli_prepare($conexion, $sql);


    if (!$stmt) {

        return false;
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idNotificacion,
        $idCliente
    );


    $resultado = mysqli_stmt_execute($stmt);


    mysqli_stmt_close($stmt);


    return $resultado;
}
