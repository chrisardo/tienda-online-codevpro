<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_contador_notificaciones.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");


/*======================================================
=            VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([
        "cantidad" => 0
    ]);

    exit();
}


/*======================================================
=            CONEXIÓN
======================================================*/

require_once "../controladores/conexion.php";


/*======================================================
=            VARIABLES
======================================================*/

$idCliente = (int) $_SESSION["idCliente"];


/*======================================================
=            OBTENER TOTAL DE NOTIFICACIONES
======================================================*/

$sql = "

SELECT COUNT(*) AS total

FROM notificaciones_cliente

WHERE idCliente = ?
AND leido = 0
AND Eliminado = 0

";


$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idCliente
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$total = 0;


if ($fila = mysqli_fetch_assoc($resultado)) {

    $total = (int) $fila["total"];
}


/*======================================================
=            RESPUESTA JSON
======================================================*/

echo json_encode([

    "cantidad" => $total

]);


/*======================================================
=            CERRAR CONEXIÓN
======================================================*/

mysqli_stmt_close($stmt);

mysqli_close($conexion);

exit;
