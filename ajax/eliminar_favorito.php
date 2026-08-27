<?php
//=========================================================
// CoDevPro Technology
// ajax/eliminar_favorito.php
//=========================================================

session_start();

header("Content-Type: application/json");

require_once "../controladores/conexion.php";

/*=============================================
CLIENTE
=============================================*/

$idCliente = $_SESSION["idCliente"] ?? 0;
$idProducto = intval($_POST["idProducto"] ?? 0);

if ($idCliente <= 0) {

    echo json_encode([
        "estado"  => false,
        "mensaje" => "Debes iniciar sesión."
    ]);

    exit;
}

/*=============================================
VERIFICAR SI EXISTE
=============================================*/

$sql = "SELECT id_favorito
        FROM favoritos
        WHERE idCliente = ?
        AND idProducto = ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param($stmt, "ii", $idCliente, $idProducto);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (!mysqli_fetch_assoc($resultado)) {

    echo json_encode([
        "estado"  => false,
        "mensaje" => "El producto no está en favoritos."
    ]);

    exit;
}

/*=============================================
ELIMINAR FAVORITO
=============================================*/

$sql = "DELETE
        FROM favoritos
        WHERE idCliente = ?
        AND idProducto = ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param($stmt, "ii", $idCliente, $idProducto);

if (mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "estado"  => true,
        "accion"  => "eliminado",
        "mensaje" => "Producto eliminado de favoritos."
    ]);
} else {

    echo json_encode([
        "estado"  => false,
        "mensaje" => "No fue posible eliminar el favorito."
    ]);
}
