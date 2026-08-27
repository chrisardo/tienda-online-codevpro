<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_sueldo.php
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
// VALIDAR ID SUELDO
//=====================================================

$idSueldo = isset($_GET['id_sueldo'])
    ? (int) $_GET['id_sueldo']
    : 0;


if ($idSueldo <= 0) {

    responderJSON(
        false,
        'El identificador del sueldo no es válido.',
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

if (!$conexion->set_charset('utf8mb4')) {

    responderJSON(
        false,
        'No se pudo configurar la conexión con la base de datos.',
        [],
        500
    );
}


//=====================================================
// CONSULTAR SUELDO
//=====================================================

try {

    /*
    |-------------------------------------------------
    | IMPORTANTE
    |
    | La columna REAL de la tabla sueldo_empleado es:
    |
    |     tipo_pago
    |
    | El JavaScript utiliza:
    |
    |     tipo_base
    |
    | Por eso hacemos:
    |
    |     se.tipo_pago AS tipo_base
    |-------------------------------------------------
    */

    $sql = "

        SELECT

            se.id_sueldo,

            se.id_empleado,

            se.sueldo_base,

            se.tipo_pago AS tipo_base,

            se.fecha_inicio,

            se.fecha_fin,

            se.estado,

            se.observacion,

            se.id_user,

            se.fecha_registro,

            se.fecha_actualizado,


            e.nombre AS nombre_empleado,

            e.apellido AS apellido_empleado,

            e.dni AS dni_empleado,

            e.celular AS celular_empleado,

            e.email AS email_empleado,

            e.imagen AS imagen_empleado,

            e.id_rol,


            r.nombre AS nombre_rol


        FROM sueldo_empleado AS se


        INNER JOIN empleados AS e

            ON e.id_empleado = se.id_empleado

            AND e.id_user = se.id_user


        LEFT JOIN rol AS r

            ON r.id_rol = e.id_rol

            AND r.id_user = se.id_user


        WHERE

            se.id_sueldo = ?

            AND se.id_user = ?


        LIMIT 1
    ";


    //=================================================
    // PREPARAR
    //=================================================

    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            'No se pudo preparar la consulta del sueldo: ' .
                $conexion->error
        );
    }


    //=================================================
    // PARÁMETROS
    //=================================================

    $stmt->bind_param(
        'ii',
        $idSueldo,
        $idUser
    );


    //=================================================
    // EJECUTAR
    //=================================================

    if (!$stmt->execute()) {

        throw new Exception(
            'No se pudo ejecutar la consulta del sueldo: ' .
                $stmt->error
        );
    }


    //=================================================
    // RESULTADO
    //=================================================

    $resultado = $stmt->get_result();


    if (!$resultado) {

        throw new Exception(
            'No se pudo obtener el resultado de la consulta.'
        );
    }


    $sueldo = $resultado->fetch_assoc();


    $stmt->close();


    //=================================================
    // VALIDAR EXISTENCIA
    //=================================================

    if (!$sueldo) {

        responderJSON(
            false,
            'El sueldo no existe o no pertenece a esta cuenta.',
            [],
            404
        );
    }


    //=================================================
    // IMAGEN DEL EMPLEADO
    //=================================================

    $imagenEmpleado = '';


    if (
        isset($sueldo['imagen_empleado']) &&
        $sueldo['imagen_empleado'] !== null &&
        $sueldo['imagen_empleado'] !== ''
    ) {

        $imagenBinaria = $sueldo['imagen_empleado'];


        //=============================================
        // DETECTAR MIME
        //=============================================

        $mime = 'image/jpeg';


        if (function_exists('finfo_open')) {

            $finfo = finfo_open(
                FILEINFO_MIME_TYPE
            );


            if ($finfo) {

                $mimeDetectado = finfo_buffer(
                    $finfo,
                    $imagenBinaria
                );


                if (
                    is_string($mimeDetectado) &&
                    strpos($mimeDetectado, 'image/') === 0
                ) {

                    $mime = $mimeDetectado;
                }


                finfo_close($finfo);
            }
        }


        //=============================================
        // DATA URI
        //=============================================

        $imagenEmpleado =
            'data:' .
            $mime .
            ';base64,' .
            base64_encode($imagenBinaria);
    }


    //=================================================
    // ELIMINAR BINARIO DEL ARRAY
    //=================================================

    unset(
        $sueldo['imagen_empleado']
    );


    //=================================================
    // NOMBRE COMPLETO
    //=================================================

    $nombreCompleto = trim(
        (string) ($sueldo['nombre_empleado'] ?? '') .
            ' ' .
            (string) ($sueldo['apellido_empleado'] ?? '')
    );


    if ($nombreCompleto === '') {

        $nombreCompleto = 'Empleado sin nombre';
    }


    //=================================================
    // CARGO
    //=================================================

    $cargoEmpleado = trim(
        (string) ($sueldo['nombre_rol'] ?? '')
    );


    if ($cargoEmpleado === '') {

        $cargoEmpleado = 'Sin cargo';
    }


    //=================================================
    // TIPO DE PAGO
    //=================================================

    $tipoBase = strtoupper(
        trim(
            (string) ($sueldo['tipo_base'] ?? '')
        )
    );


    //=================================================
    // FORMATEAR SUELDO
    //=================================================

    $sueldoBase = number_format(
        (float) ($sueldo['sueldo_base'] ?? 0),
        2,
        '.',
        ''
    );


    //=================================================
    // CONSTRUIR RESPUESTA
    //=================================================

    $datosSueldo = [

        'id_sueldo' =>
        (int) $sueldo['id_sueldo'],


        'id_empleado' =>
        (int) $sueldo['id_empleado'],


        'sueldo_base' =>
        $sueldoBase,


        /*
        |---------------------------------------------
        | El JS espera tipo_base.
        | La BD utiliza tipo_pago.
        |---------------------------------------------
        */

        'tipo_base' =>
        $tipoBase,


        'fecha_inicio' =>
        !empty($sueldo['fecha_inicio'])
            ? (string) $sueldo['fecha_inicio']
            : '',


        'fecha_fin' =>
        !empty($sueldo['fecha_fin'])
            ? (string) $sueldo['fecha_fin']
            : '',


        'estado' =>
        strtoupper(
            trim(
                (string) ($sueldo['estado'] ?? '')
            )
        ),


        'observacion' =>
        $sueldo['observacion'] !== null
            ? (string) $sueldo['observacion']
            : '',


        'fecha_registro' =>
        $sueldo['fecha_registro'] ?? null,


        'fecha_actualizado' =>
        $sueldo['fecha_actualizado'] ?? null,


        //=================================================
        // INFORMACIÓN EMPLEADO
        //=================================================

        'empleado' => [

            'id_empleado' =>
            (int) $sueldo['id_empleado'],


            'nombre' =>
            $nombreCompleto,


            'nombre_simple' =>
            (string) (
                $sueldo['nombre_empleado'] ?? ''
            ),


            'apellido' =>
            (string) (
                $sueldo['apellido_empleado'] ?? ''
            ),


            'dni' =>
            (string) (
                $sueldo['dni_empleado'] ?? ''
            ),


            'celular' =>
            $sueldo['celular_empleado'] !== null
                ? (string) $sueldo['celular_empleado']
                : '',


            'email' =>
            (string) (
                $sueldo['email_empleado'] ?? ''
            ),


            'cargo' =>
            $cargoEmpleado,


            'imagen' =>
            $imagenEmpleado
        ]
    ];


    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        true,
        'Sueldo obtenido correctamente.',
        [
            'sueldo' => $datosSueldo
        ]
    );
} catch (Throwable $e) {

    //=================================================
    // REGISTRAR ERROR
    //=================================================

    error_log(
        'obtener_sueldo.php ERROR: ' .
            $e->getMessage() .
            ' | Línea: ' .
            $e->getLine() .
            ' | Archivo: ' .
            $e->getFile()
    );


    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        false,
        'No se pudo obtener la información del sueldo.',
        [
            'error_tecnico' =>
            $e->getMessage()
        ],
        500
    );
}
