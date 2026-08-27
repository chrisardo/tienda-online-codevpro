<?php
//======================================================
// AJAX
// Cargar Provincias
//======================================================

session_start();

require_once "../controladores/conexion.php";

header("Content-Type:text/html; charset=utf-8");

$idDepartamento = intval($_POST["id_departamento"] ?? 0);

echo '<option value="">Seleccione</option>';

if ($idDepartamento <= 0) {
    exit();
}

$sql = "SELECT

            id_provincia,
            nombre

        FROM provincia

        WHERE Eliminado=0

        AND id_departamento=?

        ORDER BY nombre ASC";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idDepartamento
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

while ($fila = mysqli_fetch_assoc($resultado)) {

    echo '<option value="' . $fila["id_provincia"] . '">' .
        htmlspecialchars($fila["nombre"]) .
        '</option>';
}
