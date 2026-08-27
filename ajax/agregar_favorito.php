<?php
//Toda esta parte es de ajax/agregar_favorito.php
session_start();
header("Content-Type: application/json");

require_once "../controladores/conexion.php";

$idCliente  = $_SESSION["idCliente"] ?? 0;
$idProducto = intval($_POST["idProducto"] ?? 0);

if ($idCliente <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Inicia sesión primero"
    ]);

    exit;
}

/*==============================
VERIFICAR SI YA EXISTE
==============================*/

$sql = "SELECT id_favorito
      FROM favoritos
      WHERE idCliente=?
      AND idProducto=?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param($stmt, "ii", $idCliente, $idProducto);

mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

if (mysqli_fetch_assoc($res)) {

    $sql = "DELETE FROM favoritos
            WHERE idCliente=? AND idProducto=?";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "ii", $idCliente, $idProducto);

    mysqli_stmt_execute($stmt);

    echo json_encode([
        "estado" => true,
        "accion" => "eliminado",
        "mensaje" => "Producto eliminado de favoritos."
    ]);

    exit;
}

/*==============================
INSERTAR
==============================*/

$sql = "INSERT INTO favoritos(idCliente,idProducto)
      VALUES(?,?)";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param($stmt, "ii", $idCliente, $idProducto);

mysqli_stmt_execute($stmt);

echo json_encode([
    "estado" => true,
    "accion" => "agregado",
    "mensaje" => "Producto agregado a favoritos."
]);
