<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida"
    ]);

    exit;
}

$idUser      = intval($_SESSION["idUser"]);
$idCategoria = intval($_POST["idCategoria"] ?? 0);
$nombre      = trim($_POST["nombre"] ?? "");

if (empty($nombre)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Ingrese el nombre"
    ]);

    exit;
}

/*=============================================
=            VALIDAR EXISTENCIA
=============================================*/

$sqlValidar = "

SELECT id_categorias

FROM categorias

WHERE nombre = ?
AND id_user = ?
AND id_categorias != ?
AND Eliminado = 0

";

$stmtValidar = mysqli_prepare(
    $conexion,
    $sqlValidar
);

mysqli_stmt_bind_param(
    $stmtValidar,
    "sii",
    $nombre,
    $idUser,
    $idCategoria
);

mysqli_stmt_execute($stmtValidar);

if (mysqli_stmt_get_result($stmtValidar)->num_rows > 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Ya existe una categoría con ese nombre"
    ]);

    exit;
}

/*=============================================
=            ACTUALIZAR
=============================================*/

if (
    isset($_FILES["imagen"]) &&
    $_FILES["imagen"]["error"] == 0
) {

    $imagen = file_get_contents(
        $_FILES["imagen"]["tmp_name"]
    );

    $sql = "

    UPDATE categorias SET

    nombre = ?,
    imagen = ?

    WHERE id_categorias = ?
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
        $idCategoria,
        $idUser
    );
} else {

    $sql = "

    UPDATE categorias SET

    nombre = ?

    WHERE id_categorias = ?
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
        $idCategoria,
        $idUser
    );
}

$ok = mysqli_stmt_execute($stmt);

echo json_encode([

    "estado" => $ok,

    "mensaje" => $ok
        ? "Categoría actualizada correctamente"
        : "No se pudo actualizar"

]);
