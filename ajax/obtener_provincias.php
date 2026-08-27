<?php
//Todo esto pertenece a ajax/obtener_provincias.php
require_once "../controladores/conexion.php";

$idDepartamento = isset($_POST["id_departamento"])
    ? intval($_POST["id_departamento"])
    : 0;

$sql = "

SELECT

id_provincia,
nombre

FROM provincia

WHERE id_departamento='$idDepartamento'

AND Eliminado=0

ORDER BY nombre

";

$resultado = mysqli_query($conexion, $sql);

$html = '<option value="">Seleccione...</option>';

while ($fila = mysqli_fetch_assoc($resultado)) {

    $html .= '

    <option value="' . $fila["id_provincia"] . '">

        ' . htmlspecialchars($fila["nombre"]) . '

    </option>

    ';
}

echo $html;
