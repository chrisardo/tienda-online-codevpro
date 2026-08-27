<?php
//Toda esta parte pertenece a ajax/obtener_distritos.php
require_once "../controladores/conexion.php";

$idProvincia = isset($_POST["id_provincia"])
    ? intval($_POST["id_provincia"])
    : 0;

$sql = "

SELECT

id_distrito,
nombre

FROM distrito

WHERE id_provincia='$idProvincia'

AND Eliminado=0

ORDER BY nombre

";

$resultado = mysqli_query($conexion, $sql);

$html = '<option value="">Seleccione...</option>';

while ($fila = mysqli_fetch_assoc($resultado)) {

    $html .= '

    <option value="' . $fila["id_distrito"] . '">

        ' . htmlspecialchars($fila["nombre"]) . '

    </option>

    ';
}

echo $html;
