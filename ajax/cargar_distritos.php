<?php
//======================================================
// ajax/Cargar Distritos
//======================================================

session_start();

require_once "../controladores/conexion.php";

header("Content-Type:text/html; charset=utf-8");

$idProvincia = intval($_POST["id_provincia"] ?? 0);

echo '<option value="">Seleccione</option>';

if ($idProvincia <= 0) {
    exit();
}

$sql = "SELECT

            id_distrito,
            nombre

        FROM distrito

        WHERE Eliminado=0

        AND id_provincia=?

        ORDER BY nombre ASC";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idProvincia
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

while ($fila = mysqli_fetch_assoc($resultado)) {

    echo '<option value="' . $fila["id_distrito"] . '">' .
        htmlspecialchars($fila["nombre"]) .
        '</option>';
}
