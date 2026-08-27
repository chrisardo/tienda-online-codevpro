<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_preferencias.php
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
=            OBTENER ID DEL CLIENTE
======================================================*/

$idCliente = (int) $_SESSION["idCliente"];


/*======================================================
=            VERIFICAR SI EXISTEN PREFERENCIAS
======================================================*/

$sqlVerificar = "

SELECT *

FROM preferencias_cliente

WHERE idCliente = ?

LIMIT 1

";

$stmt = mysqli_prepare($conexion, $sqlVerificar);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idCliente
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$preferencias = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);


/*======================================================
=            CREAR PREFERENCIAS POR DEFECTO
======================================================*/

if (!$preferencias) {

    $sqlInsertar = "

    INSERT INTO preferencias_cliente(

        idCliente,
        correo_promociones,
        estado_pedido,
        nuevos_productos,
        ofertas_flash,
        id_idiomas,
        id_moneda,
        id_metodo_pago,
        fecha_actualizado

    )

    VALUES(

        ?,
        1,
        1,
        1,
        1,
        NULL,
        NULL,
        NULL,
        NOW()

    )

    ";

    $stmtInsertar = mysqli_prepare(
        $conexion,
        $sqlInsertar
    );

    mysqli_stmt_bind_param(
        $stmtInsertar,
        "i",
        $idCliente
    );

    mysqli_stmt_execute($stmtInsertar);

    mysqli_stmt_close($stmtInsertar);


    /*==========================================
    VOLVER A CONSULTAR
    ==========================================*/

    $stmt = mysqli_prepare(
        $conexion,
        $sqlVerificar
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idCliente
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    $preferencias = mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmt);
}


/*======================================================
=            OBTENER IDIOMAS
======================================================*/

$sqlIdiomas = "

SELECT

id_idiomas,
nombre

FROM idiomas

WHERE Eliminado = 0

ORDER BY nombre ASC

";

$resultadoIdiomas = mysqli_query(
    $conexion,
    $sqlIdiomas
);

$idiomas = [];

while ($fila = mysqli_fetch_assoc($resultadoIdiomas)) {

    $idiomas[] = [

        "id" => (int)$fila["id_idiomas"],
        "nombre" => $fila["nombre"]

    ];
}


/*======================================================
=            OBTENER MONEDAS
======================================================*/

$sqlMonedas = "

SELECT

id_moneda,
nombre

FROM monedas

WHERE Eliminado = 0

ORDER BY nombre ASC

";

$resultadoMonedas = mysqli_query(
    $conexion,
    $sqlMonedas
);

$monedas = [];

while ($fila = mysqli_fetch_assoc($resultadoMonedas)) {

    $monedas[] = [

        "id" => (int)$fila["id_moneda"],
        "nombre" => $fila["nombre"]

    ];
}


/*======================================================
=            RESPUESTA JSON
======================================================*/

echo json_encode([

    "estado" => "ok",

    "preferencias" => [

        "correo_promociones" => (int)$preferencias["correo_promociones"],

        "estado_pedido" => (int)$preferencias["estado_pedido"],

        "nuevos_productos" => (int)$preferencias["nuevos_productos"],

        "ofertas_flash" => (int)$preferencias["ofertas_flash"],

        "id_idiomas" => $preferencias["id_idiomas"],

        "id_moneda" => $preferencias["id_moneda"],

        "id_metodo_pago" => $preferencias["id_metodo_pago"]

    ],

    "idiomas" => $idiomas,

    "monedas" => $monedas

], JSON_UNESCAPED_UNICODE);


/*======================================================
=            CERRAR CONEXIÓN
======================================================*/

mysqli_close($conexion);

exit;
