<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_notificaciones_cliente.php
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
        "mensaje" => "Debes iniciar sesión."
    ]);
    exit;
}


/*======================================================
=            OBTENER ID DEL CLIENTE
======================================================*/

$idCliente = (int) $_SESSION["idCliente"];
/*======================================================
=            FECHA INTELIGENTE
======================================================*/

function fechaInteligente($fecha)
{
    $fechaNotificacion = strtotime($fecha);

    $fechaActual = time();

    $diferencia = $fechaActual - $fechaNotificacion;


    // Segundos

    if ($diferencia < 60) {

        return "Hace unos segundos";
    }


    // Minutos

    if ($diferencia < 3600) {

        $minutos = floor($diferencia / 60);

        return "Hace " . $minutos . " minuto" .
            ($minutos > 1 ? "s" : "");
    }


    // Horas

    if ($diferencia < 86400) {

        $horas = floor($diferencia / 3600);

        return "Hace " . $horas . " hora" .
            ($horas > 1 ? "s" : "");
    }


    // Ayer

    if ($diferencia < 172800) {

        return "Ayer - " .
            date("H:i", $fechaNotificacion);
    }


    // Días

    if ($diferencia < 604800) {

        $dias = floor($diferencia / 86400);

        return "Hace " . $dias . " día" .
            ($dias > 1 ? "s" : "");
    }


    // Más de 7 días

    return date(
        "d/m/Y - H:i",
        $fechaNotificacion
    );
}
/*======================================================
=            CONSULTAR NOTIFICACIONES
======================================================*/

$sql = "

SELECT

    id_notificacion,
    titulo,
    mensaje,
    icono,
    color,
    url,
    leido,
    fecha,
    tipo

FROM notificaciones_cliente

WHERE idCliente = ?
AND Eliminado = 0

ORDER BY fecha DESC

LIMIT 6

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
=            EJECUTAR CONSULTA
======================================================*/

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idCliente
);

if (!mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se pudo ejecutar la consulta."
    ]);

    exit;
}


$resultado = mysqli_stmt_get_result($stmt);


/*======================================================
=            GUARDAR NOTIFICACIONES
======================================================*/

$notificaciones = [];

while ($fila = mysqli_fetch_assoc($resultado)) {

    $notificaciones[] = [

        "id" => (int) $fila["id_notificacion"],

        "titulo" => $fila["titulo"],

        "mensaje" => $fila["mensaje"],

        "icono" => !empty($fila["icono"])
            ? $fila["icono"]
            : "bi-bell-fill",

        "color" => !empty($fila["color"])
            ? $fila["color"]
            : "primary",
        "tipo" => $fila["tipo"],
        "url" => !empty($fila["url"])
            ? $fila["url"]
            : "#",

        "leido" => (int) $fila["leido"],

        "fecha" => fechaInteligente(
            $fila["fecha"]
        ),

    ];
}


/*======================================================
=            CALCULAR CONTADOR DE NO LEÍDAS
======================================================*/

$contador = 0;

foreach ($notificaciones as $notificacion) {

    if ($notificacion["leido"] == 0) {

        $contador++;
    }
}


/*======================================================
=            CERRAR STATEMENT
======================================================*/

mysqli_stmt_close($stmt);


/*======================================================
=            RESPUESTA FINAL
======================================================*/

echo json_encode([

    "estado" => "ok",

    "contador" => $contador,

    "notificaciones" => $notificaciones

], JSON_UNESCAPED_UNICODE);


/*======================================================
=            CERRAR CONEXIÓN
======================================================*/

mysqli_close($conexion);

exit;
