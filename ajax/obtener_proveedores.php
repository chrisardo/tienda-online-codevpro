<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_proveedores.php
// Obtener proveedores para modal editar producto
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
CONSULTAR PROVEEDORES
=============================================*/

$sql = "

SELECT

    id_provedor,
    nombre

FROM provedores

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

$proveedores = [];


while ($fila = mysqli_fetch_assoc($resultado)) {

    $proveedores[] = [

        "id_provedor" => $fila["id_provedor"],

        "nombre" => $fila["nombre"]

    ];
}



/*=============================================
RESPUESTA JSON
=============================================*/

echo json_encode($proveedores);



mysqli_close($conexion);
