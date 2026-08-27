<?php
//Todo esto pertenece a ajax/agregar_carrito.php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";
require_once "../controladores/token_carrito.php";

//======================================
// VALIDAR DATOS
//======================================

$idProducto = isset($_POST["idProducto"]) ? intval($_POST["idProducto"]) : 0;
$cantidad   = isset($_POST["cantidad"]) ? intval($_POST["cantidad"]) : 1;

if ($idProducto <= 0 || $cantidad <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Datos inválidos."
    ]);

    exit;
}

//======================================
// CLIENTE O INVITADO
//======================================

$idCliente = isset($_SESSION["idCliente"])
    ? intval($_SESSION["idCliente"])
    : 0;

$token = obtenerTokenCarrito();

//======================================
// OBTENER PRODUCTO
//======================================

$sql = "SELECT
            idProducto,
            nombre,
            precio,
            stock
        FROM producto
        WHERE idProducto=?
        AND Eliminado=0
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idProducto);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (!$producto = mysqli_fetch_assoc($resultado)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Producto no encontrado."
    ]);

    exit;
}

//======================================
// VALIDAR STOCK
//======================================

if ($cantidad > $producto["stock"]) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Stock insuficiente."
    ]);

    exit;
}

//======================================
// BUSCAR SI YA EXISTE EN EL CARRITO
//======================================

if ($idCliente > 0) {

    $sql = "SELECT
                idCarrito,
                cantidad
            FROM carrito_online
            WHERE idCliente=?
            AND idProducto=?
            AND estado='pendiente'
            LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $idCliente, $idProducto);
} else {

    $sql = "SELECT
                idCarrito,
                cantidad
            FROM carrito_online
            WHERE token=?
            AND idProducto=?
            AND estado='pendiente'
            LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "si", $token, $idProducto);
}

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

//======================================
// YA EXISTE
//======================================

if ($carrito = mysqli_fetch_assoc($resultado)) {

    $nuevaCantidad = $carrito["cantidad"] + $cantidad;

    if ($nuevaCantidad > $producto["stock"]) {

        echo json_encode([
            "estado" => false,
            "mensaje" => "No hay suficiente stock."
        ]);

        exit;
    }

    $sql = "UPDATE carrito_online
            SET cantidad=?
            WHERE idCarrito=?";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $nuevaCantidad,
        $carrito["idCarrito"]
    );

    mysqli_stmt_execute($stmt);
} else {

    //======================================
    // INSERTAR
    //======================================

    $sql = "INSERT INTO carrito_online(

                idCliente,
                token,
                idProducto,
                cantidad,
                precio,
                fecha,
                estado

            )

            VALUES(

                ?,
                ?,
                ?,
                ?,
                ?,
                NOW(),
                'pendiente'

            )";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(

        $stmt,

        "isiid",

        $idCliente,
        $token,
        $idProducto,
        $cantidad,
        $producto["precio"]

    );

    mysqli_stmt_execute($stmt);
}

//======================================
// CONTADOR DEL CARRITO
//======================================

if ($idCliente > 0) {

    $sql = "SELECT SUM(cantidad) total
            FROM carrito_online
            WHERE idCliente=?
            AND estado='pendiente'";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idCliente);
} else {

    $sql = "SELECT SUM(cantidad) total
            FROM carrito_online
            WHERE token=?
            AND estado='pendiente'";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
}

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$fila = mysqli_fetch_assoc($resultado);

$total = intval($fila["total"]);

echo json_encode([

    "estado" => true,

    "mensaje" => "Producto agregado correctamente.",

    "contador" => $total

]);
