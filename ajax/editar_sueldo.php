<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/editar_sueldo.php
// Módulo: Sueldos y Pagos
// Sistema: Inventa
// Función: Editar sueldo existente
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');


//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON(
    bool $success,
    string $mensaje,
    array $datos = [],
    int $codigoHTTP = 200
): void {

    http_response_code($codigoHTTP);

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'mensaje' => $mensaje
            ],
            $datos
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {

    responderJSON(
        false,
        'La sesión no es válida. Inicie sesión nuevamente.',
        [],
        401
    );
}


//=====================================================
// MÉTODO
//=====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    responderJSON(
        false,
        'Método de solicitud no permitido.',
        [],
        405
    );
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


if (
    !isset($conexion) ||
    !($conexion instanceof mysqli)
) {

    responderJSON(
        false,
        'No se pudo establecer la conexión con la base de datos.',
        [],
        500
    );
}


if (!$conexion->set_charset('utf8mb4')) {

    responderJSON(
        false,
        'No se pudo configurar la conexión con la base de datos.',
        [],
        500
    );
}


mysqli_report(
    MYSQLI_REPORT_ERROR |
        MYSQLI_REPORT_STRICT
);


//=====================================================
// RECIBIR DATOS
//=====================================================

$idSueldo = isset($_POST['id_sueldo'])
    ? (int) $_POST['id_sueldo']
    : 0;

$idEmpleado = isset($_POST['id_empleado'])
    ? (int) $_POST['id_empleado']
    : 0;

$sueldoBase = isset($_POST['sueldo_base'])
    ? (float) $_POST['sueldo_base']
    : 0;

$tipoPago = isset($_POST['tipo_base'])
    ? strtoupper(trim($_POST['tipo_base']))
    : '';

$fechaInicio = isset($_POST['fecha_inicio'])
    ? trim($_POST['fecha_inicio'])
    : '';

$fechaFin = isset($_POST['fecha_fin'])
    ? trim($_POST['fecha_fin'])
    : '';

$estado = isset($_POST['estado'])
    ? strtoupper(trim($_POST['estado']))
    : '';

$observacion = isset($_POST['observacion'])
    ? trim($_POST['observacion'])
    : '';


//=====================================================
// VALIDAR ID SUELDO
//=====================================================

if ($idSueldo <= 0) {

    responderJSON(
        false,
        'El sueldo seleccionado no es válido.',
        [],
        400
    );
}


//=====================================================
// VALIDAR EMPLEADO
//=====================================================

if ($idEmpleado <= 0) {

    responderJSON(
        false,
        'Debe seleccionar un empleado.',
        [],
        400
    );
}


//=====================================================
// VALIDAR SUELDO
//=====================================================

if (
    !isset($_POST['sueldo_base']) ||
    !is_numeric($_POST['sueldo_base'])
) {

    responderJSON(
        false,
        'El sueldo base no es válido.',
        [],
        400
    );
}


if ($sueldoBase < 0) {

    responderJSON(
        false,
        'El sueldo base no puede ser negativo.',
        [],
        400
    );
}


//=====================================================
// VALIDAR TIPO
//=====================================================

$tiposPermitidos = [
    'MENSUAL',
    'QUINCENAL',
    'SEMANAL',
    'DIARIO'
];

if (!in_array($tipoPago, $tiposPermitidos, true)) {

    responderJSON(
        false,
        'La periodicidad seleccionada no es válida.',
        [],
        400
    );
}


//=====================================================
// VALIDAR ESTADO
//=====================================================

$estadosPermitidos = [
    'ACTIVO',
    'INACTIVO'
];

if (!in_array($estado, $estadosPermitidos, true)) {

    responderJSON(
        false,
        'El estado seleccionado no es válido.',
        [],
        400
    );
}


//=====================================================
// FUNCIÓN FECHA
//=====================================================

function fechaValida(string $fecha): bool
{
    $objetoFecha = DateTime::createFromFormat(
        'Y-m-d',
        $fecha
    );

    return
        $objetoFecha !== false &&
        $objetoFecha->format('Y-m-d') === $fecha;
}


//=====================================================
// FECHA INICIO
//=====================================================

if ($fechaInicio === '') {

    responderJSON(
        false,
        'La fecha de inicio es obligatoria.',
        [],
        400
    );
}


if (!fechaValida($fechaInicio)) {

    responderJSON(
        false,
        'La fecha de inicio no es válida.',
        [],
        400
    );
}


//=====================================================
// FECHA FIN
//=====================================================

if ($fechaFin !== '') {

    if (!fechaValida($fechaFin)) {

        responderJSON(
            false,
            'La fecha de fin no es válida.',
            [],
            400
        );
    }


    if ($fechaFin < $fechaInicio) {

        responderJSON(
            false,
            'La fecha de fin no puede ser anterior a la fecha de inicio.',
            [],
            400
        );
    }
}


//=====================================================
// OBSERVACIÓN
//=====================================================

if (mb_strlen($observacion) > 255) {

    responderJSON(
        false,
        'La observación no puede superar los 255 caracteres.',
        [],
        400
    );
}


//=====================================================
// TRANSACCIÓN
//=====================================================

try {

    $conexion->begin_transaction();


    //=================================================
    // VERIFICAR SUELDO
    //=================================================

    $sqlSueldo = "
        SELECT
            id_sueldo,
            id_empleado
        FROM sueldo_empleado
        WHERE
            id_sueldo = ?
            AND id_user = ?
        LIMIT 1
    ";

    $stmtSueldo = $conexion->prepare(
        $sqlSueldo
    );

    $stmtSueldo->bind_param(
        'ii',
        $idSueldo,
        $idUser
    );

    $stmtSueldo->execute();

    $resultadoSueldo = $stmtSueldo->get_result();

    $sueldoExistente = $resultadoSueldo->fetch_assoc();

    $stmtSueldo->close();


    if (!$sueldoExistente) {

        throw new Exception(
            'El sueldo seleccionado no existe o no pertenece a esta cuenta.'
        );
    }


    //=================================================
    // VERIFICAR EMPLEADO
    //=================================================

    $sqlEmpleado = "
        SELECT
            id_empleado,
            nombre,
            apellido,
            estado
        FROM empleados
        WHERE
            id_empleado = ?
            AND id_user = ?
        LIMIT 1
    ";

    $stmtEmpleado = $conexion->prepare(
        $sqlEmpleado
    );

    $stmtEmpleado->bind_param(
        'ii',
        $idEmpleado,
        $idUser
    );

    $stmtEmpleado->execute();

    $resultadoEmpleado = $stmtEmpleado->get_result();

    $empleado = $resultadoEmpleado->fetch_assoc();

    $stmtEmpleado->close();


    if (!$empleado) {

        throw new Exception(
            'El empleado seleccionado no existe o no pertenece a esta cuenta.'
        );
    }


    //=================================================
    // ACTUALIZAR SUELDO
    //=================================================

    $sqlActualizar = "
    UPDATE sueldo_empleado
    SET
        id_empleado = ?,
        sueldo_base = ?,
        tipo_pago = ?,
        fecha_inicio = ?,
        fecha_fin = NULLIF(?, ''),
        estado = ?,
        observacion = ?,
        fecha_actualizado = NOW()
    WHERE
        id_sueldo = ?
        AND id_user = ?
";

    $stmtActualizar = $conexion->prepare(
        $sqlActualizar
    );

    $stmtActualizar->bind_param(
        'idsssssii',
        $idEmpleado,
        $sueldoBase,
        $tipoPago,
        $fechaInicio,
        $fechaFin,
        $estado,
        $observacion,
        $idSueldo,
        $idUser
    );

    $stmtActualizar->execute();

    $stmtActualizar->close();


    //=================================================
    // SI QUEDA ACTIVO
    // DESACTIVAR LOS DEMÁS
    //=================================================

    if ($estado === 'ACTIVO') {

        $sqlDesactivar = "
            UPDATE sueldo_empleado
            SET
                estado = 'INACTIVO',
                fecha_actualizado = NOW()
            WHERE
                id_empleado = ?
                AND id_user = ?
                AND estado = 'ACTIVO'
                AND id_sueldo <> ?
        ";

        $stmtDesactivar = $conexion->prepare(
            $sqlDesactivar
        );

        $stmtDesactivar->bind_param(
            'iii',
            $idEmpleado,
            $idUser,
            $idSueldo
        );

        $stmtDesactivar->execute();

        $stmtDesactivar->close();
    }


    //=================================================
    // CONFIRMAR
    //=================================================

    $conexion->commit();


    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        true,
        'El sueldo fue actualizado correctamente.',
        [
            'id_sueldo' => $idSueldo,
            'id_empleado' => $idEmpleado
        ]
    );
} catch (Throwable $e) {

    try {

        if ($conexion->in_transaction) {
            $conexion->rollback();
        }
    } catch (Throwable $rollbackError) {

        error_log(
            'editar_sueldo.php rollback: ' .
                $rollbackError->getMessage()
        );
    }


    error_log(
        'editar_sueldo.php: ' .
            $e->getMessage() .
            ' | Archivo: ' .
            $e->getFile() .
            ' | Línea: ' .
            $e->getLine()
    );


    responderJSON(
        false,
        'No se pudo actualizar el sueldo. Revise los datos e intente nuevamente.',
        [],
        500
    );
}
