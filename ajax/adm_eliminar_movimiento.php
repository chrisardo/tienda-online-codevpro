<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_eliminar_movimiento.php
// Módulo: Ingresos y Gastos
// Sistema: Inventa
//=====================================================

header('Content-Type: application/json; charset=utf-8');

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// RESPUESTA JSON
//=====================================================

function respuestaJSON($status, $mensaje, $datos = [])
{
    echo json_encode(
        [
            "status" => $status,
            "mensaje" => $mensaje,
            "datos"   => $datos
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    respuestaJSON(
        "error",
        "Método de solicitud no permitido."
    );
}

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!$conexion) {

    respuestaJSON(
        "error",
        "No se pudo establecer conexión con la base de datos."
    );
}

//=====================================================
// OBTENER ID
//=====================================================

$idDeposito = isset($_POST["id_deposito"])
    ? (int) $_POST["id_deposito"]
    : 0;

//=====================================================
// VALIDAR ID
//=====================================================

if ($idDeposito <= 0) {

    respuestaJSON(
        "error",
        "El ID del movimiento no es válido."
    );
}

//=====================================================
// INICIAR TRANSACCIÓN
//=====================================================

mysqli_begin_transaction($conexion);

try {

    //=================================================
    // BUSCAR MOVIMIENTO
    //=================================================

    $sqlBuscar = "
        SELECT
            id_deposito,
            id_cuenta_bancaria,
            monto_pago,
            tipo,
            id_user,
            Eliminado
        FROM deposito_gasto
        WHERE id_deposito = ?
        LIMIT 1
    ";

    $stmtBuscar = mysqli_prepare(
        $conexion,
        $sqlBuscar
    );

    if (!$stmtBuscar) {

        throw new Exception(
            "No se pudo preparar la consulta del movimiento."
        );
    }

    mysqli_stmt_bind_param(
        $stmtBuscar,
        "i",
        $idDeposito
    );

    if (!mysqli_stmt_execute($stmtBuscar)) {

        mysqli_stmt_close($stmtBuscar);

        throw new Exception(
            "No se pudo consultar el movimiento."
        );
    }

    $resultado = mysqli_stmt_get_result($stmtBuscar);

    $movimiento = mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmtBuscar);

    //=================================================
    // VERIFICAR EXISTENCIA
    //=================================================

    if (!$movimiento) {

        throw new Exception(
            "El movimiento no existe."
        );
    }

    //=================================================
    // VERIFICAR SI YA ESTÁ ELIMINADO
    //=================================================

    $eliminado = isset($movimiento["Eliminado"])
        ? (int) $movimiento["Eliminado"]
        : 0;

    if ($eliminado === 1) {

        throw new Exception(
            "El movimiento ya se encuentra eliminado."
        );
    }

    //=================================================
    // DATOS DEL MOVIMIENTO
    //=================================================

    $idCuenta = isset($movimiento["id_cuenta_bancaria"])
        ? (int) $movimiento["id_cuenta_bancaria"]
        : 0;

    $monto = isset($movimiento["monto_pago"])
        ? (float) $movimiento["monto_pago"]
        : 0;

    $tipo = strtoupper(
        trim(
            $movimiento["tipo"] ?? ""
        )
    );

    //=================================================
    // VALIDAR MONTO
    //=================================================

    if ($monto <= 0) {

        throw new Exception(
            "El monto del movimiento no es válido."
        );
    }

    //=================================================
    // RESTAURAR BALANCE DE LA CUENTA
    //=================================================

    /*
     * Si fue INGRESO:
     *
     * Al registrar:
     * balance = balance + monto
     *
     * Al eliminar:
     * balance = balance - monto
     *
     * ----------------------------------------------
     *
     * Si fue GASTO:
     *
     * Al registrar:
     * balance = balance - monto
     *
     * Al eliminar:
     * balance = balance + monto
     */

    if ($idCuenta > 0) {

        if ($tipo === "INGRESO") {

            $sqlBalance = "
                UPDATE cuenta_banco
                SET balance = balance - ?
                WHERE id_cuenta_bancaria = ?
                LIMIT 1
            ";
        } elseif ($tipo === "GASTO") {

            $sqlBalance = "
                UPDATE cuenta_banco
                SET balance = balance + ?
                WHERE id_cuenta_bancaria = ?
                LIMIT 1
            ";
        } else {

            throw new Exception(
                "El tipo de movimiento no es válido."
            );
        }

        $stmtBalance = mysqli_prepare(
            $conexion,
            $sqlBalance
        );

        if (!$stmtBalance) {

            throw new Exception(
                "No se pudo preparar la actualización de la cuenta bancaria."
            );
        }

        mysqli_stmt_bind_param(
            $stmtBalance,
            "di",
            $monto,
            $idCuenta
        );

        if (!mysqli_stmt_execute($stmtBalance)) {

            mysqli_stmt_close($stmtBalance);

            throw new Exception(
                "No se pudo actualizar el balance de la cuenta bancaria."
            );
        }

        mysqli_stmt_close($stmtBalance);
    }

    //=================================================
    // ELIMINACIÓN LÓGICA
    //=================================================

    $sqlEliminar = "
        UPDATE deposito_gasto
        SET Eliminado = 1
        WHERE id_deposito = ?
        AND Eliminado = 0
        LIMIT 1
    ";

    $stmtEliminar = mysqli_prepare(
        $conexion,
        $sqlEliminar
    );

    if (!$stmtEliminar) {

        throw new Exception(
            "No se pudo preparar la eliminación del movimiento."
        );
    }

    mysqli_stmt_bind_param(
        $stmtEliminar,
        "i",
        $idDeposito
    );

    if (!mysqli_stmt_execute($stmtEliminar)) {

        mysqli_stmt_close($stmtEliminar);

        throw new Exception(
            "No se pudo eliminar el movimiento."
        );
    }

    $filasAfectadas = mysqli_stmt_affected_rows(
        $stmtEliminar
    );

    mysqli_stmt_close($stmtEliminar);

    //=================================================
    // VERIFICAR ELIMINACIÓN
    //=================================================

    if ($filasAfectadas <= 0) {

        throw new Exception(
            "No se pudo actualizar el estado de eliminación del movimiento."
        );
    }

    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    mysqli_commit($conexion);

    //=================================================
    // RESPUESTA
    //=================================================

    respuestaJSON(
        "success",
        "Movimiento eliminado correctamente.",
        [
            "id_deposito" => $idDeposito,
            "tipo"        => $tipo,
            "monto"       => $monto,
            "id_cuenta"   => $idCuenta
        ]
    );
} catch (Throwable $e) {

    //=================================================
    // ROLLBACK
    //=================================================

    mysqli_rollback($conexion);

    //=================================================
    // RESPUESTA ERROR
    //=================================================

    respuestaJSON(
        "error",
        $e->getMessage()
    );
}
