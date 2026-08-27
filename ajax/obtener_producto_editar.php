<?php
//Toda esta seccion pertenece a ajax/obtener_producto_editar.php
session_start();

header("Content-Type: application/json");

require_once "../controladores/conexion.php";

if (!isset($_POST["idProducto"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "ID no recibido"
    ]);

    exit;
}
$idUser = $_SESSION["idUser"];
$idProducto = intval($_POST["idProducto"]);

$sql = "
SELECT *
FROM producto
WHERE idProducto='$idProducto'
AND id_user='$idUser'
LIMIT 1
";

$rs = mysqli_query($conexion, $sql);

if (!$rs) {

    echo json_encode([
        "estado" => false,
        "mensaje" => mysqli_error($conexion)
    ]);

    exit;
}

if (mysqli_num_rows($rs) == 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Producto no encontrado"
    ]);

    exit;
}

$fila = mysqli_fetch_assoc($rs);

echo json_encode([
    "estado" => true,
    "producto" => $fila
]);
