<?php
//======================================================
// CoDevPro Technology
// ajax/registrar_marca.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión inválida."
    ]);

    exit;
}

$idUser = intval($_SESSION["idUser"]);

$nombre = trim($_POST["nombre"] ?? "");

if (empty($nombre)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Ingrese el nombre de la marca."
    ]);

    exit;
}

/*=========================================
= VALIDAR DUPLICADO
=========================================*/

$sql = "

SELECT id_marca

FROM marcas

WHERE nombre = ?
AND id_user = ?
AND Eliminado = 0

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $nombre,
    $idUser
);

mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

if ($res->num_rows > 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La marca ya existe."
    ]);

    exit;
}

/*=========================================
= IMAGEN
=========================================*/

$imagen = null;

if (
    isset($_FILES["imagen"]) &&
    $_FILES["imagen"]["error"] == 0
) {

    $imagen = file_get_contents(
        $_FILES["imagen"]["tmp_name"]
    );
}

/*=========================================
= REGISTRAR
=========================================*/

$sql = "

INSERT INTO marcas (

    nombre,
    imagen,
    id_user,
    Eliminado

)

VALUES (

    ?,
    ?,
    ?,
    0

)

";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "ssi",
    $nombre,
    $imagen,
    $idUser
);

if (!mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo registrar."
    ]);

    exit;
}

echo json_encode([

    "estado" => true,

    "mensaje" => "Marca registrada correctamente."

]);
