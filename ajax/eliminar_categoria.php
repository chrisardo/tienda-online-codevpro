<?php
//======================================================
// CoDevPro Technology
// ajax/eliminar_categoria.php
//======================================================

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

if ($idCategoria <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Categoría inválida"
    ]);

    exit;
}

/*=============================================
= VALIDAR QUE EXISTA
=============================================*/

$sqlCategoria = "

SELECT id_categorias

FROM categorias

WHERE id_categorias = ?
AND id_user = ?
AND Eliminado = 0

";

$stmtCategoria = mysqli_prepare(
    $conexion,
    $sqlCategoria
);

mysqli_stmt_bind_param(
    $stmtCategoria,
    "ii",
    $idCategoria,
    $idUser
);

mysqli_stmt_execute($stmtCategoria);

if (
    mysqli_stmt_get_result($stmtCategoria)->num_rows == 0
) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La categoría no existe"
    ]);

    exit;
}

/*=============================================
= VALIDAR PRODUCTOS ASOCIADOS
=============================================*/

$sqlProductos = "

SELECT COUNT(*) total

FROM producto

WHERE id_categorias = ?
AND Eliminado = 0

";

$stmtProductos = mysqli_prepare(
    $conexion,
    $sqlProductos
);

mysqli_stmt_bind_param(
    $stmtProductos,
    "i",
    $idCategoria
);

mysqli_stmt_execute($stmtProductos);

$totalProductos =
    mysqli_stmt_get_result($stmtProductos)
        ->fetch_assoc()["total"];

if ($totalProductos > 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se puede eliminar. Existen {$totalProductos} productos asociados a esta categoría."
    ]);

    exit;
}

/*=============================================
= ELIMINACIÓN LÓGICA
=============================================*/

$sqlEliminar = "

UPDATE categorias

SET Eliminado = 1

WHERE id_categorias = ?
AND id_user = ?

";

$stmtEliminar = mysqli_prepare(
    $conexion,
    $sqlEliminar
);

mysqli_stmt_bind_param(
    $stmtEliminar,
    "ii",
    $idCategoria,
    $idUser
);

$ok = mysqli_stmt_execute($stmtEliminar);

echo json_encode([

    "estado" => $ok,

    "mensaje" => $ok
        ? "Categoría eliminada correctamente"
        : "No se pudo eliminar"

]);
