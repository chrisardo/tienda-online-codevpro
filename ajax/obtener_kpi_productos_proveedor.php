<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_kpi_productos_proveedor.php
// Módulo: Productos del Proveedor
// Sistema: Inventa
//=====================================================

declare(strict_types=1);


//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=UTF-8");


//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON(
    bool $success,
    string $mensaje = "",
    array $datos = [],
    int $codigoHTTP = 200
): void {

    http_response_code($codigoHTTP);

    echo json_encode(
        array_merge(
            [
                "success" => $success,
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
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;


if ($idUser <= 0) {

    responderJSON(
        false,
        "Sesión no válida. Debe iniciar sesión nuevamente.",
        [],
        401
    );
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !($conexion instanceof mysqli)) {

    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos.",
        [],
        500
    );
}


//=====================================================
// CONFIGURAR CHARSET
//=====================================================

$conexion->set_charset("utf8mb4");


//=====================================================
// CONSULTA KPI
//=====================================================
//
// TOTAL
// -----
// Todos los productos del usuario que NO están
// eliminados.
//
// ACTIVOS
// -------
// Productos disponibles en catálogo.
//
// SIN STOCK
// ---------
// Productos activos cuyo stock es 0 o menor.
//
// VALOR INVENTARIO
// ----------------
// stock × costo_compra
//
// Se calcula únicamente sobre productos activos.
//
//=====================================================

$sql = "
    SELECT

        COUNT(*) AS total,

        SUM(
            CASE
                WHEN p.Eliminado = 0
                THEN 1
                ELSE 0
            END
        ) AS activos,

        SUM(
            CASE
                WHEN p.Eliminado = 0
                     AND COALESCE(p.stock, 0) <= 0
                THEN 1
                ELSE 0
            END
        ) AS sin_stock,

        COALESCE(
            SUM(
                CASE
                    WHEN p.Eliminado = 0
                    THEN
                        COALESCE(p.stock, 0)
                        *
                        COALESCE(p.costo_compra, 0)
                    ELSE 0
                END
            ),
            0
        ) AS valor_inventario

    FROM producto p

    WHERE
        p.id_user = ?
";


//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = $conexion->prepare($sql);


if (!$stmt) {

    responderJSON(
        false,
        "No se pudo preparar la consulta de KPI.",
        [],
        500
    );
}


//=====================================================
// BIND
//=====================================================

$stmt->bind_param(
    "i",
    $idUser
);


//=====================================================
// EJECUTAR
//=====================================================

if (!$stmt->execute()) {

    $stmt->close();

    responderJSON(
        false,
        "No se pudieron obtener los KPI de productos.",
        [],
        500
    );
}


//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = $stmt->get_result();


//=====================================================
// VALIDAR RESULTADO
//=====================================================

if (!$resultado) {

    $stmt->close();

    responderJSON(
        false,
        "No se pudo obtener el resultado de los KPI.",
        [],
        500
    );
}


//=====================================================
// DATOS
//=====================================================

$fila = $resultado->fetch_assoc();


//=====================================================
// NORMALIZAR VALORES
//=====================================================

$total = isset($fila["total"])
    ? (int) $fila["total"]
    : 0;


$activos = isset($fila["activos"])
    ? (int) $fila["activos"]
    : 0;


$sinStock = isset($fila["sin_stock"])
    ? (int) $fila["sin_stock"]
    : 0;


$valorInventario = isset($fila["valor_inventario"])
    ? (float) $fila["valor_inventario"]
    : 0;


//=====================================================
// CERRAR
//=====================================================

$stmt->close();


//=====================================================
// RESPUESTA
//=====================================================

responderJSON(
    true,
    "KPI obtenidos correctamente.",
    [

        "total" => $total,

        "activos" => $activos,

        "sin_stock" => $sinStock,

        "valor_inventario" => round($valorInventario, 2)

    ]
);
