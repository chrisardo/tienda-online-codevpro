<?php
//Toda esta parte pertenece a ajax/adm_eliminar_productos_masivo.php
session_start();

header(
    "Content-Type: application/json"
);

require_once
    "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false
    ]);

    exit;
}

$data = json_decode(
    file_get_contents(
        "php://input"
    ),
    true
);

$ids = $data["ids"] ?? [];

if (empty($ids)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sin productos."
    ]);

    exit;
}

$idUser = intval(
    $_SESSION["idUser"]
);

foreach ($ids as $idProducto) {

    $idProducto =
        intval($idProducto);

    mysqli_query(
        $conexion,
        "UPDATE producto
         SET Eliminado = 1
         WHERE idProducto = '$idProducto'
         AND id_user = '$idUser'"
    );
}

echo json_encode([

    "estado" => true,

    "mensaje" =>
    "Productos eliminados correctamente."

]);
