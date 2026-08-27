<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/guardar_sueldo.php
// Módulo: Sueldos y Pagos
// Sistema: Inventa
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
// VALIDAR SESIÓN
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
// VALIDAR MÉTODO
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


//=====================================================
// VERIFICAR CONEXIÓN
//=====================================================

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


//=====================================================
// CONFIGURAR UTF-8
//=====================================================

if (!$conexion->set_charset('utf8mb4')) {

    responderJSON(
        false,
        'No se pudo configurar la conexión con la base de datos.',
        [],
        500
    );
}


//=====================================================
// ACTIVAR EXCEPCIONES MYSQLI
//=====================================================

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


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


//=====================================================
// TIPO DE PAGO
//
// El JS actualmente envía:
// tipo_base
//
// La tabla realmente tiene:
// tipo_pago
//=====================================================

$tipoPago = isset($_POST['tipo_base'])
    ? strtoupper(trim($_POST['tipo_base']))
    : '';


//=====================================================
// FECHAS
//=====================================================

$fechaInicio = isset($_POST['fecha_inicio'])
    ? trim($_POST['fecha_inicio'])
    : '';

$fechaFin = isset($_POST['fecha_fin'])
    ? trim($_POST['fecha_fin'])
    : '';


//=====================================================
// ESTADO
//=====================================================

$estado = isset($_POST['estado'])
    ? strtoupper(trim($_POST['estado']))
    : 'ACTIVO';


//=====================================================
// OBSERVACIÓN
//=====================================================

$observacion = isset($_POST['observacion'])
    ? trim($_POST['observacion'])
    : '';


//=====================================================
// VALIDAR ID EMPLEADO
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

if (!is_numeric($_POST['sueldo_base'] ?? null)) {

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
// VALIDAR TIPO DE PAGO
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
// VALIDAR FECHA INICIO
//=====================================================

if ($fechaInicio === '') {

    responderJSON(
        false,
        'La fecha de inicio es obligatoria.',
        [],
        400
    );
}


//=====================================================
// VALIDAR FECHAS
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


if (!fechaValida($fechaInicio)) {

    responderJSON(
        false,
        'La fecha de inicio no es válida.',
        [],
        400
    );
}


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
// VALIDAR OBSERVACIÓN
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
// INICIAR TRANSACCIÓN
//=====================================================

try {

    $conexion->begin_transaction();


    //=================================================
    // VERIFICAR QUE EL EMPLEADO PERTENEZCA AL USUARIO
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

    $stmtEmpleado = $conexion->prepare($sqlEmpleado);

    $stmtEmpleado->bind_param(
        'ii',
        $idEmpleado,
        $idUser
    );

    $stmtEmpleado->execute();

    $resultadoEmpleado = $stmtEmpleado->get_result();

    $empleado = $resultadoEmpleado->fetch_assoc();

    $stmtEmpleado->close();


    //=================================================
    // EMPLEADO NO EXISTE
    //=================================================

    if (!$empleado) {

        throw new Exception(
            'El empleado seleccionado no existe o no pertenece a esta cuenta.'
        );
    }


    //=================================================
    // VALIDAR EMPLEADO ACTIVO
    //
    // Solo se exige que exista.
    // Esto permite editar un sueldo de un empleado
    // que posteriormente fue desactivado.
    //=================================================

    //=================================================
    // SI ES EDICIÓN
    //=================================================

    if ($idSueldo > 0) {


        //=============================================
        // VERIFICAR QUE EL SUELDO EXISTA Y PERTENEZCA
        // AL USUARIO
        //=============================================

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

        $stmtSueldo = $conexion->prepare($sqlSueldo);

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


        //=============================================
        // ACTUALIZAR SUELDO
        //=============================================

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
            'idssssii',
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


        //=============================================
        // SI EL SUELDO ACTUALIZADO ES ACTIVO,
        // DESACTIVAR OTROS SUELDOS ACTIVOS DEL MISMO
        // EMPLEADO
        //=============================================

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


        //=============================================
        // CONFIRMAR
        //=============================================

        $conexion->commit();


        responderJSON(
            true,
            'El sueldo fue actualizado correctamente.',
            [
                'id_sueldo' => $idSueldo,
                'id_empleado' => $idEmpleado
            ]
        );
    }


    //=================================================
    // NUEVO SUELDO
    //=================================================

    //=================================================
    // SI SE VA A CREAR ACTIVO,
    // DESACTIVAR EL ACTUAL ANTES DE INSERTAR
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
        ";

        $stmtDesactivar = $conexion->prepare(
            $sqlDesactivar
        );

        $stmtDesactivar->bind_param(
            'ii',
            $idEmpleado,
            $idUser
        );

        $stmtDesactivar->execute();

        $stmtDesactivar->close();
    }


    //=================================================
    // INSERTAR NUEVO SUELDO
    //=================================================

    $sqlInsertar = "
        INSERT INTO sueldo_empleado
        (
            id_empleado,
            sueldo_base,
            tipo_pago,
            fecha_inicio,
            fecha_fin,
            estado,
            observacion,
            id_user,
            fecha_registro,
            fecha_actualizado
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            NULLIF(?, ''),
            ?,
            ?,
            ?,
            NOW(),
            NOW()
        )
    ";


    $stmtInsertar = $conexion->prepare(
        $sqlInsertar
    );


    $stmtInsertar->bind_param(
        'idsssssi',
        $idEmpleado,
        $sueldoBase,
        $tipoPago,
        $fechaInicio,
        $fechaFin,
        $estado,
        $observacion,
        $idUser
    );


    $stmtInsertar->execute();


    //=================================================
    // OBTENER ID GENERADO
    //=================================================

    $nuevoIdSueldo = $conexion->insert_id;


    $stmtInsertar->close();


    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    $conexion->commit();


    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        true,
        'El sueldo fue asignado correctamente.',
        [
            'id_sueldo' => (int) $nuevoIdSueldo,
            'id_empleado' => $idEmpleado
        ]
    );
} catch (Throwable $e) {


    //=================================================
    // ROLLBACK
    //=================================================

    try {

        if ($conexion->errno === 0 || $conexion->in_transaction) {
            $conexion->rollback();
        }
    } catch (Throwable $rollbackError) {

        error_log(
            'guardar_sueldo.php rollback: ' .
                $rollbackError->getMessage()
        );
    }


    //=================================================
    // REGISTRAR ERROR REAL
    //=================================================

    error_log(
        'guardar_sueldo.php: ' .
            $e->getMessage() .
            ' | Archivo: ' .
            $e->getFile() .
            ' | Línea: ' .
            $e->getLine()
    );


    //=================================================
    // RESPUESTA CONTROLADA
    //=================================================

    responderJSON(
        false,
        'No se pudo guardar el sueldo. Revise los datos e intente nuevamente.',
        [],
        500
    );
}
