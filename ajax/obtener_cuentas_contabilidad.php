<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_cuentas_contabilidad.php
// Módulo: Contabilidad
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// CONFIGURACIÓN DE RESPUESTA
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

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
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON($estado, $mensaje = "", $datos = [])
{
    echo json_encode(
        array_merge(
            [
                "estado" => $estado,
                "mensaje" => $mensaje
            ],
            $datos
        ),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {
    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos.",
        [
            "cuentas" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// OBTENER USUARIO DE SESIÓN
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
            "cuentas" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// OBTENER PARÁMETROS
//=====================================================
//
// El JS envía:
// - anio
// - periodo
// - fecha_inicio
// - fecha_fin
// - meses
//
// Estos parámetros no son necesarios para obtener
// las cuentas bancarias, porque el balance pertenece
// directamente a cada cuenta en cuenta_banco.
//
// Se reciben para mantener compatibilidad con el
// sistema de filtros de contabilidad.
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

$meses = isset($_GET["meses"])
    ? (int) $_GET["meses"]
    : 12;

//=====================================================
// CONSULTAR CUENTAS BANCARIAS
//=====================================================
//
// IMPORTANTE:
//
// No se aplican aquí los filtros de fecha.
//
// La tabla cuenta_banco contiene el balance actual
// de cada cuenta.
//
// Los filtros de fecha corresponden al resumen,
// movimientos y gráficos de contabilidad.
//
//=====================================================

$sql = "
    SELECT
        cb.id_cuenta_bancaria,
        cb.nombre,
        cb.balance
    FROM cuenta_banco cb
    WHERE
        cb.id_user = ?
        AND COALESCE(cb.Eliminado, 0) = 0
    ORDER BY
        cb.nombre ASC
";

//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    responderJSON(
        false,
        "No se pudo preparar la consulta de cuentas bancarias.",
        [
            "cuentas" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// VINCULAR USUARIO
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {
    $error = mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);

    responderJSON(
        false,
        "No se pudieron obtener las cuentas bancarias.",
        [
            "cuentas" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = mysqli_stmt_get_result($stmt);

if ($resultado === false) {
    mysqli_stmt_close($stmt);

    responderJSON(
        false,
        "No se pudo obtener el resultado de las cuentas bancarias.",
        [
            "cuentas" => [],
            "total" => 0
        ]
    );
}

//=====================================================
// PROCESAR CUENTAS
//=====================================================

$cuentas = [];

while ($fila = mysqli_fetch_assoc($resultado)) {

    $idCuenta = isset($fila["id_cuenta_bancaria"])
        ? (int) $fila["id_cuenta_bancaria"]
        : 0;

    $nombre = isset($fila["nombre"])
        ? trim((string) $fila["nombre"])
        : "Cuenta bancaria";

    $balance = isset($fila["balance"])
        ? (float) $fila["balance"]
        : 0.00;

    $cuentas[] = [
        "id_cuenta_bancaria" => $idCuenta,
        "nombre" => $nombre,
        "balance" => $balance
    ];
}

//=====================================================
// CERRAR CONSULTA
//=====================================================

mysqli_free_result($resultado);

mysqli_stmt_close($stmt);

//=====================================================
// TOTAL DE CUENTAS
//=====================================================

$totalCuentas = count($cuentas);

//=====================================================
// RESPUESTA
//=====================================================

responderJSON(
    true,
    "Cuentas bancarias obtenidas correctamente.",
    [
        "cuentas" => $cuentas,
        "total" => $totalCuentas
    ]
);
