<?php
//======================================================
// CoDevPro Technology
// controladores/editar_testimonio.php
// Actualizar testimonio existente
//======================================================

/*======================================================
ACTUALIZAR TESTIMONIO
======================================================*/

$sql = "

UPDATE testimonios

SET

calificacion = ?,
comentario = ?,
estado = 'APROBADO'

WHERE

id_testimonio = ?

LIMIT 1

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
    "isi",
    $calificacion,
    $comentario,
    $idTestimonio
);

if (mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "estado"  => "ok",
        "mensaje" => "Tu opinión fue actualizada correctamente."
    ]);
} else {

    echo json_encode([
        "estado"  => "error",
        "mensaje" => "Error al actualizar el testimonio: " . mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
