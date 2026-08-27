<?php
//Todo esto pertenece a ajax/obtener_contador_carrito.php
session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";
require_once "../controladores/token_carrito.php";

$idCliente = isset($_SESSION["idCliente"]) ? intval($_SESSION["idCliente"]) : 0;
$token = obtenerTokenCarrito();

if ($idCliente > 0) {

    $sql = "SELECT IFNULL(SUM(cantidad),0) AS total
            FROM carrito_online
            WHERE idCliente = ?
            AND estado = 'pendiente'";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idCliente);

} else {

    $sql = "SELECT IFNULL(SUM(cantidad),0) AS total
            FROM carrito_online
            WHERE token = ?
            AND estado = 'pendiente'";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);

}

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$fila = mysqli_fetch_assoc($resultado);

echo json_encode([
    "estado" => true,
    "contador" => intval($fila["total"])
]);