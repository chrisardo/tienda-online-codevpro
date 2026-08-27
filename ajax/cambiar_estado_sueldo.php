<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/cambiar_estado_sueldo.php
// Módulo: Sueldos y Pagos
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

mysqli_report(MYSQLI_REPORT_OFF);


//=====================================================
// RESPUESTA JSON
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
// OBTENER DATOS
//=====================================================

$idSueldo = isset($_POST['id_sueldo'])
    ? (int) $_POST['id_sueldo']
    : 0;

$nuevoEstado = isset($_POST['estado'])
    ? strtoupper(trim((string) $_POST['estado']))
    : '';


//=====================================================
// VALIDAR ID
//=====================================================

if ($idSueldo <= 0) {

    responderJSON(
        false,
        'El identificador del sueldo no es válido.',
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

if (!in_array($nuevoEstado, $estadosPermitidos, true)) {

    responderJSON(
        false,
        'El estado enviado no es válido.',
        [],
        400
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


//=====================================================
// UTF-8
//=====================================================

$conexion->set_charset('utf8mb4');


//=====================================================
// INICIAR TRANSACCIÓN
//=====================================================

$conexion->begin_transaction();


try {

    //=================================================
    // OBTENER SUELDO ACTUAL
    //=================================================

    $sql = "
        SELECT
            se.id_sueldo,
            se.id_empleado,
            se.estado,
            se.sueldo_base,
            se.tipo_pago,
            se.fecha_inicio,
            se.fecha_fin,

            e.nombre,
            e.apellido

        FROM sueldo_empleado AS se

        INNER JOIN empleados AS e
            ON e.id_empleado = se.id_empleado
            AND e.id_user = se.id_user

        WHERE
            se.id_sueldo = ?
            AND se.id_user = ?

        LIMIT 1

        FOR UPDATE
    ";


    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            'No se pudo preparar la consulta del sueldo: ' .
                $conexion->error
        );
    }


    $stmt->bind_param(
        'ii',
        $idSueldo,
        $idUser
    );


    if (!$stmt->execute()) {

        throw new Exception(
            'No se pudo consultar el sueldo: ' .
                $stmt->error
        );
    }


    //=================================================
    // RESULTADO
    //=================================================

    $stmt->store_result();


    if ($stmt->num_rows === 0) {

        $stmt->close();

        throw new Exception(
            'El sueldo no existe o no pertenece a este usuario.'
        );
    }


    //=================================================
    // VARIABLES
    //=================================================

    $idSueldoBD = 0;
    $idEmpleado = 0;
    $estadoActual = '';
    $sueldoBase = 0;
    $tipoPago = '';
    $fechaInicio = '';
    $fechaFin = null;
    $nombreEmpleado = '';
    $apellidoEmpleado = '';


    $stmt->bind_result(
        $idSueldoBD,
        $idEmpleado,
        $estadoActual,
        $sueldoBase,
        $tipoPago,
        $fechaInicio,
        $fechaFin,
        $nombreEmpleado,
        $apellidoEmpleado
    );


    $stmt->fetch();

    $stmt->close();


    //=================================================
    // VALIDAR ESTADO ACTUAL
    //=================================================

    if ($estadoActual === $nuevoEstado) {

        $conexion->commit();

        responderJSON(
            true,
            $nuevoEstado === 'ACTIVO'
                ? 'El sueldo ya se encontraba activo.'
                : 'El sueldo ya se encontraba inactivo.',
            [
                'id_sueldo' => $idSueldoBD,
                'id_empleado' => $idEmpleado,
                'estado' => $nuevoEstado
            ]
        );
    }


    //=================================================
    // SI SE QUIERE ACTIVAR
    //=================================================

    if ($nuevoEstado === 'ACTIVO') {

        /*
        |-------------------------------------------------
        | Un empleado solamente puede tener UN sueldo
        | activo.
        |
        | Si existe otro sueldo activo, lo desactivamos
        | automáticamente.
        |-------------------------------------------------
        */

        $sqlDesactivarOtros = "
            UPDATE sueldo_empleado

            SET
                estado = 'INACTIVO',
                fecha_actualizado = NOW()

            WHERE
                id_empleado = ?
                AND id_user = ?
                AND id_sueldo <> ?
                AND estado = 'ACTIVO'
        ";


        $stmtOtros = $conexion->prepare(
            $sqlDesactivarOtros
        );


        if (!$stmtOtros) {

            throw new Exception(
                'No se pudo preparar la actualización de sueldos anteriores: ' .
                    $conexion->error
            );
        }


        $stmtOtros->bind_param(
            'iii',
            $idEmpleado,
            $idUser,
            $idSueldo
        );


        if (!$stmtOtros->execute()) {

            throw new Exception(
                'No se pudieron desactivar los sueldos anteriores: ' .
                    $stmtOtros->error
            );
        }


        $sueldosDesactivados =
            $stmtOtros->affected_rows;


        $stmtOtros->close();
    }


    //=================================================
    // ACTUALIZAR SUELDO SELECCIONADO
    //=================================================

    $sqlActualizar = "
        UPDATE sueldo_empleado

        SET
            estado = ?,
            fecha_actualizado = NOW()

        WHERE
            id_sueldo = ?
            AND id_user = ?

        LIMIT 1
    ";


    $stmtActualizar = $conexion->prepare(
        $sqlActualizar
    );


    if (!$stmtActualizar) {

        throw new Exception(
            'No se pudo preparar la actualización del estado: ' .
                $conexion->error
        );
    }


    $stmtActualizar->bind_param(
        'sii',
        $nuevoEstado,
        $idSueldo,
        $idUser
    );


    if (!$stmtActualizar->execute()) {

        throw new Exception(
            'No se pudo actualizar el estado del sueldo: ' .
                $stmtActualizar->error
        );
    }


    $filasActualizadas =
        $stmtActualizar->affected_rows;


    $stmtActualizar->close();


    //=================================================
    // VALIDAR ACTUALIZACIÓN
    //=================================================

    if (
        $filasActualizadas <= 0 &&
        $estadoActual !== $nuevoEstado
    ) {

        throw new Exception(
            'No se pudo actualizar el estado del sueldo.'
        );
    }


    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    $conexion->commit();


    //=================================================
    // NOMBRE EMPLEADO
    //=================================================

    $nombreCompleto = trim(
        $nombreEmpleado . ' ' . $apellidoEmpleado
    );


    if ($nombreCompleto === '') {
        $nombreCompleto = 'Empleado';
    }


    //=================================================
    // MENSAJE
    //=================================================

    if ($nuevoEstado === 'ACTIVO') {

        $mensaje =
            'El sueldo de ' .
            $nombreCompleto .
            ' fue activado correctamente.';


        if (
            isset($sueldosDesactivados) &&
            $sueldosDesactivados > 0
        ) {

            $mensaje .=
                ' El sueldo activo anterior fue desactivado automáticamente.';
        }
    } else {

        $mensaje =
            'El sueldo de ' .
            $nombreCompleto .
            ' fue desactivado correctamente.';
    }


    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        true,
        $mensaje,
        [
            'id_sueldo' =>
            (int) $idSueldo,

            'id_empleado' =>
            (int) $idEmpleado,

            'estado_anterior' =>
            $estadoActual,

            'estado' =>
            $nuevoEstado,

            'sueldos_desactivados' =>
            isset($sueldosDesactivados)
                ? (int) $sueldosDesactivados
                : 0
        ]
    );
} catch (Throwable $e) {

    //=================================================
    // ROLLBACK
    //=================================================

    $conexion->rollback();


    //=================================================
    // LOG
    //=================================================

    error_log(
        'cambiar_estado_sueldo.php ERROR: ' .
            $e->getMessage() .
            ' | Línea: ' .
            $e->getLine() .
            ' | Archivo: ' .
            $e->getFile() .
            ' | id_user: ' .
            $idUser .
            ' | id_sueldo: ' .
            $idSueldo
    );


    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        false,
        $e->getMessage(),
        [],
        500
    );
}
