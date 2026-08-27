<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_kpi_sueldos.php
// Módulo: Sueldos y Pagos
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
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
    !($conexion instanceof mysqli)
) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se pudo establecer la conexión con la base de datos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// VARIABLES KPI
//=====================================================

$totalEmpleados = 0;
$sueldosActivos = 0;
$empleadosSinSueldo = 0;
$nominaMensual = 0.00;

//=====================================================
// PROCESO
//=====================================================

try {

    //=================================================
    // KPI 1
    // TOTAL DE EMPLEADOS ACTIVOS
    //=================================================

    $sql = "
        SELECT COUNT(*) AS total
        FROM empleados
        WHERE id_user = ?
        AND estado = 'ACTIVO'
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            'Error al preparar el KPI de empleados.'
        );
    }

    $stmt->bind_param(
        "i",
        $idUser
    );

    if (!$stmt->execute()) {

        $stmt->close();

        throw new Exception(
            'Error al ejecutar el KPI de empleados.'
        );
    }

    $resultado = $stmt->get_result();

    $fila = $resultado->fetch_assoc();

    $totalEmpleados = (int) (
        $fila['total'] ?? 0
    );

    $stmt->close();


    //=================================================
    // KPI 2
    // SUELDOS ACTIVOS
    //=================================================

    $sql = "
        SELECT COUNT(*) AS total
        FROM sueldo_empleado
        WHERE id_user = ?
        AND estado = 'ACTIVO'
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            'Error al preparar el KPI de sueldos activos.'
        );
    }

    $stmt->bind_param(
        "i",
        $idUser
    );

    if (!$stmt->execute()) {

        $stmt->close();

        throw new Exception(
            'Error al ejecutar el KPI de sueldos activos.'
        );
    }

    $resultado = $stmt->get_result();

    $fila = $resultado->fetch_assoc();

    $sueldosActivos = (int) (
        $fila['total'] ?? 0
    );

    $stmt->close();


    //=================================================
    // KPI 3
    // EMPLEADOS SIN SUELDO ACTIVO
    //=================================================

    $sql = "
        SELECT COUNT(*) AS total

        FROM empleados e

        LEFT JOIN sueldo_empleado s
            ON s.id_empleado = e.id_empleado
            AND s.id_user = e.id_user
            AND s.estado = 'ACTIVO'

        WHERE
            e.id_user = ?
            AND e.estado = 'ACTIVO'
            AND s.id_sueldo IS NULL
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            'Error al preparar el KPI de empleados sin sueldo.'
        );
    }

    $stmt->bind_param(
        "i",
        $idUser
    );

    if (!$stmt->execute()) {

        $stmt->close();

        throw new Exception(
            'Error al ejecutar el KPI de empleados sin sueldo.'
        );
    }

    $resultado = $stmt->get_result();

    $fila = $resultado->fetch_assoc();

    $empleadosSinSueldo = (int) (
        $fila['total'] ?? 0
    );

    $stmt->close();


    //=================================================
    // KPI 4
    // NÓMINA MENSUAL
    //=================================================
    //
    // MENSUAL:
    // sueldo_base
    //
    // QUINCENAL:
    // sueldo_base * 2
    //
    // SEMANAL:
    // sueldo_base * 52 / 12
    //
    // DIARIO:
    // sueldo_base * 30
    //
    // Solo se consideran sueldos ACTIVOS.
    //=================================================

    $sql = "
        SELECT

            COALESCE(
                SUM(
                    CASE

                        WHEN tipo_base = 'MENSUAL'
                            THEN sueldo_base

                        WHEN tipo_base = 'QUINCENAL'
                            THEN sueldo_base * 2

                        WHEN tipo_base = 'SEMANAL'
                            THEN sueldo_base * 52 / 12

                        WHEN tipo_base = 'DIARIO'
                            THEN sueldo_base * 30

                        ELSE 0

                    END
                ),
                0
            ) AS nomina_mensual

        FROM sueldo_empleado

        WHERE
            id_user = ?
            AND estado = 'ACTIVO'
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            'Error al preparar el KPI de nómina mensual.'
        );
    }

    $stmt->bind_param(
        "i",
        $idUser
    );

    if (!$stmt->execute()) {

        $stmt->close();

        throw new Exception(
            'Error al ejecutar el KPI de nómina mensual.'
        );
    }

    $resultado = $stmt->get_result();

    $fila = $resultado->fetch_assoc();

    $nominaMensual = (float) (
        $fila['nomina_mensual'] ?? 0
    );

    $stmt->close();


    //=================================================
    // RESPUESTA
    //=================================================

    echo json_encode([

        'success' => true,

        'kpi' => [

            'empleados' => $totalEmpleados,

            'sueldos_activos' => $sueldosActivos,

            'sin_sueldo' => $empleadosSinSueldo,

            'nomina_mensual' => round(
                $nominaMensual,
                2
            )

        ]

    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([

        'success' => false,

        'mensaje' => $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);
}
