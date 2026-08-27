<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_notificacion.php
// Módulo: Notificaciones
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// SESIÓN
//=====================================================

session_start();

//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=utf-8");

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// FUNCIÓN RESPUESTA
//=====================================================

function responder($success, $mensaje = "", $datos = [])
{
    echo json_encode(
        [
            "success" => $success,
            "mensaje" => $mensaje,
            "notificacion" => $datos
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    responder(
        false,
        "No se pudo establecer la conexión con la base de datos."
    );
}

//=====================================================
// OBTENER ID
//=====================================================

$idNotificacion = isset($_GET["id_notificacion"])
    ? (int) $_GET["id_notificacion"]
    : 0;

// También aceptamos "id" para mantener compatibilidad
// con posibles llamadas desde JavaScript.

if ($idNotificacion <= 0 && isset($_GET["id"])) {

    $idNotificacion = (int) $_GET["id"];
}

//=====================================================
// VALIDAR ID
//=====================================================

if ($idNotificacion <= 0) {

    responder(
        false,
        "El ID de la notificación no es válido."
    );
}

//=====================================================
// CONSULTAR NOTIFICACIÓN
//=====================================================
//
// Tabla:
// notificaciones_cliente
//
// Columnas:
// id_notificacion
// idCliente
// titulo
// mensaje
// icono
// color
// url
// leido
// fecha
// Eliminado
//
//=====================================================

$sql = "
    SELECT
        n.id_notificacion,
        n.idCliente,
        n.titulo,
        n.mensaje,
        n.icono,
        n.color,
        n.url,
        n.leido,
        n.fecha,
        n.Eliminado
    FROM notificaciones_cliente n
    WHERE n.id_notificacion = ?
      AND n.Eliminado = 0
    LIMIT 1
";

//=====================================================
// PREPARAR
//=====================================================

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    responder(
        false,
        "No se pudo preparar la consulta de la notificación."
    );
}

//=====================================================
// PARÁMETROS
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idNotificacion
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    responder(
        false,
        "No se pudo consultar la notificación."
    );
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado) {

    mysqli_stmt_close($stmt);

    responder(
        false,
        "No se pudo obtener el resultado de la consulta."
    );
}

//=====================================================
// BUSCAR NOTIFICACIÓN
//=====================================================

$notificacion = mysqli_fetch_assoc($resultado);

//=====================================================
// CERRAR
//=====================================================

mysqli_free_result($resultado);

mysqli_stmt_close($stmt);

//=====================================================
// VALIDAR EXISTENCIA
//=====================================================

if (!$notificacion) {

    responder(
        false,
        "La notificación no existe o fue eliminada."
    );
}

//=====================================================
// NORMALIZAR DATOS
//=====================================================

$notificacion["id_notificacion"] = (int) $notificacion["id_notificacion"];

$notificacion["idCliente"] = (int) $notificacion["idCliente"];

$notificacion["leido"] = (int) $notificacion["leido"];

$notificacion["Eliminado"] = (int) $notificacion["Eliminado"];

$notificacion["titulo"] = $notificacion["titulo"] ?? "";

$notificacion["mensaje"] = $notificacion["mensaje"] ?? "";

$notificacion["icono"] = $notificacion["icono"] ?? "bi-bell-fill";

$notificacion["color"] = $notificacion["color"] ?? "primary";

$notificacion["url"] = $notificacion["url"] ?? "";

$notificacion["fecha"] = $notificacion["fecha"] ?? "";

//=====================================================
// RESPUESTA
//=====================================================

responder(
    true,
    "Notificación obtenida correctamente.",
    $notificacion
);

//=====================================================
// FIN
//=====================================================