<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_marcas.php
// Obtener marcas para modal editar producto
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


/*=============================================
VALIDAR SESIÓN
=============================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}


$idUser = intval($_SESSION["idUser"]);


/*=============================================
CONSULTAR MARCAS
=============================================*/

$sql = "

SELECT

    id_marca,
    nombre

FROM marcas

WHERE id_user = '$idUser'

AND Eliminado = 0

ORDER BY nombre ASC

";


$resultado = mysqli_query(
    $conexion,
    $sql
);



/*=============================================
VALIDAR ERROR SQL
=============================================*/

if (!$resultado) {

    echo json_encode([
        "estado" => false,
        "mensaje" => mysqli_error($conexion)
    ]);

    exit;
}



/*=============================================
ARRAY RESULTADO
=============================================*/

$marcas = [];


while ($fila = mysqli_fetch_assoc($resultado)) {

    $marcas[] = [

        "id_marca" => $fila["id_marca"],

        "nombre" => $fila["nombre"]

    ];
}



/*=============================================
RESPUESTA JSON
=============================================*/

echo json_encode($marcas);



mysqli_close($conexion);
