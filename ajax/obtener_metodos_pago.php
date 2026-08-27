<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_metodos_pago.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

/*======================================================
VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}

$idUser = intval($_SESSION["idUser"]);

/*======================================================
OBTENER MÉTODOS DE PAGO
======================================================*/

$sql = "

SELECT

id_metodo_pago,
nombre

FROM metodo_pago

WHERE Eliminado = 0
AND id_user = ?

ORDER BY nombre ASC

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param($stmt, "i", $idUser);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$metodos = [];

while ($fila = mysqli_fetch_assoc($resultado)) {

    $metodos[] = [

        "id" => (int)$fila["id_metodo_pago"],
        "nombre" => $fila["nombre"]

    ];
}

echo json_encode([

    "estado" => true,

    "metodos" => $metodos

], JSON_UNESCAPED_UNICODE);

mysqli_stmt_close($stmt);
mysqli_close($conexion);

exit;
