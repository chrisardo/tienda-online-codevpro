<?php
//Toda esta parte es de ajax/obtener_sucursales.php
session_start();

header('Content-Type: application/json');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"];

$sql = "
SELECT
    id_sucursal,
    nombre
FROM sucursal
WHERE id_user='$idUser'
AND Eliminado=0
ORDER BY nombre ASC
";

$rs = mysqli_query($conexion, $sql);

$datos = [];

while ($row = mysqli_fetch_assoc($rs)) {

    $datos[] = $row;
}

echo json_encode($datos);
