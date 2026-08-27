<?php
//======================================================
// CoDevPro Technology
// ajax/adm_oferta_productos.php
//======================================================

session_start();

header(
    "Content-Type: application/json; charset=utf-8"
);

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}

$idUser = intval(
    $_SESSION["idUser"]
);

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$ids = $data["ids"] ?? [];

if (empty($ids)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se seleccionaron productos."
    ]);

    exit;
}

$actualizados = 0;

foreach ($ids as $idProducto) {

    $idProducto = intval(
        $idProducto
    );

    $sql = "
    UPDATE producto
    SET oferta = 1
    WHERE idProducto = '$idProducto'
    AND id_user = '$idUser'
    ";

    if (
        mysqli_query(
            $conexion,
            $sql
        )
    ) {

        $actualizados++;
    }
}

echo json_encode([

    "estado" => true,

    "mensaje" =>
    "Se activó la oferta en {$actualizados} producto(s)."

]);

mysqli_close($conexion);
