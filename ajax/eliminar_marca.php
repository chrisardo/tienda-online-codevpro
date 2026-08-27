<?php
//======================================================
// CoDevPro Technology
// ajax/eliminar_marca.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión inválida."
    ]);

    exit;
}

$idUser  = intval($_SESSION["idUser"]);
$idMarca = intval($_POST["idMarca"] ?? 0);

$sql = "

UPDATE marcas

SET Eliminado = 1

WHERE id_marca = ?
AND id_user = ?

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idMarca,
    $idUser
);

if (!mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo eliminar."
    ]);

    exit;
}

echo json_encode([
    "estado" => true,
    "mensaje" => "Marca eliminada correctamente."
]);
