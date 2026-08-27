<?php

//=====================================================
// CoDevPro Technology
// Archivo: ajax/registrar_pago_empleado.php
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
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// DATOS
//=====================================================

$idEmpleado = isset($_POST['id_empleado'])
    ? (int) $_POST['id_empleado']
    : 0;

$idSueldo = isset($_POST['id_sueldo'])
    ? (int) $_POST['id_sueldo']
    : 0;

$periodoInicio =
    trim($_POST['periodo_inicio'] ?? '');

$periodoFin =
    trim($_POST['periodo_fin'] ?? '');

$montoBase =
    isset($_POST['monto_base'])
    ? (float) $_POST['monto_base']
    : 0;

$bonificaciones =
    isset($_POST['bonificaciones'])
    ? (float) $_POST['bonificaciones']
    : 0;

$descuentos =
    isset($_POST['descuentos'])
    ? (float) $_POST['descuentos']
    : 0;

$montoTotal =
    isset($_POST['monto_total'])
    ? (float) $_POST['monto_total']
    : 0;

$fechaPago =
    trim($_POST['fecha_pago'] ?? '');

$idCuenta =
    isset($_POST['id_cuenta_bancaria'])
    ? (int) $_POST['id_cuenta_bancaria']
    : 0;

$idMetodo =
    isset($_POST['id_metodo_pago'])
    ? (int) $_POST['id_metodo_pago']
    : 0;

$estado =
    strtoupper(
        trim($_POST['estado'] ?? 'PENDIENTE')
    );

$observacion =
    trim($_POST['observacion'] ?? '');

//=====================================================
// VALIDACIONES
//=====================================================

if ($idEmpleado <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'El empleado no es válido.'
    ]);

    exit;
}

if ($idSueldo <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'El sueldo no es válido.'
    ]);

    exit;
}

if (!$periodoInicio || !$periodoFin) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Debe indicar el período del pago.'
    ]);

    exit;
}

if ($periodoInicio > $periodoFin) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'El período de inicio no puede ser posterior al período final.'
    ]);

    exit;
}

if ($montoBase <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'El monto base debe ser mayor que cero.'
    ]);

    exit;
}

if ($bonificaciones < 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Las bonificaciones no pueden ser negativas.'
    ]);

    exit;
}

if ($descuentos < 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Los descuentos no pueden ser negativos.'
    ]);

    exit;
}

//=====================================================
// RECALCULAR TOTAL EN SERVIDOR
//=====================================================

$montoTotalCalculado =
    $montoBase +
    $bonificaciones -
    $descuentos;

if ($montoTotalCalculado <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'El monto total debe ser mayor que cero.'
    ]);

    exit;
}

// No confiar en el total enviado por JavaScript.
$montoTotal =
    $montoTotalCalculado;

//=====================================================
// ESTADO
//=====================================================

$estadosPermitidos = [
    'PENDIENTE',
    'PAGADO',
    'ANULADO'
];

if (!in_array(
    $estado,
    $estadosPermitidos,
    true
)) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'El estado del pago no es válido.'
    ]);

    exit;
}

//=====================================================
// TRANSACCIÓN
//=====================================================

mysqli_begin_transaction($conexion);

try {

    //=================================================
    // VALIDAR EMPLEADO
    //=================================================

    $sqlEmpleado = "

        SELECT
            id_empleado

        FROM empleados

        WHERE id_empleado = ?

          AND id_user = ?

          AND estado = 'ACTIVO'

        LIMIT 1

    ";

    $stmtEmpleado =
        mysqli_prepare(
            $conexion,
            $sqlEmpleado
        );

    if (!$stmtEmpleado) {
        throw new Exception(
            "No se pudo validar el empleado."
        );
    }

    mysqli_stmt_bind_param(
        $stmtEmpleado,
        "ii",
        $idEmpleado,
        $idUser
    );

    mysqli_stmt_execute(
        $stmtEmpleado
    );

    $resultadoEmpleado =
        mysqli_stmt_get_result(
            $stmtEmpleado
        );

    $empleado =
        mysqli_fetch_assoc(
            $resultadoEmpleado
        );

    mysqli_stmt_close(
        $stmtEmpleado
    );

    if (!$empleado) {
        throw new Exception(
            "El empleado no existe, está inactivo o no pertenece a su cuenta."
        );
    }

    //=================================================
    // VALIDAR SUELDO
    //=================================================

    $sqlSueldo = "

        SELECT
            id_sueldo,
            sueldo_base,
            tipo_pago

        FROM sueldo_empleado

        WHERE id_sueldo = ?

          AND id_empleado = ?

          AND id_user = ?

          AND estado = 'ACTIVO'

        LIMIT 1

    ";

    $stmtSueldo =
        mysqli_prepare(
            $conexion,
            $sqlSueldo
        );

    if (!$stmtSueldo) {
        throw new Exception(
            "No se pudo validar el sueldo."
        );
    }

    mysqli_stmt_bind_param(
        $stmtSueldo,
        "iii",
        $idSueldo,
        $idEmpleado,
        $idUser
    );

    mysqli_stmt_execute(
        $stmtSueldo
    );

    $resultadoSueldo =
        mysqli_stmt_get_result(
            $stmtSueldo
        );

    $sueldo =
        mysqli_fetch_assoc(
            $resultadoSueldo
        );

    mysqli_stmt_close(
        $stmtSueldo
    );

    if (!$sueldo) {

        throw new Exception(
            "El sueldo seleccionado no es válido."
        );
    }

    //=================================================
    // VALIDAR CUENTA BANCARIA
    //=================================================

    $sqlCuenta = "

        SELECT
            id_cuenta_bancaria

        FROM cuenta_banco

        WHERE id_cuenta_bancaria = ?

          AND id_user = ?

          AND Eliminado = 0

        LIMIT 1

    ";

    $stmtCuenta =
        mysqli_prepare(
            $conexion,
            $sqlCuenta
        );

    if (!$stmtCuenta) {
        throw new Exception(
            "No se pudo validar la cuenta bancaria."
        );
    }

    mysqli_stmt_bind_param(
        $stmtCuenta,
        "ii",
        $idCuenta,
        $idUser
    );

    mysqli_stmt_execute(
        $stmtCuenta
    );

    $resultadoCuenta =
        mysqli_stmt_get_result(
            $stmtCuenta
        );

    $cuenta =
        mysqli_fetch_assoc(
            $resultadoCuenta
        );

    mysqli_stmt_close(
        $stmtCuenta
    );

    if (!$cuenta) {

        throw new Exception(
            "La cuenta bancaria seleccionada no es válida."
        );
    }

    //=================================================
    // VALIDAR MÉTODO DE PAGO
    //=================================================

    $sqlMetodo = "

        SELECT
            id_metodo_pago

        FROM metodo_pago

        WHERE id_metodo_pago = ?

          AND id_user = ?

          AND Eliminado = 0

        LIMIT 1

    ";

    $stmtMetodo =
        mysqli_prepare(
            $conexion,
            $sqlMetodo
        );

    if (!$stmtMetodo) {
        throw new Exception(
            "No se pudo validar el método de pago."
        );
    }

    mysqli_stmt_bind_param(
        $stmtMetodo,
        "ii",
        $idMetodo,
        $idUser
    );

    mysqli_stmt_execute(
        $stmtMetodo
    );

    $resultadoMetodo =
        mysqli_stmt_get_result(
            $stmtMetodo
        );

    $metodo =
        mysqli_fetch_assoc(
            $resultadoMetodo
        );

    mysqli_stmt_close(
        $stmtMetodo
    );

    if (!$metodo) {

        throw new Exception(
            "El método de pago seleccionado no es válido."
        );
    }

    //=================================================
    // EVITAR PAGO DUPLICADO
    //=================================================

    $sqlDuplicado = "

        SELECT
            id_pago

        FROM pago_empleado

        WHERE id_empleado = ?

          AND id_user = ?

          AND periodo_inicio = ?

          AND periodo_fin = ?

          AND estado <> 'ANULADO'

        LIMIT 1

    ";

    $stmtDuplicado =
        mysqli_prepare(
            $conexion,
            $sqlDuplicado
        );

    if (!$stmtDuplicado) {
        throw new Exception(
            "No se pudo validar pagos anteriores."
        );
    }

    mysqli_stmt_bind_param(
        $stmtDuplicado,
        "iiss",
        $idEmpleado,
        $idUser,
        $periodoInicio,
        $periodoFin
    );

    mysqli_stmt_execute(
        $stmtDuplicado
    );

    $resultadoDuplicado =
        mysqli_stmt_get_result(
            $stmtDuplicado
        );

    $duplicado =
        mysqli_fetch_assoc(
            $resultadoDuplicado
        );

    mysqli_stmt_close(
        $stmtDuplicado
    );

    if ($duplicado) {

        throw new Exception(
            "Ya existe un pago registrado para este empleado y período."
        );
    }

    //=================================================
    // INSERTAR PAGO
    //=================================================

    $sqlInsertar = "

        INSERT INTO pago_empleado (

            id_empleado,

            id_sueldo,

            periodo_inicio,

            periodo_fin,

            monto_base,

            bonificaciones,

            descuentos,

            monto_total,

            fecha_pago,

            id_cuenta_bancaria,

            id_metodo_pago,

            estado,

            observacion,

            id_user,

            fecha_registro,

            fecha_actualizado

        )

        VALUES (

            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            NOW(),
            NOW()

        )

    ";

    $stmtInsertar =
        mysqli_prepare(
            $conexion,
            $sqlInsertar
        );

    if (!$stmtInsertar) {
        throw new Exception(
            "No se pudo preparar el registro del pago."
        );
    }

    mysqli_stmt_bind_param(
        $stmtInsertar,
        "iissddddsiissi",
        $idEmpleado,
        $idSueldo,
        $periodoInicio,
        $periodoFin,
        $montoBase,
        $bonificaciones,
        $descuentos,
        $montoTotal,
        $fechaPago,
        $idCuenta,
        $idMetodo,
        $estado,
        $observacion,
        $idUser
    );

    if (!mysqli_stmt_execute(
        $stmtInsertar
    )) {

        throw new Exception(
            "No se pudo registrar el pago: " .
                mysqli_stmt_error($stmtInsertar)
        );
    }

    $idPago =
        mysqli_insert_id($conexion);

    mysqli_stmt_close(
        $stmtInsertar
    );

    //=================================================
    // CONFIRMAR
    //=================================================

    mysqli_commit(
        $conexion
    );

    echo json_encode([

        'success' => true,

        'mensaje' =>
        'El pago se registró correctamente.',

        'id_pago' =>
        $idPago

    ], JSON_UNESCAPED_UNICODE);

    exit;
} catch (Throwable $e) {

    mysqli_rollback(
        $conexion
    );

    echo json_encode([

        'success' => false,

        'mensaje' =>
        $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

    exit;
}
