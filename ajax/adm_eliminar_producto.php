<?php
//======================================================
// CoDevPro Technology
// ajax/eliminar_producto.php
// Eliminación lógica de productos
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

/*======================================================
VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida"
    ]);

    exit;
}

$idUser = intval($_SESSION["idUser"]);

/*======================================================
VALIDAR ID
======================================================*/

$idProducto = intval($_POST["idProducto"] ?? 0);

if ($idProducto <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Producto inválido"
    ]);

    exit;
}

/*======================================================
VERIFICAR PRODUCTO
======================================================*/

$sqlValidar = "

SELECT idProducto

FROM producto

WHERE idProducto = '$idProducto'

AND id_user = '$idUser'

AND Eliminado = 0

LIMIT 1

";

$resultado = mysqli_query(
    $conexion,
    $sqlValidar
);

if (!$resultado || mysqli_num_rows($resultado) == 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Producto no encontrado"
    ]);

    exit;
}

/*======================================================
ELIMINACIÓN LÓGICA
======================================================*/

$sqlEliminar = "

UPDATE producto

SET

    Eliminado = 1,
    fecha_actualizado = CURDATE()

WHERE idProducto = '$idProducto'

AND id_user = '$idUser'

LIMIT 1

";

if (mysqli_query($conexion, $sqlEliminar)) {

    echo json_encode([
        "estado" => true,
        "mensaje" => "Producto eliminado correctamente"
    ]);
} else {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error al eliminar: " . mysqli_error($conexion)
    ]);
}

mysqli_close($conexion);
