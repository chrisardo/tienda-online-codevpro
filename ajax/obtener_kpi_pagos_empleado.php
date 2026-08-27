<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_kpi_pagos_empleado.php
// Módulo: Pagos a Empleados
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

header('Content-Type: application/json; charset=utf-8');

//=====================================================
// FUNCIÓN RESPUESTA
//=====================================================

function responderJSON(array $respuesta): void
{
    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {

    responderJSON([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ]);
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    responderJSON([
        'success' => false,
        'mensaje' => 'No se pudo establecer la conexión con la base de datos.'
    ]);
}

$conexion->set_charset("utf8mb4");

//=====================================================
// CONSULTA KPI
//=====================================================
//
// Se obtienen los tres KPI en una sola consulta:
//
// 1. Total pagado
// 2. Total pendiente
// 3. Total pagado durante el mes actual
//
// TRIM + UPPER permiten evitar problemas si el campo
// estado tiene espacios o diferencias entre mayúsculas
// y minúsculas.
//=====================================================

$sql = "
    SELECT

        COALESCE(
            SUM(
                CASE
                    WHEN UPPER(TRIM(estado)) = 'PAGADO'
                    THEN monto_total
                    ELSE 0
                END
            ),
            0
        ) AS total_pagado,

        COALESCE(
            SUM(
                CASE
                    WHEN UPPER(TRIM(estado)) = 'PENDIENTE'
                    THEN monto_total
                    ELSE 0
                END
            ),
            0
        ) AS total_pendiente,

        COALESCE(
            SUM(
                CASE
                    WHEN UPPER(TRIM(estado)) = 'PAGADO'
                    AND fecha_pago IS NOT NULL
                    AND YEAR(fecha_pago) = YEAR(CURDATE())
                    AND MONTH(fecha_pago) = MONTH(CURDATE())
                    THEN monto_total
                    ELSE 0
                END
            ),
            0
        ) AS total_mes_actual

    FROM pago_empleado

    WHERE id_user = ?
";

//=====================================================
// PREPARAR
//=====================================================

$stmt = $conexion->prepare($sql);

if (!$stmt) {

    responderJSON([
        'success' => false,
        'mensaje' => 'Error al preparar la consulta de KPI.',
        'error' => $conexion->error
    ]);
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

    responderJSON([
        'success' => false,
        'mensaje' => 'No fue posible obtener los KPI.',
        'error' => $stmt->error
    ]);
}

//=====================================================
// RESULTADO
//=====================================================

$resultado = $stmt->get_result();

$fila = $resultado->fetch_assoc();

$stmt->close();

//=====================================================
// OBTENER VALORES
//=====================================================

$totalPagado = isset($fila['total_pagado'])
    ? (float) $fila['total_pagado']
    : 0;

$totalPendiente = isset($fila['total_pendiente'])
    ? (float) $fila['total_pendiente']
    : 0;

$totalMesActual = isset($fila['total_mes_actual'])
    ? (float) $fila['total_mes_actual']
    : 0;

//=====================================================
// RESPUESTA
//=====================================================

responderJSON([

    'success' => true,

    'mensaje' => 'KPI obtenidos correctamente.',

    'datos' => [

        'total_pagado' => round(
            $totalPagado,
            2
        ),

        'total_pendiente' => round(
            $totalPendiente,
            2
        ),

        'total_mes_actual' => round(
            $totalMesActual,
            2
        )

    ]

]);
