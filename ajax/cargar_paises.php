<?php
//======================================================
// CoDevPro Technology
// ajax/cargar_paises.php
//======================================================

session_start();

require_once "../controladores/conexion.php";

header("Content-Type:text/html; charset=utf-8");

echo '<option value="">Seleccione</option>';

$sql = "SELECT
            id_pais,
            nombre
        FROM pais
        WHERE Eliminado = 0
        ORDER BY nombre ASC";

$resultado = mysqli_query($conexion, $sql);

while ($fila = mysqli_fetch_assoc($resultado)) {

    echo '<option value="' . $fila["id_pais"] . '">'
        . htmlspecialchars($fila["nombre"])
        . '</option>';
}
