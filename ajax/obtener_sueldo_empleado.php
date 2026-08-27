<?php

//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_sueldos_empleado.php
// Módulo: Pagos a Empleados
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

//=====================================================
// SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ]);

    exit;
}

//=====================================================
// ID EMPLEADO
//=====================================================

$idEmpleado = isset($_GET['id_empleado'])
    ? (int) $_GET['id_empleado']
    : 0;

if ($idEmpleado <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Empleado no válido.'
    ]);

    exit;
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// CONSULTAR SUELDO ACTIVO
//=====================================================

$sql = "

    SELECT

        se.id_sueldo,

        se.id_empleado,

        se.sueldo_base,

        se.tipo_pago,

        se.fecha_inicio,

        se.fecha_fin,

        se.estado,

        se.observacion

    FROM sueldo_empleado se

    INNER JOIN empleados e
        ON e.id_empleado = se.id_empleado
       AND e.id_user = se.id_user

    WHERE se.id_empleado = ?

      AND se.id_user = ?

      AND se.estado = 'ACTIVO'

      AND e.estado = 'ACTIVO'

    ORDER BY se.id_sueldo DESC

    LIMIT 1

";

//=====================================================
// PREPARAR
//=====================================================

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se pudo preparar la consulta del sueldo.'
    ]);

    exit;
}

//=====================================================
// PARÁMETROS
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idEmpleado,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se pudo consultar el sueldo del empleado.'
    ]);

    exit;
}

//=====================================================
// RESULTADO
//=====================================================

$resultado = mysqli_stmt_get_result($stmt);

$sueldo = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

//=====================================================
// SIN SUELDO
//=====================================================

if (!$sueldo) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'El empleado no tiene un sueldo activo registrado.'
    ]);

    exit;
}

//=====================================================
// RESPUESTA
//=====================================================

echo json_encode([

    'success' => true,

    'datos' => [

        'sueldos' => [

            $sueldo

        ]

    ]

], JSON_UNESCAPED_UNICODE);

exit;
