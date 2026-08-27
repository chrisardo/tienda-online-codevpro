<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_movimientos_contabilidad.php
// Módulo: Contabilidad
// Sistema: Inventa
//=====================================================

//=====================================================
// CONFIGURACIÓN DE RESPUESTA
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

header(
    "Cache-Control: no-store, no-cache, must-revalidate, max-age=0"
);

header("Pragma: no-cache");

//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// RESPUESTA JSON
//=====================================================

function responderJSON(
    $estado,
    $mensaje = "",
    $datos = []
) {
    echo json_encode(
        array_merge(
            [
                "estado" => $estado,
                "mensaje" => $mensaje
            ],
            $datos
        ),
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !$conexion
) {
    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos.",
        [
            "movimientos" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// USUARIO DE SESIÓN
//=====================================================

$idUser = 0;

if (isset($_SESSION["idUser"])) {
    $idUser = (int) $_SESSION["idUser"];
}

//=====================================================
// VALIDAR USUARIO
//=====================================================

if ($idUser <= 0) {
    responderJSON(
        false,
        "La sesión del usuario no es válida.",
        [
            "movimientos" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// OBTENER FILTROS
//=====================================================

$anio = isset($_GET["anio"])
    ? trim((string) $_GET["anio"])
    : "";

$periodo = isset($_GET["periodo"])
    ? trim((string) $_GET["periodo"])
    : "todos";

$fechaInicio = isset($_GET["fecha_inicio"])
    ? trim((string) $_GET["fecha_inicio"])
    : "";

$fechaFin = isset($_GET["fecha_fin"])
    ? trim((string) $_GET["fecha_fin"])
    : "";

//=====================================================
// NORMALIZAR AÑO
//=====================================================

$anioNumero = 0;

if (
    $anio !== "" &&
    ctype_digit($anio)
) {
    $anioNumero = (int) $anio;

    if (
        $anioNumero < 2000 ||
        $anioNumero > 2100
    ) {
        $anioNumero = 0;
    }
}

//=====================================================
// NORMALIZAR PERÍODO
//=====================================================

$periodoNumero = 0;

if (
    $periodo !== "" &&
    $periodo !== "todos" &&
    ctype_digit($periodo)
) {
    $periodoNumero = (int) $periodo;

    if (
        $periodoNumero < 1 ||
        $periodoNumero > 12
    ) {
        $periodoNumero = 0;
    }
}

//=====================================================
// VALIDAR FECHA
//=====================================================

function fechaEsValida($fecha)
{
    if (!$fecha) {
        return false;
    }

    $objeto = DateTime::createFromFormat(
        "Y-m-d",
        $fecha
    );

    return (
        $objeto !== false &&
        $objeto->format("Y-m-d") === $fecha
    );
}

//=====================================================
// FECHA INICIO
//=====================================================

if (
    $fechaInicio !== "" &&
    !fechaEsValida($fechaInicio)
) {
    $fechaInicio = "";
}

//=====================================================
// FECHA FIN
//=====================================================

if (
    $fechaFin !== "" &&
    !fechaEsValida($fechaFin)
) {
    $fechaFin = "";
}

//=====================================================
// VALIDAR RANGO
//=====================================================

if (
    $fechaInicio !== "" &&
    $fechaFin !== "" &&
    $fechaInicio > $fechaFin
) {
    responderJSON(
        false,
        "La fecha de inicio no puede ser mayor que la fecha final.",
        [
            "movimientos" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// FILTROS SQL
//=====================================================

$filtroVentas = "";
$filtroGastos = "";
$filtroPagos = "";

//=====================================================
// PARÁMETROS
//=====================================================
//
// IMPORTANTE:
// Los parámetros deben agregarse EXACTAMENTE
// en el mismo orden en que aparecen los ?
// dentro de la consulta SQL.
//
//=====================================================

$tipos = "";
$parametros = [];

//=====================================================
// 1. USUARIO - VENTAS
//=====================================================

$tipos .= "i";

$parametros[] = $idUser;

//=====================================================
// FILTROS - VENTAS
//=====================================================

if ($anioNumero > 0) {

    $filtroVentas .= "
        AND YEAR(tv.fecha_venta) = ?
    ";

    $tipos .= "i";

    $parametros[] = $anioNumero;
}

if ($periodoNumero > 0) {

    $filtroVentas .= "
        AND MONTH(tv.fecha_venta) = ?
    ";

    $tipos .= "i";

    $parametros[] = $periodoNumero;
}

if ($fechaInicio !== "") {

    $filtroVentas .= "
        AND tv.fecha_venta >= ?
    ";

    $tipos .= "s";

    $parametros[] = $fechaInicio . " 00:00:00";
}

if ($fechaFin !== "") {

    $filtroVentas .= "
        AND tv.fecha_venta < DATE_ADD(?, INTERVAL 1 DAY)
    ";

    $tipos .= "s";

    $parametros[] = $fechaFin;
}

//=====================================================
// 2. USUARIO - GASTOS
//=====================================================

$tipos .= "i";

$parametros[] = $idUser;

//=====================================================
// FILTROS - GASTOS
//=====================================================

if ($anioNumero > 0) {

    $filtroGastos .= "
        AND YEAR(dg.fecha) = ?
    ";

    $tipos .= "i";

    $parametros[] = $anioNumero;
}

if ($periodoNumero > 0) {

    $filtroGastos .= "
        AND MONTH(dg.fecha) = ?
    ";

    $tipos .= "i";

    $parametros[] = $periodoNumero;
}

if ($fechaInicio !== "") {

    $filtroGastos .= "
        AND dg.fecha >= ?
    ";

    $tipos .= "s";

    $parametros[] = $fechaInicio . " 00:00:00";
}

if ($fechaFin !== "") {

    $filtroGastos .= "
        AND dg.fecha < DATE_ADD(?, INTERVAL 1 DAY)
    ";

    $tipos .= "s";

    $parametros[] = $fechaFin;
}

//=====================================================
// 3. USUARIO - PAGOS
//=====================================================

$tipos .= "i";

$parametros[] = $idUser;

//=====================================================
// FILTROS - PAGOS
//=====================================================

if ($anioNumero > 0) {

    $filtroPagos .= "
        AND YEAR(pe.fecha_pago) = ?
    ";

    $tipos .= "i";

    $parametros[] = $anioNumero;
}

if ($periodoNumero > 0) {

    $filtroPagos .= "
        AND MONTH(pe.fecha_pago) = ?
    ";

    $tipos .= "i";

    $parametros[] = $periodoNumero;
}

if ($fechaInicio !== "") {

    $filtroPagos .= "
        AND pe.fecha_pago >= ?
    ";

    $tipos .= "s";

    $parametros[] = $fechaInicio . " 00:00:00";
}

if ($fechaFin !== "") {

    $filtroPagos .= "
        AND pe.fecha_pago < DATE_ADD(?, INTERVAL 1 DAY)
    ";

    $tipos .= "s";

    $parametros[] = $fechaFin;
}

//=====================================================
// CONSULTA
//=====================================================

$sql = "

    /*=================================================
      1. INGRESOS POR VENTAS
    =================================================*/

    SELECT

        tv.id_ticket_ventas AS id_movimiento,

        tv.fecha_venta AS fecha,

        CONCAT(
            'Venta #',
            tv.id_ticket_ventas
        ) AS concepto,

        'Ventas' AS categoria,

        COALESCE(
            mp.nombre,
            'Sin método'
        ) AS metodo_pago,

        'ingreso' AS tipo,

        CAST(
            tv.total_venta AS DECIMAL(15,2)
        ) AS monto,

        'ticket_ventas' AS origen

    FROM ticket_ventas tv

    LEFT JOIN metodo_pago mp
        ON mp.id_metodo_pago = tv.id_metodo_pago
        AND mp.id_user = tv.id_user
        AND COALESCE(mp.Eliminado, 0) = 0

    WHERE
        tv.id_user = ?

        AND (
            tv.estado_venta IS NULL
            OR UPPER(
                TRIM(tv.estado_venta)
            ) NOT IN (
                'CANCELADO',
                'CANCELADA'
            )
        )

        $filtroVentas


    UNION ALL


    /*=================================================
      2. GASTOS REGISTRADOS
    =================================================*/

    SELECT

        dg.id_deposito AS id_movimiento,

        dg.fecha AS fecha,

        COALESCE(
            NULLIF(
                TRIM(dg.concepto),
                ''
            ),
            'Gasto registrado'
        ) AS concepto,

        COALESCE(
            c.nombre,
            'Sin categoría'
        ) AS categoria,

        COALESCE(
            mp.nombre,
            'Sin método'
        ) AS metodo_pago,

        'gasto' AS tipo,

        CAST(
            dg.monto_pago AS DECIMAL(15,2)
        ) AS monto,

        'deposito_gasto' AS origen

    FROM deposito_gasto dg

    LEFT JOIN categorias c
        ON c.id_categorias = dg.id_categoria
        AND c.id_user = dg.id_user
        AND COALESCE(c.Eliminado, 0) = 0

    LEFT JOIN metodo_pago mp
        ON mp.id_metodo_pago = dg.id_metodo_pago
        AND mp.id_user = dg.id_user
        AND COALESCE(mp.Eliminado, 0) = 0

    WHERE
        dg.id_user = ?

        AND COALESCE(
            dg.Eliminado,
            0
        ) = 0

        $filtroGastos


    UNION ALL


    /*=================================================
      3. PAGOS DE EMPLEADOS
    =================================================*/

    SELECT

        pe.id_pago AS id_movimiento,

        pe.fecha_pago AS fecha,

        CONCAT(
            'Pago de empleado: ',
            COALESCE(
                CONCAT(
                    e.nombre,
                    ' ',
                    e.apellido
                ),
                'Empleado'
            )
        ) AS concepto,

        'Sueldos y pagos' AS categoria,

        COALESCE(
            mp.nombre,
            'Sin método'
        ) AS metodo_pago,

        'gasto' AS tipo,

        CAST(
            pe.monto_total AS DECIMAL(15,2)
        ) AS monto,

        'pago_empleado' AS origen

    FROM pago_empleado pe

    LEFT JOIN empleados e
        ON e.id_empleado = pe.id_empleado
        AND e.id_user = pe.id_user

    LEFT JOIN metodo_pago mp
        ON mp.id_metodo_pago = pe.id_metodo_pago
        AND mp.id_user = pe.id_user
        AND COALESCE(mp.Eliminado, 0) = 0

    WHERE
        pe.id_user = ?

        AND UPPER(
            TRIM(pe.estado)
        ) = 'PAGADO'

        $filtroPagos


    ORDER BY
        fecha DESC,
        id_movimiento DESC

    LIMIT 50
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
        "Error preparar movimientos contabilidad: " .
            mysqli_error($conexion)
    );

    responderJSON(
        false,
        "No se pudo preparar la consulta de movimientos contables.",
        [
            "movimientos" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// VALIDAR CANTIDAD DE PARÁMETROS
//=====================================================

$cantidadParametros = count($parametros);

$cantidadEsperada = strlen($tipos);

if ($cantidadParametros !== $cantidadEsperada) {

    error_log(
        "Error parámetros movimientos contabilidad. " .
            "Esperados: " .
            $cantidadEsperada .
            " | Recibidos: " .
            $cantidadParametros .
            " | Tipos: " .
            $tipos
    );

    mysqli_stmt_close($stmt);

    responderJSON(
        false,
        "Error interno al preparar los filtros de movimientos.",
        [
            "movimientos" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// VINCULAR PARÁMETROS
//=====================================================

$referencias = [];

$referencias[] = $stmt;
$referencias[] = $tipos;

foreach ($parametros as $indice => $valor) {

    $referencias[] = &$parametros[$indice];
}

call_user_func_array(
    "mysqli_stmt_bind_param",
    $referencias
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    $error = mysqli_stmt_error($stmt);

    error_log(
        "Error ejecutar obtener_movimientos_contabilidad.php: " .
            $error
    );

    mysqli_stmt_close($stmt);

    responderJSON(
        false,
        "No se pudieron obtener los movimientos contables.",
        [
            "movimientos" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = mysqli_stmt_get_result($stmt);

if ($resultado === false) {

    $error = mysqli_stmt_error($stmt);

    error_log(
        "Error resultado movimientos contabilidad: " .
            $error
    );

    mysqli_stmt_close($stmt);

    responderJSON(
        false,
        "No se pudo obtener el resultado de los movimientos contables.",
        [
            "movimientos" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// PROCESAR MOVIMIENTOS
//=====================================================

$movimientos = [];

while ($fila = mysqli_fetch_assoc($resultado)) {

    $idMovimiento = isset($fila["id_movimiento"])
        ? (int) $fila["id_movimiento"]
        : 0;

    $fecha = isset($fila["fecha"])
        ? (string) $fila["fecha"]
        : "";

    $concepto = isset($fila["concepto"])
        ? trim((string) $fila["concepto"])
        : "Sin concepto";

    $categoria = isset($fila["categoria"])
        ? trim((string) $fila["categoria"])
        : "Sin categoría";

    $metodoPago = isset($fila["metodo_pago"])
        ? trim((string) $fila["metodo_pago"])
        : "Sin método";

    $tipo = isset($fila["tipo"])
        ? strtolower(trim((string) $fila["tipo"]))
        : "gasto";

    $monto = isset($fila["monto"])
        ? (float) $fila["monto"]
        : 0.00;

    $origen = isset($fila["origen"])
        ? (string) $fila["origen"]
        : "";

    $movimientos[] = [

        "id_movimiento" =>
        $idMovimiento,

        "fecha" =>
        $fecha,

        "concepto" =>
        $concepto,

        "categoria" =>
        $categoria,

        "nombre_categoria" =>
        $categoria,

        "metodo_pago" =>
        $metodoPago,

        "nombre_metodo_pago" =>
        $metodoPago,

        "tipo" =>
        $tipo,

        "monto" =>
        $monto,

        "monto_pago" =>
        $monto,

        "origen" =>
        $origen
    ];
}

//=====================================================
// CERRAR
//=====================================================

mysqli_free_result($resultado);

mysqli_stmt_close($stmt);

//=====================================================
// RESPUESTA
//=====================================================

responderJSON(
    true,
    "Movimientos contables obtenidos correctamente.",
    [
        "movimientos" =>
        $movimientos,

        "total" =>
        count($movimientos)
    ]
);
