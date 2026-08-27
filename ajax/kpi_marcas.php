<?php
//Toda esta parte es de ajax/kpi_marcas.php
session_start();

header("Content-Type: application/json");

require_once "../controladores/conexion.php";

$idUser = intval($_SESSION["idUser"]);

/* TOTAL MARCAS */

$sql = "

SELECT COUNT(*) total

FROM marcas

WHERE id_user = ?
AND Eliminado = 0

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$totalMarcas =
    mysqli_stmt_get_result($stmt)
        ->fetch_assoc()["total"];

/* PRODUCTOS CON MARCA */

$sql = "

SELECT COUNT(*) total

FROM producto

WHERE id_user = ?
AND id_marca IS NOT NULL
AND Eliminado = 0

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$productosMarca =
    mysqli_stmt_get_result($stmt)
        ->fetch_assoc()["total"];

echo json_encode([

    "estado" => true,

    "totalMarcas" => $totalMarcas,

    "productosMarca" => $productosMarca

]);
