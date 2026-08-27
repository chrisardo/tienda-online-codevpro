<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_categorias.php
// Obtener categorías para modal editar producto
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
CONSULTAR CATEGORÍAS
=============================================*/

$sql = "

SELECT

    id_categorias,
    nombre

FROM categorias

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

$categorias = [];


while ($fila = mysqli_fetch_assoc($resultado)) {

    $categorias[] = [

        "id_categorias" => $fila["id_categorias"],

        "nombre" => $fila["nombre"]

    ];
}



/*=============================================
RESPUESTA JSON
=============================================*/

echo json_encode($categorias);



mysqli_close($conexion);
