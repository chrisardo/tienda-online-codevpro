<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_actualizar_movimiento.php
// Módulo: Ingresos y Gastos
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// CONFIGURACIÓN
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

require_once "../controladores/conexion.php";

//=====================================================
// RESPUESTA JSON
//=====================================================

function responderJSON($status, $mensaje, $datos = [])
{
    echo json_encode(
        [
            "status" => $status,
            "mensaje" => $mensaje,
            "datos" => $datos
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    responderJSON(
        "error",
        "No se pudo establecer la conexión con la base de datos."
    );
}

//=====================================================
// SOLO POST
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    responderJSON(
        "error",
        "Método de solicitud no permitido."
    );
}

//=====================================================
// OBTENER DATOS
//=====================================================

$idDeposito = isset($_POST["id_deposito"])
    ? (int) $_POST["id_deposito"]
    : 0;

$tipo = isset($_POST["tipo"])
    ? strtoupper(trim($_POST["tipo"]))
    : "";

$idCuentaBancaria = isset($_POST["id_cuenta_bancaria"]) &&
    $_POST["id_cuenta_bancaria"] !== ""
    ? (int) $_POST["id_cuenta_bancaria"]
    : 0;

$idCategoria = isset($_POST["id_categoria"]) &&
    $_POST["id_categoria"] !== ""
    ? (int) $_POST["id_categoria"]
    : 0;

$idProveedor = isset($_POST["id_proveedor"]) &&
    $_POST["id_proveedor"] !== ""
    ? (int) $_POST["id_proveedor"]
    : 0;

$idMetodoPago = isset($_POST["id_metodo_pago"]) &&
    $_POST["id_metodo_pago"] !== ""
    ? (int) $_POST["id_metodo_pago"]
    : 0;

$fecha = isset($_POST["fecha"])
    ? trim($_POST["fecha"])
    : "";

$concepto = isset($_POST["concepto"])
    ? trim($_POST["concepto"])
    : "";

$montoPago = isset($_POST["monto_pago"])
    ? (float) $_POST["monto_pago"]
    : 0;

$descripcion = isset($_POST["descripcion"])
    ? trim($_POST["descripcion"])
    : "";

//=====================================================
// VALIDACIONES BÁSICAS
//=====================================================

if ($idDeposito <= 0) {

    responderJSON(
        "error",
        "El ID del movimiento no es válido."
    );
}

if ($tipo !== "INGRESO" && $tipo !== "GASTO") {

    responderJSON(
        "error",
        "El tipo de movimiento no es válido."
    );
}

if ($idCuentaBancaria <= 0) {

    responderJSON(
        "error",
        "Debe seleccionar una cuenta bancaria."
    );
}

if ($idCategoria <= 0) {

    responderJSON(
        "error",
        "Debe seleccionar una categoría."
    );
}

if ($idMetodoPago <= 0) {

    responderJSON(
        "error",
        "Debe seleccionar un método de pago."
    );
}

if ($fecha === "") {

    responderJSON(
        "error",
        "Debe seleccionar una fecha."
    );
}

if ($concepto === "") {

    responderJSON(
        "error",
        "Debe ingresar el concepto del movimiento."
    );
}

if ($montoPago <= 0) {

    responderJSON(
        "error",
        "El monto debe ser mayor a 0."
    );
}

//=====================================================
// VALIDAR FECHA
//=====================================================

$fechaObjeto = DateTime::createFromFormat("Y-m-d", $fecha);

if (
    !$fechaObjeto ||
    $fechaObjeto->format("Y-m-d") !== $fecha
) {

    responderJSON(
        "error",
        "La fecha del movimiento no es válida."
    );
}

//=====================================================
// INICIAR TRANSACCIÓN
//=====================================================

mysqli_begin_transaction($conexion);

try {

    //=================================================
    // OBTENER MOVIMIENTO ACTUAL
    //=================================================

    $sqlMovimiento = "
        SELECT
            id_deposito,
            tipo,
            id_cuenta_bancaria,
            id_categoria,
            id_proveedor,
            id_metodo_pago,
            fecha,
            concepto,
            monto_pago,
            descripcion
        FROM deposito_gasto
        WHERE id_deposito = ?
        LIMIT 1
    ";

    $stmtMovimiento = mysqli_prepare(
        $conexion,
        $sqlMovimiento
    );

    if (!$stmtMovimiento) {

        throw new Exception(
            "No se pudo preparar la consulta del movimiento."
        );
    }

    mysqli_stmt_bind_param(
        $stmtMovimiento,
        "i",
        $idDeposito
    );

    if (!mysqli_stmt_execute($stmtMovimiento)) {

        mysqli_stmt_close($stmtMovimiento);

        throw new Exception(
            "No se pudo obtener el movimiento."
        );
    }

    $resultadoMovimiento = mysqli_stmt_get_result(
        $stmtMovimiento
    );

    $movimientoAnterior = mysqli_fetch_assoc(
        $resultadoMovimiento
    );

    mysqli_stmt_close($stmtMovimiento);

    if (!$movimientoAnterior) {

        throw new Exception(
            "El movimiento que intenta actualizar no existe."
        );
    }

    //=================================================
    // DATOS ANTERIORES
    //=================================================

    $tipoAnterior = strtoupper(
        trim($movimientoAnterior["tipo"] ?? "")
    );

    $cuentaAnterior = (int) (
        $movimientoAnterior["id_cuenta_bancaria"] ?? 0
    );

    $montoAnterior = (float) (
        $movimientoAnterior["monto_pago"] ?? 0
    );

    //=================================================
    // VALIDAR CUENTA NUEVA
    //=================================================

    $sqlCuenta = "
        SELECT
            id_cuenta_bancaria,
            nombre,
            balance
        FROM cuenta_banco
        WHERE id_cuenta_bancaria = ?
        LIMIT 1
    ";

    $stmtCuenta = mysqli_prepare(
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
        "i",
        $idCuentaBancaria
    );

    mysqli_stmt_execute($stmtCuenta);

    $resultadoCuenta = mysqli_stmt_get_result(
        $stmtCuenta
    );

    $cuentaNueva = mysqli_fetch_assoc(
        $resultadoCuenta
    );

    mysqli_stmt_close($stmtCuenta);

    if (!$cuentaNueva) {

        throw new Exception(
            "La cuenta bancaria seleccionada no existe."
        );
    }

    //=================================================
    // REVERTIR MOVIMIENTO ANTERIOR
    //=================================================
    //
    // INGRESO anterior:
    //     balance - monto
    //
    // GASTO anterior:
    //     balance + monto
    //
    //=================================================

    if ($cuentaAnterior > 0 && $montoAnterior > 0) {

        if ($tipoAnterior === "INGRESO") {

            $sqlRevertir = "
                UPDATE cuenta_banco
                SET balance = balance - ?
                WHERE id_cuenta_bancaria = ?
            ";
        } else {

            $sqlRevertir = "
                UPDATE cuenta_banco
                SET balance = balance + ?
                WHERE id_cuenta_bancaria = ?
            ";
        }

        $stmtRevertir = mysqli_prepare(
            $conexion,
            $sqlRevertir
        );

        if (!$stmtRevertir) {

            throw new Exception(
                "No se pudo revertir el saldo anterior."
            );
        }

        mysqli_stmt_bind_param(
            $stmtRevertir,
            "di",
            $montoAnterior,
            $cuentaAnterior
        );

        if (!mysqli_stmt_execute($stmtRevertir)) {

            mysqli_stmt_close($stmtRevertir);

            throw new Exception(
                "No se pudo revertir el saldo de la cuenta anterior."
            );
        }

        mysqli_stmt_close($stmtRevertir);
    }

    //=================================================
    // APLICAR NUEVO MOVIMIENTO
    //=================================================
    //
    // INGRESO:
    //     balance + monto
    //
    // GASTO:
    //     balance - monto
    //
    //=================================================

    if ($tipo === "INGRESO") {

        $sqlAplicar = "
            UPDATE cuenta_banco
            SET balance = balance + ?
            WHERE id_cuenta_bancaria = ?
        ";
    } else {

        $sqlAplicar = "
            UPDATE cuenta_banco
            SET balance = balance - ?
            WHERE id_cuenta_bancaria = ?
        ";
    }

    $stmtAplicar = mysqli_prepare(
        $conexion,
        $sqlAplicar
    );

    if (!$stmtAplicar) {

        throw new Exception(
            "No se pudo preparar la actualización del saldo."
        );
    }

    mysqli_stmt_bind_param(
        $stmtAplicar,
        "di",
        $montoPago,
        $idCuentaBancaria
    );

    if (!mysqli_stmt_execute($stmtAplicar)) {

        mysqli_stmt_close($stmtAplicar);

        throw new Exception(
            "No se pudo actualizar el saldo de la cuenta."
        );
    }

    mysqli_stmt_close($stmtAplicar);

    //=================================================
    // ACTUALIZAR MOVIMIENTO
    //=================================================

    $sqlActualizar = "
        UPDATE deposito_gasto
        SET
            tipo = ?,
            id_cuenta_bancaria = ?,
            id_categoria = ?,
            id_proveedor = NULLIF(?, 0),
            id_metodo_pago = ?,
            fecha = ?,
            concepto = ?,
            monto_pago = ?,
            descripcion = ?
        WHERE id_deposito = ?
    ";

    $stmtActualizar = mysqli_prepare(
        $conexion,
        $sqlActualizar
    );

    if (!$stmtActualizar) {

        throw new Exception(
            "No se pudo preparar la actualización del movimiento."
        );
    }

    mysqli_stmt_bind_param(
        $stmtActualizar,
        "siiiissdsi",
        $tipo,
        $idCuentaBancaria,
        $idCategoria,
        $idProveedor,
        $idMetodoPago,
        $fecha,
        $concepto,
        $montoPago,
        $descripcion,
        $idDeposito
    );

    if (!mysqli_stmt_execute($stmtActualizar)) {

        mysqli_stmt_close($stmtActualizar);

        throw new Exception(
            "No se pudo actualizar el movimiento."
        );
    }

    $filasAfectadas = mysqli_stmt_affected_rows(
        $stmtActualizar
    );

    mysqli_stmt_close($stmtActualizar);

    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    mysqli_commit($conexion);

    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        "success",
        "Movimiento actualizado correctamente.",
        [
            "id_deposito" => $idDeposito,
            "tipo" => $tipo,
            "id_cuenta_bancaria" => $idCuentaBancaria,
            "id_categoria" => $idCategoria,
            "id_proveedor" => $idProveedor,
            "id_metodo_pago" => $idMetodoPago,
            "fecha" => $fecha,
            "concepto" => $concepto,
            "monto_pago" => number_format(
                $montoPago,
                2,
                ".",
                ""
            ),
            "descripcion" => $descripcion,
            "filas_afectadas" => $filasAfectadas
        ]
    );
} catch (Throwable $e) {

    //=================================================
    // REVERTIR TRANSACCIÓN
    //=================================================

    mysqli_rollback($conexion);

    //=================================================
    // LOG DEL ERROR
    //=================================================

    error_log(
        "Error en adm_actualizar_movimiento.php: " .
            $e->getMessage()
    );

    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        "error",
        $e->getMessage()
    );
}
