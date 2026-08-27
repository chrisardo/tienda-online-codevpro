<?php
//======================================================
// ajax/Cargar Departamentos
// CoDevPro Technology
//======================================================

session_start();

require_once "../controladores/conexion.php";

header("Content-Type:text/html; charset=utf-8");

$idPais = intval($_POST["id_pais"] ?? 0);

echo '<option value="">Seleccione</option>';

if ($idPais <= 0) {
    exit();
}

$sql = "SELECT
            id_departamento,
            nombre
        FROM departamento
        WHERE Eliminado=0
        AND id_pais=?
        ORDER BY nombre ASC";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idPais
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

while ($fila = mysqli_fetch_assoc($resultado)) {

    echo '<option value="' . $fila["id_departamento"] . '">' .
        htmlspecialchars($fila["nombre"]) .
        '</option>';
}
