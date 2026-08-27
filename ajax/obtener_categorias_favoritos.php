<?php
//=========================================================
// CoDevPro Technology
// ajax/obtener_categorias_favoritos.php
//=========================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

/*=========================================================
=            VALIDAR SESION
=========================================================*/

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([]);

    exit;
}

/*=========================================================
=            CONSULTAR CATEGORIAS
=========================================================*/

$sql = "SELECT
            id_categorias,
            nombre
        FROM categorias
        WHERE id_user = ?
        AND Eliminado = 0
        ORDER BY nombre ASC";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo json_encode([]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$categorias = [];

while ($fila = mysqli_fetch_assoc($resultado)) {

    $categorias[] = [

        "id" => (int)$fila["id_categorias"],

        "nombre" => $fila["nombre"]
    ];
}

echo json_encode(
    $categorias,
    JSON_UNESCAPED_UNICODE
);
