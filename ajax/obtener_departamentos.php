<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_departamentos.php
//======================================================

require_once "../controladores/conexion.php";

$idPais = isset($_POST["id_pais"]) ? intval($_POST["id_pais"]) : 0;

$sql = "

SELECT
id_departamento,
nombre

FROM departamento

WHERE id_pais='$idPais'

AND Eliminado=0

ORDER BY nombre

";

$resultado = mysqli_query($conexion, $sql);

$html = '<option value="">Seleccione...</option>';

while ($fila = mysqli_fetch_assoc($resultado)) {

    $html .= '

    <option value="' . $fila["id_departamento"] . '">

        ' . htmlspecialchars($fila["nombre"]) . '

    </option>

    ';
}

echo $html;
