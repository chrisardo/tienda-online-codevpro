<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/listar_sueldos.php
// Módulo: Sueldos y Pagos
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');


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
// MANEJO DE ERRORES
//=====================================================

mysqli_report(MYSQLI_REPORT_OFF);


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

    error_log(
        'listar_sueldos.php - Error charset: ' .
            $conexion->error
    );
}


//=====================================================
// PARÁMETROS
//=====================================================

$buscar = isset($_GET['buscar'])
    ? trim((string) $_GET['buscar'])
    : '';

$estado = isset($_GET['estado'])
    ? trim((string) $_GET['estado'])
    : '';

$tipoBase = isset($_GET['tipo_base'])
    ? trim((string) $_GET['tipo_base'])
    : '';

$pagina = isset($_GET['pagina'])
    ? (int) $_GET['pagina']
    : 1;


//=====================================================
// NORMALIZAR PAGINACIÓN
//=====================================================

if ($pagina < 1) {
    $pagina = 1;
}

$porPagina = 5;

$offset = ($pagina - 1) * $porPagina;


//=====================================================
// VALIDAR ESTADO
//=====================================================

$estadosPermitidos = [
    'ACTIVO',
    'INACTIVO'
];

if (
    $estado !== '' &&
    !in_array($estado, $estadosPermitidos, true)
) {

    $estado = '';
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

if (
    $tipoBase !== '' &&
    !in_array($tipoBase, $tiposPermitidos, true)
) {

    $tipoBase = '';
}


//=====================================================
// FUNCIONES AUXILIARES
//=====================================================

function ejecutarPrepared(
    mysqli $conexion,
    string $sql,
    string $tipos = '',
    array $parametros = []
): mysqli_stmt {

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {

        throw new Exception(
            'Error al preparar consulta: ' .
                $conexion->error
        );
    }


    if ($tipos !== '') {

        $referencias = [];

        $referencias[] = $tipos;

        foreach ($parametros as $indice => $valor) {
            $referencias[] = &$parametros[$indice];
        }

        call_user_func_array(
            [$stmt, 'bind_param'],
            $referencias
        );
    }


    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        throw new Exception(
            'Error al ejecutar consulta: ' .
                $error
        );
    }


    return $stmt;
}


//=====================================================
// CONSTRUIR CONDICIONES
//=====================================================

$where = [];

$where[] = "se.id_user = ?";

$tipos = 'i';

$parametros = [
    $idUser
];


//=====================================================
// BÚSQUEDA
//=====================================================

if ($buscar !== '') {

    $where[] = "
        (
            e.nombre LIKE ?
            OR e.apellido LIKE ?
            OR CONCAT(e.nombre, ' ', e.apellido) LIKE ?
            OR e.dni LIKE ?
        )
    ";

    $buscarLike = '%' . $buscar . '%';

    $tipos .= 'ssss';

    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
}


//=====================================================
// FILTRO ESTADO
//=====================================================

if ($estado !== '') {

    $where[] = "se.estado = ?";

    $tipos .= 's';

    $parametros[] = $estado;
}


//=====================================================
// FILTRO TIPO
//=====================================================

if ($tipoBase !== '') {

    /*
    |-------------------------------------------------
    | IMPORTANTE
    |
    | En la BD la columna REAL es:
    |
    | sueldo_empleado.tipo_pago
    |
    | El JavaScript trabaja con:
    |
    | tipo_base
    |
    | Por eso usamos tipo_pago en WHERE.
    |-------------------------------------------------
    */

    $where[] = "se.tipo_pago = ?";

    $tipos .= 's';

    $parametros[] = $tipoBase;
}


//=====================================================
// WHERE FINAL
//=====================================================

$whereSQL = implode(
    "\n AND ",
    $where
);


//=====================================================
// EJECUCIÓN PRINCIPAL
//=====================================================

try {

    //=================================================
    // CONTAR REGISTROS
    //=================================================

    $sqlCount = "
        SELECT
            COUNT(*) AS total

        FROM sueldo_empleado AS se

        INNER JOIN empleados AS e
            ON e.id_empleado = se.id_empleado
            AND e.id_user = se.id_user

        WHERE
            {$whereSQL}
    ";


    $stmtCount = ejecutarPrepared(
        $conexion,
        $sqlCount,
        $tipos,
        $parametros
    );


    //=================================================
    // OBTENER COUNT SIN get_result()
    //=================================================

    $total = 0;

    $stmtCount->bind_result($total);

    if (!$stmtCount->fetch()) {
        $total = 0;
    }

    $stmtCount->close();

    $total = (int) $total;


    //=================================================
    // CALCULAR PÁGINAS
    //=================================================

    $paginas = $total > 0
        ? (int) ceil($total / $porPagina)
        : 0;


    //=================================================
    // CORREGIR PÁGINA
    //=================================================

    if ($paginas > 0 && $pagina > $paginas) {

        $pagina = $paginas;

        $offset = ($pagina - 1) * $porPagina;
    }


    //=================================================
    // CONSULTAR SUELDOS
    //=================================================

    /*
    |-------------------------------------------------
    | IMPORTANTE
    |
    | tipo_pago -> tipo_base
    |
    | Esto hace compatible la BD con el JavaScript.
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

            {$whereSQL}


        ORDER BY

            CASE
                WHEN se.estado = 'ACTIVO' THEN 0
                ELSE 1
            END,

            se.fecha_inicio DESC,

            se.id_sueldo DESC


        LIMIT ?, ?
    ";


    //=================================================
    // AGREGAR PAGINACIÓN
    //=================================================

    $tiposConsulta = $tipos . 'ii';

    $parametrosConsulta = $parametros;

    $parametrosConsulta[] = $offset;
    $parametrosConsulta[] = $porPagina;


    //=================================================
    // PREPARAR
    //=================================================

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {

        throw new Exception(
            'No se pudo preparar la consulta de sueldos: ' .
                $conexion->error
        );
    }


    //=================================================
    // BIND
    //=================================================

    $referencias = [];

    $referencias[] = $tiposConsulta;

    foreach (
        $parametrosConsulta as $indice => $valor
    ) {

        $referencias[] = &$parametrosConsulta[$indice];
    }


    call_user_func_array(
        [$stmt, 'bind_param'],
        $referencias
    );


    //=================================================
    // EJECUTAR
    //=================================================

    if (!$stmt->execute()) {

        throw new Exception(
            'No se pudo ejecutar la consulta de sueldos: ' .
                $stmt->error
        );
    }


    //=================================================
    // RESULTADOS
    //=================================================

    /*
    |-------------------------------------------------
    | NO usamos get_result()
    |
    | Así evitamos depender de mysqlnd.
    |-------------------------------------------------
    */

    $stmt->store_result();


    //=================================================
    // VARIABLES
    //=================================================

    $idSueldo = 0;
    $idEmpleado = 0;
    $sueldoBase = 0;
    $tipoPago = '';
    $fechaInicio = null;
    $fechaFin = null;
    $estadoResultado = '';
    $observacion = '';
    $idUserResultado = 0;
    $fechaRegistro = null;
    $fechaActualizado = null;

    $nombreEmpleado = '';
    $apellidoEmpleado = '';
    $dniEmpleado = '';
    $celularEmpleado = null;
    $emailEmpleado = '';
    $idRol = null;
    $nombreRol = '';


    //=================================================
    // BIND RESULT
    //=================================================

    $stmt->bind_result(
        $idSueldo,
        $idEmpleado,
        $sueldoBase,
        $tipoPago,
        $fechaInicio,
        $fechaFin,
        $estadoResultado,
        $observacion,
        $idUserResultado,
        $fechaRegistro,
        $fechaActualizado,
        $nombreEmpleado,
        $apellidoEmpleado,
        $dniEmpleado,
        $celularEmpleado,
        $emailEmpleado,
        $idRol,
        $nombreRol
    );


    //=================================================
    // CONSTRUIR FILAS
    //=================================================

    $filas = [];

    while ($stmt->fetch()) {

        $nombreCompleto = trim(
            (string) $nombreEmpleado .
                ' ' .
                (string) $apellidoEmpleado
        );


        if ($nombreCompleto === '') {
            $nombreCompleto = 'Empleado sin nombre';
        }


        $cargo = trim(
            (string) $nombreRol
        );


        if ($cargo === '') {
            $cargo = 'Sin cargo';
        }


        $filas[] = [

            'id_sueldo' =>
            (int) $idSueldo,

            'id_empleado' =>
            (int) $idEmpleado,

            'sueldo_base' =>
            number_format(
                (float) $sueldoBase,
                2,
                '.',
                ''
            ),

            'tipo_base' =>
            (string) $tipoPago,

            'fecha_inicio' =>
            $fechaInicio !== null
                ? (string) $fechaInicio
                : '',

            'fecha_fin' =>
            $fechaFin !== null
                ? (string) $fechaFin
                : '',

            'estado' =>
            (string) $estadoResultado,

            'observacion' =>
            $observacion !== null
                ? (string) $observacion
                : '',

            'nombre_empleado' =>
            (string) $nombreEmpleado,

            'apellido_empleado' =>
            (string) $apellidoEmpleado,

            'nombre_completo' =>
            $nombreCompleto,

            'dni' =>
            (string) $dniEmpleado,

            'celular' =>
            $celularEmpleado !== null
                ? (string) $celularEmpleado
                : '',

            'email' =>
            (string) $emailEmpleado,

            'id_rol' =>
            $idRol !== null
                ? (int) $idRol
                : 0,

            'cargo' =>
            $cargo,

            'fecha_registro' =>
            $fechaRegistro,

            'fecha_actualizado' =>
            $fechaActualizado
        ];
    }


    $stmt->free_result();

    $stmt->close();


    //=================================================
    // CONSTRUIR TABLA HTML
    //=================================================

    $tabla = '';


    if (count($filas) > 0) {

        foreach ($filas as $fila) {

            //=========================================
            // ID
            //=========================================

            $id = (int) $fila['id_sueldo'];


            //=========================================
            // NOMBRE
            //=========================================

            $nombre = htmlspecialchars(
                $fila['nombre_completo'],
                ENT_QUOTES,
                'UTF-8'
            );


            //=========================================
            // DNI
            //=========================================

            $dni = htmlspecialchars(
                $fila['dni'],
                ENT_QUOTES,
                'UTF-8'
            );


            //=========================================
            // CARGO
            //=========================================

            $cargo = htmlspecialchars(
                $fila['cargo'],
                ENT_QUOTES,
                'UTF-8'
            );


            //=========================================
            // SUELDO
            //=========================================

            $sueldo = number_format(
                (float) $fila['sueldo_base'],
                2,
                '.',
                ','
            );


            //=========================================
            // TIPO
            //=========================================

            $tipo = $fila['tipo_base'];

            switch ($tipo) {

                case 'MENSUAL':
                    $tipoTexto = 'Mensual';
                    break;

                case 'QUINCENAL':
                    $tipoTexto = 'Quincenal';
                    break;

                case 'SEMANAL':
                    $tipoTexto = 'Semanal';
                    break;

                case 'DIARIO':
                    $tipoTexto = 'Diario';
                    break;

                default:
                    $tipoTexto = $tipo;
                    break;
            }


            //=========================================
            // ESTADO
            //=========================================

            if ($fila['estado'] === 'ACTIVO') {

                $estadoHTML = '
                    <span class="badge bg-success">
                        Activo
                    </span>
                ';

                $textoEstado = 'Desactivar';
            } else {

                $estadoHTML = '
                    <span class="badge bg-secondary">
                        Inactivo
                    </span>
                ';

                $textoEstado = 'Activar';
            }


            //=========================================
            // FECHA INICIO
            //=========================================

            $fechaInicioTexto = '';

            if (
                !empty($fila['fecha_inicio']) &&
                $fila['fecha_inicio'] !== '0000-00-00'
            ) {

                $fechaInicioTexto = date(
                    'd/m/Y',
                    strtotime($fila['fecha_inicio'])
                );
            }


            //=========================================
            // FECHA FIN
            //=========================================

            $fechaFinTexto = 'Vigente';

            if (
                !empty($fila['fecha_fin']) &&
                $fila['fecha_fin'] !== '0000-00-00'
            ) {

                $fechaFinTexto = date(
                    'd/m/Y',
                    strtotime($fila['fecha_fin'])
                );
            }


            //=========================================
            // OBSERVACIÓN
            //=========================================

            $observacion = htmlspecialchars(
                $fila['observacion'],
                ENT_QUOTES,
                'UTF-8'
            );


            //=========================================
            // FILA
            //=========================================

            $tabla .= '

                <tr>

                    <!-- EMPLEADO -->

                    <td>

                        <div class="d-flex align-items-center gap-3">

                            <div
                                class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                style="
                                    width:42px;
                                    height:42px;
                                    flex-shrink:0;
                                "
                            >

                                <i class="bi bi-person text-primary"></i>

                            </div>


                            <div class="min-w-0">

                                <div class="fw-semibold text-truncate">

                                    ' . $nombre . '

                                </div>

                                <small class="text-muted">

                                    DNI:
                                    ' . $dni . '

                                </small>

                            </div>

                        </div>

                    </td>


                    <!-- CARGO -->

                    <td>

                        <span class="text-muted">

                            ' . $cargo . '

                        </span>

                    </td>


                    <!-- SUELDO -->

                    <td>

                        <span class="fw-bold">

                            S/ ' . $sueldo . '

                        </span>

                    </td>


                    <!-- PERIODICIDAD -->

                    <td>

                        <span class="badge bg-light text-dark border">

                            ' . htmlspecialchars(
                $tipoTexto,
                ENT_QUOTES,
                'UTF-8'
            ) . '

                        </span>

                    </td>


                    <!-- VIGENCIA -->

                    <td>

                        <div class="small">

                            <div>

                                <i class="bi bi-calendar-event me-1"></i>

                                ' . htmlspecialchars(
                $fechaInicioTexto,
                ENT_QUOTES,
                'UTF-8'
            ) . '

                            </div>


                            <div class="text-muted">

                                <i class="bi bi-calendar-x me-1"></i>

                                ' . htmlspecialchars(
                $fechaFinTexto,
                ENT_QUOTES,
                'UTF-8'
            ) . '

                            </div>

                        </div>

                    </td>


                    <!-- ESTADO -->

                    <td>

                        ' . $estadoHTML . '

                    </td>


                    <!-- ACCIONES -->

                    <td class="text-end">

                        <div class="dropdown">

                            <button
                                type="button"
                                class="btn btn-sm btn-light border"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >

                                <i class="bi bi-three-dots-vertical"></i>

                            </button>


                            <ul class="dropdown-menu dropdown-menu-end">


                                <li>

                                    <button
                                        type="button"
                                        class="dropdown-item"
                                        onclick="editarSueldo(' . $id . ')"
                                    >

                                        <i class="bi bi-pencil me-2"></i>

                                        Editar

                                    </button>

                                </li>


                                <li>

                                    <button
                                        type="button"
                                        class="dropdown-item"
                                        onclick="cambiarEstadoSueldo(
                                            ' . $id . ',
                                            \'' .
                htmlspecialchars(
                    $fila['estado'],
                    ENT_QUOTES,
                    'UTF-8'
                ) .
                '\'
                                        )"
                                    >

                                        <i class="bi bi-arrow-repeat me-2"></i>

                                        ' . $textoEstado . '

                                    </button>

                                </li>


                                <li>

                                    <hr class="dropdown-divider">

                                </li>


                            </ul>

                        </div>

                    </td>

                </tr>

            ';
        }
    } else {

        $tabla = '
            <tr>

                <td
                    colspan="7"
                    class="text-center text-muted py-5"
                >

                    <i
                        class="bi bi-inbox fs-2 d-block mb-2"
                    ></i>

                    No se encontraron registros de sueldos.

                </td>

            </tr>
        ';
    }


    //=================================================
    // KPI
    //=================================================

    //=================================================
    // TOTAL EMPLEADOS
    //=================================================

    $sqlEmpleados = "
        SELECT COUNT(*)
        FROM empleados
        WHERE
            id_user = ?
            AND estado = 'ACTIVO'
    ";

    $stmtEmpleados = $conexion->prepare($sqlEmpleados);

    if (!$stmtEmpleados) {
        throw new Exception(
            'No se pudo consultar empleados.'
        );
    }

    $stmtEmpleados->bind_param(
        'i',
        $idUser
    );

    if (!$stmtEmpleados->execute()) {
        throw new Exception(
            'No se pudo ejecutar consulta de empleados.'
        );
    }

    $stmtEmpleados->bind_result($totalEmpleados);

    if (!$stmtEmpleados->fetch()) {
        $totalEmpleados = 0;
    }

    $stmtEmpleados->close();


    //=================================================
    // SUELDOS ACTIVOS
    //=================================================

    $sqlActivos = "
        SELECT COUNT(*)
        FROM sueldo_empleado
        WHERE
            id_user = ?
            AND estado = 'ACTIVO'
    ";

    $stmtActivos = $conexion->prepare($sqlActivos);

    if (!$stmtActivos) {
        throw new Exception(
            'No se pudo consultar sueldos activos.'
        );
    }

    $stmtActivos->bind_param(
        'i',
        $idUser
    );

    if (!$stmtActivos->execute()) {
        throw new Exception(
            'No se pudo ejecutar consulta de sueldos activos.'
        );
    }

    $stmtActivos->bind_result($sueldosActivos);

    if (!$stmtActivos->fetch()) {
        $sueldosActivos = 0;
    }

    $stmtActivos->close();


    //=================================================
    // EMPLEADOS SIN SUELDO ACTIVO
    //=================================================

    $sqlSinSueldo = "
        SELECT COUNT(*)

        FROM empleados AS e

        LEFT JOIN sueldo_empleado AS se

            ON se.id_empleado = e.id_empleado

            AND se.id_user = e.id_user

            AND se.estado = 'ACTIVO'

        WHERE

            e.id_user = ?

            AND e.estado = 'ACTIVO'

            AND se.id_sueldo IS NULL
    ";


    $stmtSinSueldo = $conexion->prepare(
        $sqlSinSueldo
    );

    if (!$stmtSinSueldo) {
        throw new Exception(
            'No se pudo consultar empleados sin sueldo.'
        );
    }


    $stmtSinSueldo->bind_param(
        'i',
        $idUser
    );


    if (!$stmtSinSueldo->execute()) {
        throw new Exception(
            'No se pudo ejecutar consulta de empleados sin sueldo.'
        );
    }


    $stmtSinSueldo->bind_result($sinSueldo);


    if (!$stmtSinSueldo->fetch()) {
        $sinSueldo = 0;
    }


    $stmtSinSueldo->close();


    //=================================================
    // NÓMINA MENSUAL
    //=================================================

    /*
    |-------------------------------------------------
    | Para la nómina mensual solamente tomamos
    | sueldos ACTIVOS.
    |
    | MENSUAL:
    | sueldo_base
    |
    | QUINCENAL:
    | sueldo_base * 2
    |
    | SEMANAL:
    | sueldo_base * 52 / 12
    |
    | DIARIO:
    | sueldo_base * 30
    |-------------------------------------------------
    */

    $sqlNomina = "
        SELECT
            COALESCE(
                SUM(
                    CASE

                        WHEN tipo_pago = 'MENSUAL'
                            THEN sueldo_base

                        WHEN tipo_pago = 'QUINCENAL'
                            THEN sueldo_base * 2

                        WHEN tipo_pago = 'SEMANAL'
                            THEN sueldo_base * 52 / 12

                        WHEN tipo_pago = 'DIARIO'
                            THEN sueldo_base * 30

                        ELSE 0

                    END
                ),
                0
            )

        FROM sueldo_empleado

        WHERE
            id_user = ?

            AND estado = 'ACTIVO'
    ";


    $stmtNomina = $conexion->prepare(
        $sqlNomina
    );


    if (!$stmtNomina) {
        throw new Exception(
            'No se pudo consultar la nómina mensual.'
        );
    }


    $stmtNomina->bind_param(
        'i',
        $idUser
    );


    if (!$stmtNomina->execute()) {
        throw new Exception(
            'No se pudo ejecutar consulta de nómina mensual.'
        );
    }


    $stmtNomina->bind_result($nominaMensual);


    if (!$stmtNomina->fetch()) {
        $nominaMensual = 0;
    }


    $stmtNomina->close();


    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        true,
        'Sueldos obtenidos correctamente.',
        [

            'tabla' => $tabla,

            'sueldos' => $filas,

            'kpi' => [

                'empleados' =>
                (int) $totalEmpleados,

                'sueldos_activos' =>
                (int) $sueldosActivos,

                'sin_sueldo' =>
                (int) $sinSueldo,

                'nomina_mensual' =>
                round(
                    (float) $nominaMensual,
                    2
                )
            ],

            'paginacion' => [

                'pagina' =>
                (int) $pagina,

                'por_pagina' =>
                (int) $porPagina,

                'total' =>
                (int) $total,

                'paginas' =>
                (int) $paginas
            ]
        ]
    );
} catch (Throwable $e) {

    //=================================================
    // REGISTRAR ERROR REAL
    //=================================================

    error_log(
        'listar_sueldos.php ERROR: ' .
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
        'No se pudo cargar la lista de sueldos.',
        [
            'error_tecnico' =>
            $e->getMessage()
        ],
        500
    );
}
