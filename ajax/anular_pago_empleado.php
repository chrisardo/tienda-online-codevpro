<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/anular_pago_empleado.php
// Módulo: Pagos a Empleados
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// RESPUESTA JSON
//=====================================================

header('Content-Type: application/json; charset=utf-8');


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;


if ($idUser <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'La sesión ha expirado. Inicie sesión nuevamente.'
    ]);

    exit;
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!$conexion) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se pudo establecer conexión con la base de datos.'
    ]);

    exit;
}


//=====================================================
// RECIBIR ID DEL PAGO
//=====================================================

$idPago = isset($_POST['id_pago'])
    ? (int) $_POST['id_pago']
    : 0;


if ($idPago <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'El identificador del pago no es válido.'
    ]);

    exit;
}


//=====================================================
// INICIAR TRANSACCIÓN
//=====================================================

mysqli_begin_transaction($conexion);


try {


    //=================================================
    // OBTENER INFORMACIÓN DEL PAGO
    //=================================================

    $sqlPago = "
        SELECT
            id_pago,
            id_empleado,
            id_sueldo,
            monto_total,
            id_cuenta_bancaria,
            estado
        FROM pago_empleado
        WHERE id_pago = ?
          AND id_user = ?
        LIMIT 1
    ";


    $stmtPago = mysqli_prepare(
        $conexion,
        $sqlPago
    );


    if (!$stmtPago) {

        throw new Exception(
            'No se pudo preparar la consulta del pago.'
        );
    }


    mysqli_stmt_bind_param(
        $stmtPago,
        "ii",
        $idPago,
        $idUser
    );


    mysqli_stmt_execute($stmtPago);


    $resultadoPago =
        mysqli_stmt_get_result($stmtPago);


    $pago =
        mysqli_fetch_assoc($resultadoPago);


    mysqli_stmt_close($stmtPago);


    //=================================================
    // VERIFICAR QUE EXISTA
    //=================================================

    if (!$pago) {

        throw new Exception(
            'El pago no existe o no pertenece a este usuario.'
        );
    }


    $estadoActual =
        strtoupper(trim($pago['estado']));


    $montoTotal =
        (float) $pago['monto_total'];


    $idCuenta =
        (int) $pago['id_cuenta_bancaria'];


    //=================================================
    // VALIDAR ESTADO
    //=================================================

    if ($estadoActual === 'ANULADO') {

        throw new Exception(
            'El pago seleccionado ya se encuentra anulado.'
        );
    }


    //=================================================
    // SI ESTÁ PAGADO, DEVOLVER EL DINERO A LA CUENTA
    //=================================================

    if ($estadoActual === 'PAGADO') {


        //=============================================
        // VALIDAR CUENTA BANCARIA
        //=============================================

        if ($idCuenta <= 0) {

            throw new Exception(
                'El pago está marcado como PAGADO, pero no tiene una cuenta bancaria asociada.'
            );
        }


        //=============================================
        // BLOQUEAR CUENTA
        //=============================================

        $sqlCuenta = "
            SELECT
                id_cuenta_bancaria,
                balance
            FROM cuenta_banco
            WHERE id_cuenta_bancaria = ?
              AND id_user = ?
              AND Eliminado = 0
            FOR UPDATE
        ";


        $stmtCuenta = mysqli_prepare(
            $conexion,
            $sqlCuenta
        );


        if (!$stmtCuenta) {

            throw new Exception(
                'No se pudo preparar la consulta de la cuenta bancaria.'
            );
        }


        mysqli_stmt_bind_param(
            $stmtCuenta,
            "ii",
            $idCuenta,
            $idUser
        );


        mysqli_stmt_execute($stmtCuenta);


        $resultadoCuenta =
            mysqli_stmt_get_result($stmtCuenta);


        $cuenta =
            mysqli_fetch_assoc($resultadoCuenta);


        mysqli_stmt_close($stmtCuenta);


        if (!$cuenta) {

            throw new Exception(
                'La cuenta bancaria asociada al pago no existe o no está disponible.'
            );
        }


        //=============================================
        // DEVOLVER MONTO A LA CUENTA
        //=============================================

        $sqlActualizarCuenta = "
            UPDATE cuenta_banco
            SET balance = balance + ?
            WHERE id_cuenta_bancaria = ?
              AND id_user = ?
              AND Eliminado = 0
        ";


        $stmtActualizarCuenta = mysqli_prepare(
            $conexion,
            $sqlActualizarCuenta
        );


        if (!$stmtActualizarCuenta) {

            throw new Exception(
                'No se pudo preparar la actualización de la cuenta bancaria.'
            );
        }


        mysqli_stmt_bind_param(
            $stmtActualizarCuenta,
            "dii",
            $montoTotal,
            $idCuenta,
            $idUser
        );


        if (
            !mysqli_stmt_execute(
                $stmtActualizarCuenta
            )
        ) {

            mysqli_stmt_close(
                $stmtActualizarCuenta
            );

            throw new Exception(
                'No se pudo actualizar el saldo de la cuenta bancaria.'
            );
        }


        mysqli_stmt_close(
            $stmtActualizarCuenta
        );
    }


    //=================================================
    // CAMBIAR ESTADO DEL PAGO A ANULADO
    //=================================================

    $sqlAnular = "
        UPDATE pago_empleado
        SET
            estado = 'ANULADO',
            fecha_actualizado = NOW()
        WHERE id_pago = ?
          AND id_user = ?
          AND estado <> 'ANULADO'
    ";


    $stmtAnular = mysqli_prepare(
        $conexion,
        $sqlAnular
    );


    if (!$stmtAnular) {

        throw new Exception(
            'No se pudo preparar la anulación del pago.'
        );
    }


    mysqli_stmt_bind_param(
        $stmtAnular,
        "ii",
        $idPago,
        $idUser
    );


    if (
        !mysqli_stmt_execute(
            $stmtAnular
        )
    ) {

        mysqli_stmt_close(
            $stmtAnular
        );

        throw new Exception(
            'No se pudo anular el pago.'
        );
    }


    $filasAfectadas =
        mysqli_stmt_affected_rows(
            $stmtAnular
        );


    mysqli_stmt_close($stmtAnular);


    if ($filasAfectadas <= 0) {

        throw new Exception(
            'El pago no pudo ser anulado.'
        );
    }


    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    mysqli_commit($conexion);


    //=================================================
    // RESPUESTA
    //=================================================

    echo json_encode([
        'success' => true,
        'mensaje' => 'El pago del empleado fue anulado correctamente.',
        'id_pago' => $idPago,
        'estado' => 'ANULADO',
        'monto_reintegrado' => (
            $estadoActual === 'PAGADO'
            ? number_format($montoTotal, 2, '.', '')
            : '0.00'
        )
    ]);

    exit;
} catch (Throwable $e) {


    //=================================================
    // DESHACER TRANSACCIÓN
    //=================================================

    mysqli_rollback($conexion);


    //=================================================
    // RESPUESTA DE ERROR
    //=================================================

    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);

    exit;
}
