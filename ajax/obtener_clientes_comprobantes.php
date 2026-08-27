<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_clientes_comprobantes.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


/*======================================================
VALIDAR USUARIO
======================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida"
    ]);

    exit;
}


$idUser = intval($_SESSION["idUser"]);



/*======================================================
OBTENER CLIENTES
======================================================*/

$sql = "

SELECT

idCliente,
nombre,
dni_o_ruc

FROM clientes

WHERE 
id_user = ?
AND Eliminado = 0

ORDER BY nombre ASC

";


$stmt = mysqli_prepare($conexion, $sql);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);


mysqli_stmt_execute($stmt);


$resultado = mysqli_stmt_get_result($stmt);



$clientes = [];

while ($fila = mysqli_fetch_assoc($resultado)) {


    $clientes[] = [

        "id" => (int)$fila["idCliente"],

        "nombre" => $fila["nombre"],

        "documento" => $fila["dni_o_ruc"]

    ];
}



echo json_encode([

    "estado" => true,

    "clientes" => $clientes

], JSON_UNESCAPED_UNICODE);



mysqli_stmt_close($stmt);

mysqli_close($conexion);

exit;
