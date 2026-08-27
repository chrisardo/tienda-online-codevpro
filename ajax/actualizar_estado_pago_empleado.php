<?php

//=====================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_estado_pago_empleado.php
// Módulo: Pagos a Empleados
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// SESIÓN
//=====================================================

session_start();

//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=utf-8");

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "No fue posible conectar con la base de datos."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = intval(
    $_SESSION["idUser"] ?? 0
);

if ($idUser <= 0) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "La sesión ha expirado. Inicie sesión nuevamente."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "Método de solicitud no permitido."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// RECIBIR DATOS
//=====================================================

$idPago = intval(
    $_POST["id_pago"] ?? 0
);

$estadoSolicitado =
    strtoupper(
        trim(
            $_POST["estado"] ?? ""
        )
    );

//=====================================================
// VALIDAR ID
//=====================================================

if ($idPago <= 0) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "El identificador del pago no es válido."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VALIDAR ESTADO
//=====================================================

if ($estadoSolicitado !== "PAGADO") {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "Estado de pago no permitido."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// INICIAR TRANSACCIÓN
//=====================================================

try {

    $conexion->begin_transaction();

    //=================================================
    // 1. OBTENER PAGO
    //=================================================

    $sqlPago = "
        SELECT
            p.id_pago,
            p.id_empleado,
            p.id_sueldo,
            p.monto_total,
            p.fecha_pago,
            p.id_cuenta_bancaria,
            p.id_metodo_pago,
            p.estado,
            p.observacion,
            e.nombre,
            e.apellido
        FROM pago_empleado p
        INNER JOIN empleados e
            ON e.id_empleado = p.id_empleado
        WHERE
            p.id_pago = ?
            AND p.id_user = ?
            AND e.id_user = ?
        LIMIT 1
    ";

    $stmtPago =
        $conexion->prepare(
            $sqlPago
        );

    if (!$stmtPago) {

        throw new Exception(
            "No fue posible preparar la consulta del pago."
        );
    }

    $stmtPago->bind_param(
        "iii",
        $idPago,
        $idUser,
        $idUser
    );

    if (!$stmtPago->execute()) {

        throw new Exception(
            "No fue posible consultar el pago."
        );
    }

    $resultadoPago =
        $stmtPago->get_result();

    if (
        !$resultadoPago ||
        $resultadoPago->num_rows === 0
    ) {

        $stmtPago->close();

        throw new Exception(
            "El pago no existe o no pertenece al usuario actual."
        );
    }

    $pago =
        $resultadoPago->fetch_assoc();

    $stmtPago->close();

    //=================================================
    // 2. VALIDAR ESTADO ACTUAL
    //=================================================

    $estadoActual =
        strtoupper(
            trim(
                $pago["estado"] ?? ""
            )
        );

    if ($estadoActual === "PAGADO") {

        throw new Exception(
            "Este pago ya se encuentra marcado como PAGADO."
        );
    }

    if ($estadoActual === "ANULADO") {

        throw new Exception(
            "No se puede marcar como pagado un pago ANULADO."
        );
    }

    if ($estadoActual !== "PENDIENTE") {

        throw new Exception(
            "El estado actual del pago no permite realizar esta operación."
        );
    }

    //=================================================
    // 3. VALIDAR MONTO
    //=================================================

    $montoTotal =
        (float)($pago["monto_total"] ?? 0);

    if ($montoTotal <= 0) {

        throw new Exception(
            "El monto total del pago no es válido."
        );
    }

    //=================================================
    // 4. VALIDAR CUENTA BANCARIA
    //=================================================

    $idCuenta =
        intval(
            $pago["id_cuenta_bancaria"] ?? 0
        );

    if ($idCuenta <= 0) {

        throw new Exception(
            "El pago no tiene una cuenta bancaria asociada."
        );
    }

    //=================================================
    // 5. OBTENER CUENTA CON BLOQUEO
    //=================================================

    $sqlCuenta = "
        SELECT
            id_cuenta_bancaria,
            nombre,
            balance
        FROM cuenta_banco
        WHERE
            id_cuenta_bancaria = ?
            AND id_user = ?
            AND Eliminado = 0
        FOR UPDATE
    ";

    $stmtCuenta =
        $conexion->prepare(
            $sqlCuenta
        );

    if (!$stmtCuenta) {

        throw new Exception(
            "No fue posible preparar la consulta de la cuenta bancaria."
        );
    }

    $stmtCuenta->bind_param(
        "ii",
        $idCuenta,
        $idUser
    );

    if (!$stmtCuenta->execute()) {

        $stmtCuenta->close();

        throw new Exception(
            "No fue posible consultar la cuenta bancaria."
        );
    }

    $resultadoCuenta =
        $stmtCuenta->get_result();

    if (
        !$resultadoCuenta ||
        $resultadoCuenta->num_rows === 0
    ) {

        $stmtCuenta->close();

        throw new Exception(
            "La cuenta bancaria no existe, está eliminada o no pertenece al usuario."
        );
    }

    $cuenta =
        $resultadoCuenta->fetch_assoc();

    $stmtCuenta->close();

    //=================================================
    // 6. VALIDAR BALANCE
    //=================================================

    $balanceActual =
        (float)($cuenta["balance"] ?? 0);

    if (
        $balanceActual < $montoTotal
    ) {

        throw new Exception(
            "El saldo de la cuenta bancaria (" .
                number_format(
                    $balanceActual,
                    2,
                    ".",
                    ""
                ) .
                ") es insuficiente para pagar " .
                number_format(
                    $montoTotal,
                    2,
                    ".",
                    ""
                ) .
                "."
        );
    }

    //=================================================
    // 7. NUEVO BALANCE
    //=================================================

    $nuevoBalance =
        $balanceActual -
        $montoTotal;

    //=================================================
    // 8. ACTUALIZAR CUENTA BANCARIA
    //=================================================

    $sqlActualizarCuenta = "
        UPDATE cuenta_banco
        SET
            balance = ?
        WHERE
            id_cuenta_bancaria = ?
            AND id_user = ?
            AND Eliminado = 0
    ";

    $stmtActualizarCuenta =
        $conexion->prepare(
            $sqlActualizarCuenta
        );

    if (!$stmtActualizarCuenta) {

        throw new Exception(
            "No fue posible preparar la actualización de la cuenta."
        );
    }

    $stmtActualizarCuenta->bind_param(
        "dii",
        $nuevoBalance,
        $idCuenta,
        $idUser
    );

    if (
        !$stmtActualizarCuenta->execute()
    ) {

        $stmtActualizarCuenta->close();

        throw new Exception(
            "No fue posible actualizar el saldo de la cuenta bancaria."
        );
    }

    if (
        $stmtActualizarCuenta->affected_rows <= 0
    ) {

        $stmtActualizarCuenta->close();

        throw new Exception(
            "No se pudo actualizar el saldo de la cuenta bancaria."
        );
    }

    $stmtActualizarCuenta->close();

    //=================================================
    // 9. ACTUALIZAR PAGO
    //=================================================

    $fechaPagoActual =
        date("Y-m-d");

    $sqlActualizarPago = "
        UPDATE pago_empleado
        SET
            estado = 'PAGADO',
            fecha_pago = ?,
            fecha_actualizado = NOW()
        WHERE
            id_pago = ?
            AND id_user = ?
            AND estado = 'PENDIENTE'
    ";

    $stmtActualizarPago =
        $conexion->prepare(
            $sqlActualizarPago
        );

    if (!$stmtActualizarPago) {

        throw new Exception(
            "No fue posible preparar la actualización del pago."
        );
    }

    $stmtActualizarPago->bind_param(
        "sii",
        $fechaPagoActual,
        $idPago,
        $idUser
    );

    if (
        !$stmtActualizarPago->execute()
    ) {

        $stmtActualizarPago->close();

        throw new Exception(
            "No fue posible actualizar el estado del pago."
        );
    }

    if (
        $stmtActualizarPago->affected_rows <= 0
    ) {

        $stmtActualizarPago->close();

        throw new Exception(
            "El pago ya no está pendiente o no pudo actualizarse."
        );
    }

    $stmtActualizarPago->close();

    //=================================================
    // 10. CONFIRMAR TRANSACCIÓN
    //=================================================

    $conexion->commit();

    //=================================================
    // NOMBRE EMPLEADO
    //=================================================

    $nombreEmpleado =
        trim(
            ($pago["nombre"] ?? "") .
                " " .
                ($pago["apellido"] ?? "")
        );

    //=================================================
    // RESPUESTA
    //=================================================

    echo json_encode(
        [
            "success" => true,

            "mensaje" =>
            "El pago de " .
                $nombreEmpleado .
                " fue marcado como pagado correctamente.",

            "datos" => [

                "id_pago" =>
                $idPago,

                "estado" =>
                "PAGADO",

                "fecha_pago" =>
                $fechaPagoActual,

                "monto_total" =>
                number_format(
                    $montoTotal,
                    2,
                    ".",
                    ""
                ),

                "id_cuenta_bancaria" =>
                $idCuenta,

                "balance_anterior" =>
                number_format(
                    $balanceActual,
                    2,
                    ".",
                    ""
                ),

                "balance_nuevo" =>
                number_format(
                    $nuevoBalance,
                    2,
                    ".",
                    ""
                )
            ]
        ],
        JSON_UNESCAPED_UNICODE
    );
} catch (Throwable $e) {

    //=================================================
    // ROLLBACK
    //=================================================

    if (
        isset($conexion) &&
        $conexion->connect_errno === 0
    ) {

        $conexion->rollback();
    }

    //=================================================
    // LOG
    //=================================================

    error_log(
        "Error actualizar_estado_pago_empleado.php: " .
            $e->getMessage()
    );

    //=================================================
    // RESPUESTA ERROR
    //=================================================

    echo json_encode(
        [
            "success" => false,

            "mensaje" =>
            $e->getMessage()
        ],
        JSON_UNESCAPED_UNICODE
    );
}

//=====================================================
// CERRAR CONEXIÓN
//=====================================================

if (isset($conexion)) {

    $conexion->close();
}
