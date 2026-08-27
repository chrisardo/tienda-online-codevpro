<?php
//======================================================
// CoDevPro Technology
// controladores/obtener_testimonio2.php
// Obtener un testimonio
//======================================================

/*======================================================
OBTENER TESTIMONIO
======================================================*/

$sql = "

SELECT

    id_testimonio,
    calificacion,
    comentario,
    respuesta,
    fecha_respuesta,
    fecha,
    estado

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
        "mensaje" => "Error al preparar la consulta: " . mysqli_error($conexion)
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

if ($fila = mysqli_fetch_assoc($resultado)) {

    echo json_encode([

        "estado" => "ok",

        "id_testimonio" => (int)$fila["id_testimonio"],

        "calificacion" => (int)$fila["calificacion"],

        "comentario" => $fila["comentario"],

        "respuesta" => $fila["respuesta"],

        "fecha_respuesta" => $fila["fecha_respuesta"],

        "fecha" => $fila["fecha"],

        "estado_testimonio" => $fila["estado"]

    ]);
} else {

    echo json_encode([

        "estado" => "error",

        "mensaje" => "No existe un testimonio para este producto."

    ]);
}

mysqli_stmt_close($stmt);
