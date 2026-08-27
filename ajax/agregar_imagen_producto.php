<?php
//Toda esta parte es Ajax/agregar_imagen_producto.php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['idUser'])) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Sesión expirada."
    ]);

    exit;
}

require_once "../controladores/conexion.php";

$idProducto = isset($_POST["idProducto"])
    ? (int)$_POST["idProducto"]
    : 0;

if ($idProducto <= 0) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Producto inválido."
    ]);

    exit;
}

/*=========================================
=   VALIDAR PRODUCTO DEL USUARIO
=========================================*/

$sql = "SELECT idProducto
      FROM producto
      WHERE idProducto=?
      AND id_user=?
      LIMIT 1";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ii",
    $idProducto,
    $_SESSION["idUser"]
);

$stmt->execute();

if ($stmt->get_result()->num_rows == 0) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Producto no encontrado."
    ]);

    exit;
}

/*=========================================
=   VALIDAR ARCHIVO
=========================================*/

if (!isset($_FILES["imagen"])) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se recibió ninguna imagen."
    ]);

    exit;
}

$imagen = $_FILES["imagen"];

/*=========================================
=   VALIDAR TAMAÑO
=========================================*/

// Máximo permitido: 2.7 MB
$maximoPeso = 2.7 * 1024 * 1024;

if ($imagen["size"] > $maximoPeso) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "La imagen supera el límite permitido de 2.7 MB."
    ]);

    exit;
}
/*=========================================
=   CONTAR IMÁGENES
=========================================*/

$sql = "SELECT COUNT(*) total
      FROM imagenes
      WHERE idProducto=?";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("i", $idProducto);

$stmt->execute();

$total = $stmt->get_result()->fetch_assoc()["total"];

if ($total >= 4) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Este producto ya tiene 4 imágenes."
    ]);

    exit;
}

/*=========================================
=   SIGUIENTE ORDEN
=========================================*/

$sql = "SELECT IFNULL(MAX(orden),0)+1 orden
      FROM imagenes
      WHERE idProducto=?";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("i", $idProducto);

$stmt->execute();

$orden = $stmt->get_result()->fetch_assoc()["orden"];

/*=========================================
=   GUARDAR
=========================================*/

$binario = file_get_contents($imagen["tmp_name"]);

$fecha = date("Y-m-d");

$sql = "INSERT INTO imagenes(

        imagenes,
        idProducto,
        fecha_registro,
        fecha_actualizado,
        orden

      )

      VALUES(

        ?,
        ?,
        ?,
        NOW(),
        ?

      )";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "sisi",
    $binario,
    $idProducto,
    $fecha,
    $orden
);

$stmt->send_long_data(0, $binario);

if ($stmt->execute()) {

    echo json_encode([

        "estado" => "ok",

        "mensaje" => "Imagen agregada correctamente."

    ]);
} else {

    echo json_encode([

        "estado" => "error",

        "mensaje" => "Error al guardar la imagen."

    ]);
}
