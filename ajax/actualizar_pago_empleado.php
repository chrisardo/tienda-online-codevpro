<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_pago_empleado.php
// Módulo: Pagos a Empleados
// Sistema: Inventa
//=====================================================

declare(strict_types=1);

//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !($conexion instanceof mysqli)) {
    echo json_encode([
        "success" => false,
        "mensaje" => "No se pudo establecer la conexión con la base de datos."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// CONFIGURAR MYSQLI
//=====================================================

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//=====================================================
// OBTENER USUARIO ACTUAL
//=====================================================

$idUser = 0;

if (isset($_SESSION["id_user"])) {
    $idUser = (int) $_SESSION["id_user"];
} elseif (isset($_SESSION["idUser"])) {
    $idUser = (int) $_SESSION["idUser"];
}

//=====================================================
// VALIDAR USUARIO
//=====================================================

if ($idUser <= 0) {
    echo json_encode([
        "success" => false,
        "mensaje" => "La sesión de usuario no es válida o ha expirado."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// SOLO POST
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "mensaje" => "Método de solicitud no permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// FUNCIONES
//=====================================================

function responderExito(string $mensaje, array $datos = []): void
{
    echo json_encode([
        "success" => true,
        "mensaje" => $mensaje,
        "datos" => $datos
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

function responderError(string $mensaje, int $codigo = 400): void
{
    http_response_code($codigo);

    echo json_encode([
        "success" => false,
        "mensaje" => $mensaje
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// RECIBIR DATOS
//=====================================================

$idPago = isset($_POST["id_pago"])
    ? (int) $_POST["id_pago"]
    : 0;

$periodoInicio = trim($_POST["periodo_inicio"] ?? "");

$periodoFin = trim($_POST["periodo_fin"] ?? "");

$montoBase = isset($_POST["monto_base"])
    ? (float) $_POST["monto_base"]
    : 0;

$bonificaciones = isset($_POST["bonificaciones"])
    ? (float) $_POST["bonificaciones"]
    : 0;

$descuentos = isset($_POST["descuentos"])
    ? (float) $_POST["descuentos"]
    : 0;

$montoTotalRecibido = isset($_POST["monto_total"])
    ? (float) $_POST["monto_total"]
    : 0;

$fechaPago = trim($_POST["fecha_pago"] ?? "");

$idCuentaBancaria = isset($_POST["id_cuenta_bancaria"])
    ? (int) $_POST["id_cuenta_bancaria"]
    : 0;

$idMetodoPago = isset($_POST["id_metodo_pago"])
    ? (int) $_POST["id_metodo_pago"]
    : 0;

$estado = strtoupper(trim($_POST["estado"] ?? "PENDIENTE"));

$observacion = trim($_POST["observacion"] ?? "");

//=====================================================
// VALIDAR ID PAGO
//=====================================================

if ($idPago <= 0) {
    responderError("El identificador del pago no es válido.");
}

//=====================================================
// VALIDAR FECHAS
//=====================================================

function fechaValida(string $fecha): bool
{
    $objetoFecha = DateTime::createFromFormat("Y-m-d", $fecha);

    return $objetoFecha !== false &&
        $objetoFecha->format("Y-m-d") === $fecha;
}

if (!fechaValida($periodoInicio)) {
    responderError("La fecha de inicio del período no es válida.");
}

if (!fechaValida($periodoFin)) {
    responderError("La fecha de fin del período no es válida.");
}

if ($periodoInicio > $periodoFin) {
    responderError(
        "La fecha inicial no puede ser posterior a la fecha final."
    );
}

if (!fechaValida($fechaPago)) {
    responderError("La fecha de pago no es válida.");
}

//=====================================================
// VALIDAR MONTOS
//=====================================================

if ($montoBase <= 0) {
    responderError("El monto base debe ser mayor que cero.");
}

if ($bonificaciones < 0) {
    responderError("Las bonificaciones no pueden ser negativas.");
}

if ($descuentos < 0) {
    responderError("Los descuentos no pueden ser negativos.");
}

//=====================================================
// CALCULAR TOTAL EN SERVIDOR
//=====================================================

$montoTotalCalculado =
    $montoBase +
    $bonificaciones -
    $descuentos;

$montoTotalCalculado = max($montoTotalCalculado, 0);

//=====================================================
// VALIDAR TOTAL RECIBIDO
//=====================================================

// No confiamos en el monto_total enviado por JavaScript.
// El servidor vuelve a calcularlo.

if ($montoTotalCalculado <= 0) {
    responderError("El monto total debe ser mayor que cero.");
}

//=====================================================
// VALIDAR DIFERENCIA DEL TOTAL
//=====================================================

if (abs($montoTotalRecibido - $montoTotalCalculado) > 0.01) {
    responderError(
        "El monto total enviado no coincide con los valores del pago."
    );
}

//=====================================================
// VALIDAR CUENTA
//=====================================================

if ($idCuentaBancaria <= 0) {
    responderError("Debe seleccionar una cuenta bancaria.");
}

//=====================================================
// VALIDAR MÉTODO
//=====================================================

if ($idMetodoPago <= 0) {
    responderError("Debe seleccionar un método de pago.");
}

//=====================================================
// VALIDAR ESTADO
//=====================================================

// IMPORTANTE:
// Desde este endpoint no permitimos convertir directamente
// un pago pendiente en PAGADO.
// El proceso correcto es mediante:
// ajax/actualizar_estado_pago_empleado.php

$estadosPermitidos = [
    "PENDIENTE",
    "ANULADO"
];

if (!in_array($estado, $estadosPermitidos, true)) {
    responderError(
        "El estado seleccionado no es válido para una edición."
    );
}

//=====================================================
// INICIAR TRANSACCIÓN
//=====================================================

try {

    $conexion->begin_transaction();

    //=================================================
    // OBTENER PAGO ACTUAL
    //=================================================

    $sqlPago = "
        SELECT
            p.id_pago,
            p.id_empleado,
            p.id_sueldo,
            p.periodo_inicio,
            p.periodo_fin,
            p.monto_base,
            p.bonificaciones,
            p.descuentos,
            p.monto_total,
            p.fecha_pago,
            p.id_cuenta_bancaria,
            p.id_metodo_pago,
            p.estado,
            p.observacion,
            p.id_user
        FROM pago_empleado p
        WHERE p.id_pago = ?
          AND p.id_user = ?
        LIMIT 1
        FOR UPDATE
    ";

    $stmtPago = $conexion->prepare($sqlPago);

    $stmtPago->bind_param(
        "ii",
        $idPago,
        $idUser
    );

    $stmtPago->execute();

    $resultadoPago = $stmtPago->get_result();

    if ($resultadoPago->num_rows === 0) {

        $stmtPago->close();

        throw new Exception(
            "No se encontró el pago seleccionado."
        );
    }

    $pagoActual = $resultadoPago->fetch_assoc();

    $stmtPago->close();

    //=================================================
    // VERIFICAR ESTADO ACTUAL
    //=================================================

    $estadoActual = strtoupper(
        trim((string) $pagoActual["estado"])
    );

    if ($estadoActual === "PAGADO") {

        throw new Exception(
            "El pago ya fue marcado como pagado y no puede modificarse."
        );
    }

    //=================================================
    // VERIFICAR EMPLEADO
    //=================================================

    $idEmpleado = (int) $pagoActual["id_empleado"];

    if ($idEmpleado <= 0) {

        throw new Exception(
            "El pago no tiene un empleado válido asociado."
        );
    }

    $sqlEmpleado = "
        SELECT id_empleado
        FROM empleados
        WHERE id_empleado = ?
          AND id_user = ?
        LIMIT 1
    ";

    $stmtEmpleado = $conexion->prepare($sqlEmpleado);

    $stmtEmpleado->bind_param(
        "ii",
        $idEmpleado,
        $idUser
    );

    $stmtEmpleado->execute();

    $resultadoEmpleado = $stmtEmpleado->get_result();

    if ($resultadoEmpleado->num_rows === 0) {

        $stmtEmpleado->close();

        throw new Exception(
            "El empleado asociado al pago no es válido."
        );
    }

    $stmtEmpleado->close();

    //=================================================
    // VERIFICAR SUELDO
    //=================================================

    $idSueldo = (int) $pagoActual["id_sueldo"];

    if ($idSueldo <= 0) {

        throw new Exception(
            "El pago no tiene un sueldo asociado válido."
        );
    }

    $sqlSueldo = "
        SELECT
            id_sueldo
        FROM sueldo_empleado
        WHERE id_sueldo = ?
          AND id_empleado = ?
          AND id_user = ?
        LIMIT 1
    ";

    $stmtSueldo = $conexion->prepare($sqlSueldo);

    $stmtSueldo->bind_param(
        "iii",
        $idSueldo,
        $idEmpleado,
        $idUser
    );

    $stmtSueldo->execute();

    $resultadoSueldo = $stmtSueldo->get_result();

    if ($resultadoSueldo->num_rows === 0) {

        $stmtSueldo->close();

        throw new Exception(
            "El sueldo asociado al pago no es válido."
        );
    }

    $stmtSueldo->close();

    //=================================================
    // VERIFICAR CUENTA BANCARIA
    //=================================================

    $sqlCuenta = "
        SELECT
            id_cuenta_bancaria,
            nombre,
            balance,
            Eliminado
        FROM cuenta_banco
        WHERE id_cuenta_bancaria = ?
          AND id_user = ?
        LIMIT 1
    ";

    $stmtCuenta = $conexion->prepare($sqlCuenta);

    $stmtCuenta->bind_param(
        "ii",
        $idCuentaBancaria,
        $idUser
    );

    $stmtCuenta->execute();

    $resultadoCuenta = $stmtCuenta->get_result();

    if ($resultadoCuenta->num_rows === 0) {

        $stmtCuenta->close();

        throw new Exception(
            "La cuenta bancaria seleccionada no existe."
        );
    }

    $cuenta = $resultadoCuenta->fetch_assoc();

    $stmtCuenta->close();

    if ((int) $cuenta["Eliminado"] !== 0) {

        throw new Exception(
            "La cuenta bancaria seleccionada está eliminada."
        );
    }

    //=================================================
    // VERIFICAR MÉTODO DE PAGO
    //=================================================

    $sqlMetodo = "
        SELECT
            id_metodo_pago,
            nombre,
            Eliminado
        FROM metodo_pago
        WHERE id_metodo_pago = ?
          AND id_user = ?
        LIMIT 1
    ";

    $stmtMetodo = $conexion->prepare($sqlMetodo);

    $stmtMetodo->bind_param(
        "ii",
        $idMetodoPago,
        $idUser
    );

    $stmtMetodo->execute();

    $resultadoMetodo = $stmtMetodo->get_result();

    if ($resultadoMetodo->num_rows === 0) {

        $stmtMetodo->close();

        throw new Exception(
            "El método de pago seleccionado no existe."
        );
    }

    $metodo = $resultadoMetodo->fetch_assoc();

    $stmtMetodo->close();

    if ((int) $metodo["Eliminado"] !== 0) {

        throw new Exception(
            "El método de pago seleccionado está eliminado."
        );
    }

    //=================================================
    // VERIFICAR SOLAPAMIENTO DE PERÍODOS
    //=================================================

    /*
     * Evita que el mismo empleado tenga dos pagos
     * pendientes para períodos que se cruzan.
     */

    if ($estado === "PENDIENTE") {

        $sqlDuplicado = "
            SELECT
                id_pago
            FROM pago_empleado
            WHERE id_empleado = ?
              AND id_user = ?
              AND id_pago <> ?
              AND estado = 'PENDIENTE'
              AND periodo_inicio <= ?
              AND periodo_fin >= ?
            LIMIT 1
        ";

        $stmtDuplicado = $conexion->prepare($sqlDuplicado);

        $stmtDuplicado->bind_param(
            "iiiss",
            $idEmpleado,
            $idUser,
            $idPago,
            $periodoFin,
            $periodoInicio
        );

        $stmtDuplicado->execute();

        $resultadoDuplicado = $stmtDuplicado->get_result();

        if ($resultadoDuplicado->num_rows > 0) {

            $stmtDuplicado->close();

            throw new Exception(
                "Ya existe otro pago pendiente para el empleado dentro del período indicado."
            );
        }

        $stmtDuplicado->close();
    }

    //=================================================
    // ACTUALIZAR PAGO
    //=================================================

    $sqlActualizar = "
        UPDATE pago_empleado
        SET
            periodo_inicio = ?,
            periodo_fin = ?,
            monto_base = ?,
            bonificaciones = ?,
            descuentos = ?,
            monto_total = ?,
            fecha_pago = ?,
            id_cuenta_bancaria = ?,
            id_metodo_pago = ?,
            estado = ?,
            observacion = ?,
            fecha_actualizado = NOW()
        WHERE id_pago = ?
          AND id_user = ?
          AND estado <> 'PAGADO'
        LIMIT 1
    ";

    $stmtActualizar = $conexion->prepare($sqlActualizar);

    $stmtActualizar->bind_param(
        "ssddddsiissii",
        $periodoInicio,
        $periodoFin,
        $montoBase,
        $bonificaciones,
        $descuentos,
        $montoTotalCalculado,
        $fechaPago,
        $idCuentaBancaria,
        $idMetodoPago,
        $estado,
        $observacion,
        $idPago,
        $idUser
    );

    $stmtActualizar->execute();

    $filasActualizadas = $stmtActualizar->affected_rows;

    $stmtActualizar->close();

    //=================================================
    // VERIFICAR ACTUALIZACIÓN
    //=================================================

    /*
     * affected_rows puede ser 0 si el usuario guarda
     * exactamente los mismos datos.
     *
     * Por eso no usamos affected_rows como única
     * condición para determinar si la operación fue válida.
     */

    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    $conexion->commit();

    //=================================================
    // RESPUESTA
    //=================================================

    responderExito(
        "El pago fue actualizado correctamente.",
        [
            "id_pago" => $idPago,
            "estado" => $estado,
            "monto_base" => number_format(
                $montoBase,
                2,
                ".",
                ""
            ),
            "bonificaciones" => number_format(
                $bonificaciones,
                2,
                ".",
                ""
            ),
            "descuentos" => number_format(
                $descuentos,
                2,
                ".",
                ""
            ),
            "monto_total" => number_format(
                $montoTotalCalculado,
                2,
                ".",
                ""
            ),
            "filas_actualizadas" => $filasActualizadas
        ]
    );
} catch (Throwable $error) {

    //=================================================
    // ROLLBACK
    //=================================================

    try {
        $conexion->rollback();
    } catch (Throwable $rollbackError) {
        // No hacer nada.
    }

    //=================================================
    // REGISTRAR ERROR
    //=================================================

    error_log(
        "Error actualizar_pago_empleado.php: " .
            $error->getMessage()
    );

    //=================================================
    // RESPUESTA
    //=================================================

    responderError(
        $error->getMessage(),
        400
    );
}
