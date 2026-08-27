<?php
//======================================================
// CoDevPro Technology
// ajax/actualizar_marca.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión inválida"
    ]);

    exit;
}

$idUser = intval($_SESSION["idUser"]);

$idMarca = intval($_POST["idMarca"] ?? 0);

$nombre = trim($_POST["nombre"] ?? "");

if (empty($nombre)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Ingrese el nombre de la marca."
    ]);

    exit;
}

/*=========================================
= VALIDAR EXISTE
=========================================*/

$sqlExiste = "

SELECT id_marca

FROM marcas

WHERE id_marca = ?
AND id_user = ?
AND Eliminado = 0

";

$stmtExiste = mysqli_prepare(
    $conexion,
    $sqlExiste
);

mysqli_stmt_bind_param(
    $stmtExiste,
    "ii",
    $idMarca,
    $idUser
);

mysqli_stmt_execute($stmtExiste);

$resExiste = mysqli_stmt_get_result(
    $stmtExiste
);

if ($resExiste->num_rows == 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Marca no encontrada."
    ]);

    exit;
}

/*=========================================
= ACTUALIZAR
=========================================*/

if (
    isset($_FILES["imagen"]) &&
    $_FILES["imagen"]["error"] == 0
) {

    $imagen = file_get_contents(
        $_FILES["imagen"]["tmp_name"]
    );

    $sql = "

    UPDATE marcas SET

    nombre = ?,
    imagen = ?

    WHERE id_marca = ?
    AND id_user = ?

    ";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssii",
        $nombre,
        $imagen,
        $idMarca,
        $idUser
    );
} else {

    $sql = "

    UPDATE marcas SET

    nombre = ?

    WHERE id_marca = ?
    AND id_user = ?

    ";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $nombre,
        $idMarca,
        $idUser
    );
}

if (!mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo actualizar."
    ]);

    exit;
}

echo json_encode([

    "estado" => true,

    "mensaje" => "Marca actualizada correctamente."

]);
