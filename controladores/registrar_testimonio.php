<?php
//======================================================
// CoDevPro Technology
// controladores/registrar_testimonio.php
// Registrar nuevo testimonio
//======================================================

/*======================================================
REGISTRAR TESTIMONIO
======================================================*/

$sql = "

INSERT INTO testimonios
(

id_ticket_ventas,
idCliente,
idProducto,
id_user,
calificacion,
comentario,
fecha,
estado

)

VALUES
(

?,
?,
?,
?,
?,
?,
CURDATE(),
'APROBADO'

)

";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo json_encode([
        "estado"  => "error",
        "mensaje" => "Error al preparar la consulta: " . mysqli_error($conexion)
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "iiiiis",
    $idTicket,
    $idCliente,
    $idProducto,
    $idUser,
    $calificacion,
    $comentario
);

if (mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "estado"  => "ok",
        "mensaje" => "Gracias por compartir tu opinión."
    ]);
} else {

    echo json_encode([
        "estado"  => "error",
        "mensaje" => "Error al registrar el testimonio: " . mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
