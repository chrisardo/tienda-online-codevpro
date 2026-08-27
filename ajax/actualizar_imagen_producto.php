<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUser'])) {
    echo json_encode(["estado" => "error", "mensaje" => "Sesión expirada"]);
    exit;
}

require_once "../controladores/conexion.php";

$idProducto = (int)$_POST["idProducto"];
$idImagen   = (int)$_POST["id_imagen"];

if (!$idProducto || !$idImagen) {
    echo json_encode(["estado" => "error", "mensaje" => "Datos inválidos"]);
    exit;
}

/* validar pertenencia */
$sql = "SELECT p.idProducto
        FROM producto p
        INNER JOIN imagenes i ON p.idProducto = i.idProducto
        WHERE p.idProducto=? AND i.id_imagen=? AND p.id_user=?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("iii", $idProducto, $idImagen, $_SESSION['idUser']);
$stmt->execute();

if ($stmt->get_result()->num_rows == 0) {
    echo json_encode(["estado" => "error", "mensaje" => "No autorizado"]);
    exit;
}

/* imagen */
if (!isset($_FILES["imagen"])) {
    echo json_encode(["estado" => "error", "mensaje" => "Sin imagen"]);
    exit;
}

$binario = file_get_contents($_FILES["imagen"]["tmp_name"]);

$sql = "UPDATE imagenes SET imagenes=?, fecha_actualizado=NOW() WHERE id_imagen=?";

$stmt = $conexion->prepare($sql);
$stmt->send_long_data(0, $binario);
$stmt->bind_param("si", $binario, $idImagen);

if ($stmt->execute()) {
    echo json_encode(["estado" => "ok", "mensaje" => "Actualizado"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error"]);
}
