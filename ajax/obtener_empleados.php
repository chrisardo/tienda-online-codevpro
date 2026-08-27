<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_empleados.php
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
OBTENER EMPLEADOS
======================================================*/

$sql = "

SELECT

id_empleado,

CONCAT(nombre,' ',apellido) AS nombre

FROM empleados

WHERE id_user = ?

AND estado = 'ACTIVO'

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



$empleados = [];



while ($fila = mysqli_fetch_assoc($resultado)) {


    $empleados[] = [

        "id" => (int)$fila["id_empleado"],

        "nombre" => $fila["nombre"]

    ];
}



echo json_encode([

    "estado" => true,

    "empleados" => $empleados

], JSON_UNESCAPED_UNICODE);



mysqli_stmt_close($stmt);

mysqli_close($conexion);

exit;
