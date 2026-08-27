<?php
session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";
require_once "../controladores/token_carrito.php";

$idCliente = $_SESSION["idCliente"] ?? 0;
$token = obtenerTokenCarrito();

$idCarrito = intval($_POST["idCarrito"] ?? 0);

if ($idCarrito <= 0) {
    echo json_encode([
        "estado" => false,
        "mensaje" => "ID inválido"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| ELIMINAR SEGÚN USUARIO LOGUEADO O TOKEN
|--------------------------------------------------------------------------
*/

if ($idCliente > 0) {

    $sql = "DELETE FROM carrito_online 
            WHERE idCarrito = ? 
            AND idCliente = ?
            AND estado = 'pendiente'";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $idCarrito, $idCliente);
} else {

    $sql = "DELETE FROM carrito_online 
            WHERE idCarrito = ? 
            AND token = ?
            AND estado = 'pendiente'";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "is", $idCarrito, $token);
}

$ok = mysqli_stmt_execute($stmt);

if ($ok) {
    echo json_encode([
        "estado" => true,
        "mensaje" => "Producto eliminado del carrito"
    ]);
} else {
    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo eliminar"
    ]);
}
