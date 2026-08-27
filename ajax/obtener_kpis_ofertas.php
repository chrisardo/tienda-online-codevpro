<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_kpis_ofertas.php
// Módulo: Ofertas y Descuentos
// Sistema: Inventa
//=====================================================

header(
    "Content-Type: application/json; charset=UTF-8"
);

//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// RESPUESTA BASE
//=====================================================

$respuesta = [
    "success" => false,
    "mensaje" => "",
    "datos"   => null
];

//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON(
    $respuesta,
    $codigoHTTP = 200
) {

    http_response_code($codigoHTTP);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// OBTENER ID DEL USUARIO
//=====================================================

$idUser = 0;

//-----------------------------------------------------
// $_SESSION["id"]
//-----------------------------------------------------

if (
    isset($_SESSION["id"]) &&
    intval($_SESSION["id"]) > 0
) {

    $idUser = intval($_SESSION["id"]);
}

//-----------------------------------------------------
// $_SESSION["id_user"]
//-----------------------------------------------------

elseif (
    isset($_SESSION["id_user"]) &&
    intval($_SESSION["id_user"]) > 0
) {

    $idUser = intval($_SESSION["id_user"]);
}

//-----------------------------------------------------
// $_SESSION["idUser"]
//-----------------------------------------------------

elseif (
    isset($_SESSION["idUser"]) &&
    intval($_SESSION["idUser"]) > 0
) {

    $idUser = intval($_SESSION["idUser"]);
}

//=====================================================
// VALIDAR USUARIO
//=====================================================

if ($idUser <= 0) {

    $respuesta["mensaje"] =
        "La sesión del usuario no es válida o ha expirado.";

    responderJSON(
        $respuesta,
        401
    );
}

//=====================================================
// VALIDAR MÉTODO
//=====================================================

if (
    $_SERVER["REQUEST_METHOD"] !== "GET"
) {

    $respuesta["mensaje"] =
        "Método de solicitud no permitido.";

    responderJSON(
        $respuesta,
        405
    );
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !$conexion
) {

    $respuesta["mensaje"] =
        "No se pudo establecer conexión con la base de datos.";

    responderJSON(
        $respuesta,
        500
    );
}

//=====================================================
// CONFIGURAR CHARSET
//=====================================================

if (
    !mysqli_set_charset(
        $conexion,
        "utf8mb4"
    )
) {

    $respuesta["mensaje"] =
        "No se pudo configurar el charset de la conexión.";

    responderJSON(
        $respuesta,
        500
    );
}

//=====================================================
// CONSULTA KPI
//=====================================================
//
// LÓGICA OFICIAL
//
// 1. TOTAL PRODUCTOS
//
//    Producto perteneciente al usuario
//    Y
//    Eliminado = 0
//
// 2. PRODUCTOS EN OFERTA
//
//    oferta = 1
//    Y
//    descuento > 0
//
// 3. PRODUCTOS CON DESCUENTO
//
//    descuento > 0
//
// 4. PRODUCTOS SIN DESCUENTO
//
//    descuento IS NULL
//    O
//    descuento <= 0
//
// 5. DESCUENTO PROMEDIO
//
//    Solo descuentos mayores a 0
//
//=====================================================

$sql = "

    SELECT

        /*=================================================
          TOTAL PRODUCTOS
        =================================================*/

        COUNT(*) AS total_productos,


        /*=================================================
          PRODUCTOS EN OFERTA
        ==================================================
          
          IMPORTANTE:
          
          Una oferta válida necesita:
          
          oferta = 1
          Y
          descuento > 0

        =================================================*/

        SUM(
            CASE
                WHEN
                    COALESCE(oferta, 0) = 1
                    AND
                    COALESCE(descuento, 0) > 0
                THEN 1
                ELSE 0
            END
        ) AS productos_oferta,


        /*=================================================
          PRODUCTOS CON DESCUENTO
        =================================================*/

        SUM(
            CASE
                WHEN
                    COALESCE(descuento, 0) > 0
                THEN 1
                ELSE 0
            END
        ) AS productos_descuento,


        /*=================================================
          PRODUCTOS SIN DESCUENTO
        =================================================*/

        SUM(
            CASE
                WHEN
                    COALESCE(descuento, 0) <= 0
                THEN 1
                ELSE 0
            END
        ) AS productos_sin_descuento,


        /*=================================================
          DESCUENTO PROMEDIO
        =================================================*/

        COALESCE(
            AVG(
                CASE
                    WHEN
                        COALESCE(descuento, 0) > 0
                    THEN descuento
                    ELSE NULL
                END
            ),
            0
        ) AS descuento_promedio


    FROM producto


    WHERE
        id_user = ?
        AND
        Eliminado = 0

";

//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

if (!$stmt) {

    error_log(
        "obtener_kpis_ofertas.php - Error prepare: " .
            mysqli_error($conexion)
    );

    $respuesta["mensaje"] =
        "No se pudo preparar la consulta de KPIs.";

    responderJSON(
        $respuesta,
        500
    );
}

//=====================================================
// BIND PARAMETER
//=====================================================

if (
    !mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    )
) {

    error_log(
        "obtener_kpis_ofertas.php - Error bind_param: " .
            mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close($stmt);

    $respuesta["mensaje"] =
        "No se pudo asociar el usuario a la consulta.";

    responderJSON(
        $respuesta,
        500
    );
}

//=====================================================
// EJECUTAR
//=====================================================

if (
    !mysqli_stmt_execute($stmt)
) {

    error_log(
        "obtener_kpis_ofertas.php - Error execute: " .
            mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close($stmt);

    $respuesta["mensaje"] =
        "No se pudo ejecutar la consulta de KPIs.";

    responderJSON(
        $respuesta,
        500
    );
}

//=====================================================
// RESULTADO
//=====================================================

mysqli_stmt_bind_result(
    $stmt,

    $totalProductosDB,
    $productosOfertaDB,
    $productosDescuentoDB,
    $productosSinDescuentoDB,
    $descuentoPromedioDB
);

//=====================================================
// LEER RESULTADO
//=====================================================

if (
    !mysqli_stmt_fetch($stmt)
) {

    mysqli_stmt_close($stmt);

    $respuesta["mensaje"] =
        "No se pudieron obtener los datos de los KPIs.";

    responderJSON(
        $respuesta,
        500
    );
}

//=====================================================
// CERRAR STATEMENT
//=====================================================

mysqli_stmt_close($stmt);

//=====================================================
// NORMALIZAR VALORES
//=====================================================

$totalProductos =
    intval(
        $totalProductosDB
    );

$productosOferta =
    intval(
        $productosOfertaDB
    );

$productosDescuento =
    intval(
        $productosDescuentoDB
    );

$productosSinDescuento =
    intval(
        $productosSinDescuentoDB
    );

$descuentoPromedio =
    floatval(
        $descuentoPromedioDB
    );

//=====================================================
// SEGURIDAD
//=====================================================

if ($totalProductos < 0) {
    $totalProductos = 0;
}

if ($productosOferta < 0) {
    $productosOferta = 0;
}

if ($productosDescuento < 0) {
    $productosDescuento = 0;
}

if ($productosSinDescuento < 0) {
    $productosSinDescuento = 0;
}

if ($descuentoPromedio < 0) {
    $descuentoPromedio = 0;
}

//=====================================================
// VALIDAR COHERENCIA
//=====================================================
//
// Los productos con descuento + los productos sin
// descuento deben coincidir con el total.
//
//=====================================================

$sumaDescuentos =
    $productosDescuento +
    $productosSinDescuento;

if (
    $sumaDescuentos !== $totalProductos
) {

    error_log(
        "obtener_kpis_ofertas.php - " .
            "Inconsistencia KPI: " .
            "Total=" . $totalProductos .
            " | Con descuento=" . $productosDescuento .
            " | Sin descuento=" . $productosSinDescuento
    );

    //=================================================
    // CORRECCIÓN DE SEGURIDAD
    //=================================================
    //
    // Si por algún motivo existiera una inconsistencia,
    // el valor de "sin descuento" se ajusta al total.
    //
    //=================================================

    $productosSinDescuento =
        $totalProductos -
        $productosDescuento;

    if ($productosSinDescuento < 0) {
        $productosSinDescuento = 0;
    }
}

//=====================================================
// NO PERMITIR VALORES MAYORES AL TOTAL
//=====================================================

if (
    $productosOferta >
    $totalProductos
) {

    $productosOferta =
        $totalProductos;
}

if (
    $productosDescuento >
    $totalProductos
) {

    $productosDescuento =
        $totalProductos;
}

if (
    $productosSinDescuento >
    $totalProductos
) {

    $productosSinDescuento =
        $totalProductos;
}

//=====================================================
// REDONDEAR PROMEDIO
//=====================================================

$descuentoPromedio =
    round(
        $descuentoPromedio,
        2
    );

//=====================================================
// DATOS PRINCIPALES
//=====================================================

$datos = [

    //=================================================
    // PRINCIPALES
    //=================================================

    "total_productos" =>
    $totalProductos,

    "productos_oferta" =>
    $productosOferta,

    "productos_descuento" =>
    $productosDescuento,

    "productos_sin_descuento" =>
    $productosSinDescuento,

    "descuento_promedio" =>
    $descuentoPromedio,


    //=================================================
    // ALIAS
    //=================================================

    "ofertas_activas" =>
    $productosOferta,

    "sin_oferta" =>
    $productosSinDescuento,


    //=================================================
    // CAMEL CASE
    //=================================================

    "totalProductos" =>
    $totalProductos,

    "productosOferta" =>
    $productosOferta,

    "productosDescuento" =>
    $productosDescuento,

    "productosSinDescuento" =>
    $productosSinDescuento,

    "descuentoPromedio" =>
    $descuentoPromedio
];

//=====================================================
// RESPUESTA EXITOSA
//=====================================================

$respuesta["success"] =
    true;

$respuesta["mensaje"] =
    "KPIs obtenidos correctamente.";

$respuesta["datos"] =
    $datos;

//=====================================================
// COMPATIBILIDAD CON JS
//=====================================================

$respuesta["total_productos"] =
    $totalProductos;

$respuesta["productos_oferta"] =
    $productosOferta;

$respuesta["productos_descuento"] =
    $productosDescuento;

$respuesta["productos_sin_descuento"] =
    $productosSinDescuento;

$respuesta["descuento_promedio"] =
    $descuentoPromedio;

$respuesta["ofertas_activas"] =
    $productosOferta;

$respuesta["sin_oferta"] =
    $productosSinDescuento;

//=====================================================
// DEBUG SERVIDOR
//=====================================================

error_log(
    "KPI OFERTAS | " .
        "Usuario: " . $idUser .
        " | Total: " . $totalProductos .
        " | Ofertas activas: " . $productosOferta .
        " | Con descuento: " . $productosDescuento .
        " | Sin descuento: " . $productosSinDescuento .
        " | Promedio: " . $descuentoPromedio
);

//=====================================================
// ENVIAR RESPUESTA
//=====================================================

responderJSON(
    $respuesta,
    200
);
