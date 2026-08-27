<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_idiomas.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


/*======================================================
=            VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([

        "estado" => "error",
        "mensaje" => "Debe iniciar sesión."

    ]);

    exit;
}


/*======================================================
=            OBTENER LOS IDIOMAS
======================================================*/

$sql = "

SELECT

    id_idiomas,
    nombre

FROM idiomas

WHERE Eliminado = 0

ORDER BY nombre ASC

";

$resultado = mysqli_query($conexion, $sql);

$idiomas = [];


/*======================================================
=            RECORRER LOS RESULTADOS
======================================================*/

while ($fila = mysqli_fetch_assoc($resultado)) {

    $idiomas[] = [

        "id"     => (int)$fila["id_idiomas"],
        "nombre" => $fila["nombre"]

    ];
}


/*======================================================
=            RESPUESTA JSON
======================================================*/

echo json_encode([

    "estado"  => "ok",
    "total"   => count($idiomas),
    "idiomas" => $idiomas

], JSON_UNESCAPED_UNICODE);


/*======================================================
=            CERRAR CONEXIÓN
======================================================*/

mysqli_close($conexion);

exit;
