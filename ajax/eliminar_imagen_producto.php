<?php
//Toda esta parte es de Ajax/eliminar_imagen_producto.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUser'])) {
    echo json_encode(["estado" => "error", "mensaje" => "Sesión expirada"]);
    exit;
}

require_once "../controladores/conexion.php";

$idImagen = (int)$_POST["id_imagen"];

/* validar propiedad */
$sql = "SELECT i.id_imagen
        FROM imagenes i
        INNER JOIN producto p ON p.idProducto = i.idProducto
        WHERE i.id_imagen=? AND p.id_user=?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $idImagen, $_SESSION['idUser']);
$stmt->execute();

if ($stmt->get_result()->num_rows == 0) {
    echo json_encode(["estado" => "error", "mensaje" => "No autorizado"]);
    exit;
}

/* eliminar */
$sql = "DELETE FROM imagenes WHERE id_imagen=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idImagen);

if ($stmt->execute()) {
    echo json_encode(["estado" => "ok"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar"]);
}
