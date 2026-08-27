<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_monedas.php
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
=            OBTENER LAS MONEDAS
======================================================*/

$sql = "

SELECT

    id_moneda,
    nombre

FROM monedas

WHERE Eliminado = 0

ORDER BY nombre ASC

";

$resultado = mysqli_query($conexion, $sql);

$monedas = [];


/*======================================================
=            RECORRER LOS RESULTADOS
======================================================*/

while ($fila = mysqli_fetch_assoc($resultado)) {

    $monedas[] = [

        "id"     => (int)$fila["id_moneda"],
        "nombre" => $fila["nombre"]

    ];
}


/*======================================================
=            RESPUESTA JSON
======================================================*/

echo json_encode([

    "estado"  => "ok",
    "total"   => count($monedas),
    "monedas" => $monedas

], JSON_UNESCAPED_UNICODE);


/*======================================================
=            CERRAR CONEXIÓN
======================================================*/

mysqli_close($conexion);

exit;
