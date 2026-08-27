<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_graficos_estadisticas_empleados.php
// Módulo: Estadísticas de Empleados
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
        'mensaje' => 'Sesión no válida.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

if (!isset($conexion) || !$conexion) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se pudo establecer la conexión con la base de datos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

mysqli_set_charset($conexion, 'utf8mb4');

//=====================================================
// PARÁMETROS
//=====================================================

$idEmpleado = isset($_GET['id_empleado']) &&
    $_GET['id_empleado'] !== ''
    ? (int) $_GET['id_empleado']
    : 0;

$idRol = isset($_GET['id_rol']) &&
    $_GET['id_rol'] !== ''
    ? (int) $_GET['id_rol']
    : 0;

$estado = isset($_GET['estado'])
    ? strtoupper(trim($_GET['estado']))
    : '';

$fechaInicio = isset($_GET['fecha_inicio'])
    ? trim($_GET['fecha_inicio'])
    : '';

$fechaFin = isset($_GET['fecha_fin'])
    ? trim($_GET['fecha_fin'])
    : '';

//=====================================================
// VALIDAR ESTADO
//=====================================================

if (
    $estado !== 'ACTIVO' &&
    $estado !== 'INACTIVO'
) {
    $estado = '';
}

//=====================================================
// VALIDAR FECHAS
//=====================================================

function fechaValidaEstadisticas($fecha)
{
    if ($fecha === '') {
        return false;
    }

    $objeto = DateTime::createFromFormat(
        'Y-m-d',
        $fecha
    );

    return $objeto &&
        $objeto->format('Y-m-d') === $fecha;
}

if (
    $fechaInicio !== '' &&
    !fechaValidaEstadisticas($fechaInicio)
) {
    $fechaInicio = '';
}

if (
    $fechaFin !== '' &&
    !fechaValidaEstadisticas($fechaFin)
) {
    $fechaFin = '';
}

//=====================================================
// SI SOLO VIENE UNA FECHA
//=====================================================

if (
    $fechaInicio !== '' &&
    $fechaFin === ''
) {
    $fechaFin = $fechaInicio;
}

if (
    $fechaFin !== '' &&
    $fechaInicio === ''
) {
    $fechaInicio = $fechaFin;
}

//=====================================================
// FUNCIÓN EJECUTAR CONSULTA
//=====================================================

function ejecutarConsulta(
    $conexion,
    $sql,
    $tipos = '',
    $parametros = []
) {

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    if (!$stmt) {

        throw new Exception(
            'Error preparando consulta: ' .
                mysqli_error($conexion)
        );
    }

    if (
        $tipos !== '' &&
        !empty($parametros)
    ) {

        mysqli_stmt_bind_param(
            $stmt,
            $tipos,
            ...$parametros
        );
    }

    if (
        !mysqli_stmt_execute($stmt)
    ) {

        $error = mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        throw new Exception(
            'Error ejecutando consulta: ' .
                $error
        );
    }

    $resultado = mysqli_stmt_get_result(
        $stmt
    );

    if (!$resultado) {

        $error = mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        throw new Exception(
            'Error obteniendo resultado: ' .
                $error
        );
    }

    $datos = [];

    while (
        $fila = mysqli_fetch_assoc($resultado)
    ) {
        $datos[] = $fila;
    }

    mysqli_free_result($resultado);

    mysqli_stmt_close($stmt);

    return $datos;
}

//=====================================================
// FILTROS DE EMPLEADOS
//=====================================================

$filtrosEmpleado = "
    e.id_user = ?
";

$parametrosEmpleado = [
    $idUser
];

$tiposEmpleado = "i";

//=====================================================
// FILTRO EMPLEADO
//=====================================================

if ($idEmpleado > 0) {

    $filtrosEmpleado .= "
        AND e.id_empleado = ?
    ";

    $tiposEmpleado .= "i";

    $parametrosEmpleado[] =
        $idEmpleado;
}

//=====================================================
// FILTRO ROL
//=====================================================

if ($idRol > 0) {

    $filtrosEmpleado .= "
        AND e.id_rol = ?
    ";

    $tiposEmpleado .= "i";

    $parametrosEmpleado[] =
        $idRol;
}

//=====================================================
// FILTRO ESTADO
//=====================================================

if ($estado !== '') {

    $filtrosEmpleado .= "
        AND UPPER(e.estado) = ?
    ";

    $tiposEmpleado .= "s";

    $parametrosEmpleado[] =
        $estado;
}

//=====================================================
// FILTROS DE VENTAS
//=====================================================

$filtrosVenta = "
    tv.id_user = ?
    AND tv.id_empleado IS NOT NULL
    AND tv.id_empleado > 0
";

$parametrosVenta = [
    $idUser
];

$tiposVenta = "i";

//=====================================================
// FILTRO EMPLEADO EN VENTAS
//=====================================================

if ($idEmpleado > 0) {

    $filtrosVenta .= "
        AND tv.id_empleado = ?
    ";

    $tiposVenta .= "i";

    $parametrosVenta[] =
        $idEmpleado;
}

//=====================================================
// FECHA INICIO VENTAS
//=====================================================

if ($fechaInicio !== '') {

    $filtrosVenta .= "
        AND tv.fecha_venta >= ?
    ";

    $tiposVenta .= "s";

    $parametrosVenta[] =
        $fechaInicio;
}

//=====================================================
// FECHA FIN VENTAS
//=====================================================

if ($fechaFin !== '') {

    $filtrosVenta .= "
        AND tv.fecha_venta <= ?
    ";

    $tiposVenta .= "s";

    $parametrosVenta[] =
        $fechaFin;
}

//=====================================================
// EXCLUIR VENTAS CANCELADAS
//=====================================================

$filtrosVenta .= "
    AND (
        tv.estado_venta IS NULL
        OR UPPER(tv.estado_venta) NOT LIKE '%CANCEL%'
    )
";

//=====================================================
// DATOS
//=====================================================

$datos = [

    'rendimiento' => [],

    'estado' => [],

    'evolucionVentas' => [],

    'roles' => [],

    'pagos' => [],

    'ranking' => []

];

try {

    //=================================================
    // 1. RENDIMIENTO POR EMPLEADO
    //=================================================

    $sqlRendimiento = "

        SELECT

            e.id_empleado,

            CONCAT(
                COALESCE(e.nombre, ''),
                ' ',
                COALESCE(e.apellido, '')
            ) AS empleado,

            COALESCE(
                r.nombre,
                'Sin rol'
            ) AS rol,

            COUNT(
                DISTINCT tv.id_ticket_ventas
            ) AS ventas,

            COALESCE(
                (
                    SELECT
                        SUM(
                            dtv2.cantidad_pedido_producto
                        )

                    FROM detalle_ticket_ventas dtv2

                    INNER JOIN ticket_ventas tv2
                        ON tv2.id_ticket_ventas =
                           dtv2.id_ticket_ventas

                    WHERE
                        tv2.id_empleado =
                        e.id_empleado

                        AND tv2.id_user =
                        e.id_user

                        AND dtv2.id_user =
                        e.id_user

                        AND (
                            tv2.estado_venta IS NULL
                            OR UPPER(tv2.estado_venta)
                               NOT LIKE '%CANCEL%'
                        )

                        " .
        (
            $fechaInicio !== ''
            ? " AND tv2.fecha_venta >= ? "
            : ""
        ) .
        (
            $fechaFin !== ''
            ? " AND tv2.fecha_venta <= ? "
            : ""
        ) .
        "
                ),
                0
            ) AS productos,

            COALESCE(
                SUM(
                    DISTINCT tv.total_venta
                ),
                0
            ) AS monto

        FROM empleados e

        LEFT JOIN rol r
            ON r.id_rol = e.id_rol
            AND r.id_user = e.id_user

        LEFT JOIN ticket_ventas tv
            ON tv.id_empleado =
               e.id_empleado

            AND tv.id_user =
                e.id_user

            AND (
                tv.estado_venta IS NULL
                OR UPPER(tv.estado_venta)
                   NOT LIKE '%CANCEL%'
            )

            " .
        (
            $fechaInicio !== ''
            ? " AND tv.fecha_venta >= ? "
            : ""
        ) .
        (
            $fechaFin !== ''
            ? " AND tv.fecha_venta <= ? "
            : ""
        ) .
        "

        WHERE
            $filtrosEmpleado

        GROUP BY
            e.id_empleado,
            e.nombre,
            e.apellido,
            r.nombre

        ORDER BY
            monto DESC,
            ventas DESC,
            empleado ASC

    ";

    $tiposRendimiento = '';

    $parametrosRendimiento = [];

    // Parámetros del JOIN tv
    if ($fechaInicio !== '') {

        $tiposRendimiento .= 's';

        $parametrosRendimiento[] =
            $fechaInicio;
    }

    if ($fechaFin !== '') {

        $tiposRendimiento .= 's';

        $parametrosRendimiento[] =
            $fechaFin;
    }

    // Parámetros del subquery
    if ($fechaInicio !== '') {

        $tiposRendimiento .= 's';

        $parametrosRendimiento[] =
            $fechaInicio;
    }

    if ($fechaFin !== '') {

        $tiposRendimiento .= 's';

        $parametrosRendimiento[] =
            $fechaFin;
    }

    // Parámetros empleados
    $tiposRendimiento .=
        $tiposEmpleado;

    foreach (
        $parametrosEmpleado
        as $parametro
    ) {

        $parametrosRendimiento[] =
            $parametro;
    }

    $datos['rendimiento'] =
        ejecutarConsulta(
            $conexion,
            $sqlRendimiento,
            $tiposRendimiento,
            $parametrosRendimiento
        );


    //=================================================
    // 2. ESTADO DE EMPLEADOS
    //=================================================

    $sqlEstado = "

        SELECT

            UPPER(
                CASE
                    WHEN e.estado IS NULL
                        OR e.estado = ''
                    THEN 'INACTIVO'
                    ELSE e.estado
                END
            ) AS estado,

            COUNT(*) AS cantidad

        FROM empleados e

        WHERE
            $filtrosEmpleado

        GROUP BY
            UPPER(
                CASE
                    WHEN e.estado IS NULL
                        OR e.estado = ''
                    THEN 'INACTIVO'
                    ELSE e.estado
                END
            )

        ORDER BY
            estado ASC

    ";

    $datos['estado'] =
        ejecutarConsulta(
            $conexion,
            $sqlEstado,
            $tiposEmpleado,
            $parametrosEmpleado
        );


    //=================================================
    // 3. EVOLUCIÓN DE VENTAS
    //=================================================

    $sqlEvolucion = "

        SELECT

            tv.fecha_venta AS fecha,

            COALESCE(
                SUM(tv.total_venta),
                0
            ) AS monto

        FROM ticket_ventas tv

        INNER JOIN empleados e
            ON e.id_empleado =
               tv.id_empleado

            AND e.id_user =
                tv.id_user

        WHERE
            $filtrosVenta

    ";

    //=================================================
    // APLICAR ROL Y ESTADO A VENTAS
    //=================================================

    if ($idRol > 0) {

        $sqlEvolucion .= "
            AND e.id_rol = ?
        ";

        $tiposVenta .= "i";

        $parametrosVenta[] =
            $idRol;
    }

    if ($estado !== '') {

        $sqlEvolucion .= "
            AND UPPER(e.estado) = ?
        ";

        $tiposVenta .= "s";

        $parametrosVenta[] =
            $estado;
    }

    $sqlEvolucion .= "

        GROUP BY
            tv.fecha_venta

        ORDER BY
            tv.fecha_venta ASC

    ";

    $datos['evolucionVentas'] =
        ejecutarConsulta(
            $conexion,
            $sqlEvolucion,
            $tiposVenta,
            $parametrosVenta
        );


    //=================================================
    // 4. EMPLEADOS POR ROL
    //=================================================

    $sqlRoles = "

        SELECT

            COALESCE(
                r.nombre,
                'Sin rol'
            ) AS rol,

            COUNT(*) AS cantidad

        FROM empleados e

        LEFT JOIN rol r
            ON r.id_rol =
               e.id_rol

            AND r.id_user =
                e.id_user

        WHERE
            $filtrosEmpleado

        GROUP BY
            r.id_rol,
            r.nombre

        ORDER BY
            cantidad DESC,
            rol ASC

    ";

    $datos['roles'] =
        ejecutarConsulta(
            $conexion,
            $sqlRoles,
            $tiposEmpleado,
            $parametrosEmpleado
        );


    //=================================================
    // 5. PAGOS A EMPLEADOS
    //=================================================
    //
    // IMPORTANTE:
    //
    // Se relaciona pago_empleado con empleados.
    //
    // Esto permite que funcionen:
    //
    // - id_empleado
    // - id_rol
    // - estado
    // - fecha_inicio
    // - fecha_fin
    //
    // Además solamente se consideran pagos PAGADOS.
    //
    //=================================================

    $sqlPagos = "

        SELECT

            DATE_FORMAT(
                pe.fecha_pago,
                '%Y-%m'
            ) AS periodo,

            COALESCE(
                SUM(pe.monto_total),
                0
            ) AS monto

        FROM pago_empleado pe

        INNER JOIN empleados e
            ON e.id_empleado =
               pe.id_empleado

            AND e.id_user =
                pe.id_user

        WHERE

            pe.id_user = ?

            AND pe.estado = 'PAGADO'

    ";

    $tiposPagos = "i";

    $parametrosPagos = [
        $idUser
    ];

    //=================================================
    // FILTRO EMPLEADO
    //=================================================

    if ($idEmpleado > 0) {

        $sqlPagos .= "
            AND pe.id_empleado = ?
        ";

        $tiposPagos .= "i";

        $parametrosPagos[] =
            $idEmpleado;
    }

    //=================================================
    // FILTRO ROL
    //=================================================

    if ($idRol > 0) {

        $sqlPagos .= "
            AND e.id_rol = ?
        ";

        $tiposPagos .= "i";

        $parametrosPagos[] =
            $idRol;
    }

    //=================================================
    // FILTRO ESTADO EMPLEADO
    //=================================================

    if ($estado !== '') {

        $sqlPagos .= "
            AND UPPER(e.estado) = ?
        ";

        $tiposPagos .= "s";

        $parametrosPagos[] =
            $estado;
    }

    //=================================================
    // FECHA INICIO
    //=================================================

    if ($fechaInicio !== '') {

        $sqlPagos .= "
            AND pe.fecha_pago >= ?
        ";

        $tiposPagos .= "s";

        $parametrosPagos[] =
            $fechaInicio;
    }

    //=================================================
    // FECHA FIN
    //=================================================

    if ($fechaFin !== '') {

        $sqlPagos .= "
            AND pe.fecha_pago <= ?
        ";

        $tiposPagos .= "s";

        $parametrosPagos[] =
            $fechaFin;
    }

    //=================================================
    // AGRUPACIÓN MENSUAL
    //=================================================

    $sqlPagos .= "

        GROUP BY

            YEAR(pe.fecha_pago),

            MONTH(pe.fecha_pago),

            DATE_FORMAT(
                pe.fecha_pago,
                '%Y-%m'
            )

        ORDER BY

            YEAR(pe.fecha_pago) ASC,

            MONTH(pe.fecha_pago) ASC

    ";

    $datos['pagos'] =
        ejecutarConsulta(
            $conexion,
            $sqlPagos,
            $tiposPagos,
            $parametrosPagos
        );


    //=================================================
    // 6. RANKING DE EMPLEADOS
    //=================================================

    $sqlRanking = "

        SELECT

            e.id_empleado,

            CONCAT(
                COALESCE(e.nombre, ''),
                ' ',
                COALESCE(e.apellido, '')
            ) AS empleado,

            COALESCE(
                r.nombre,
                'Sin rol'
            ) AS rol,

            COUNT(
                DISTINCT tv.id_ticket_ventas
            ) AS ventas,

            COALESCE(
                (
                    SELECT
                        SUM(
                            dtv2.cantidad_pedido_producto
                        )

                    FROM detalle_ticket_ventas dtv2

                    INNER JOIN ticket_ventas tv2
                        ON tv2.id_ticket_ventas =
                           dtv2.id_ticket_ventas

                    WHERE
                        tv2.id_empleado =
                        e.id_empleado

                        AND tv2.id_user =
                        e.id_user

                        AND dtv2.id_user =
                        e.id_user

                        AND (
                            tv2.estado_venta IS NULL
                            OR UPPER(tv2.estado_venta)
                               NOT LIKE '%CANCEL%'
                        )

                        " .
        (
            $fechaInicio !== ''
            ? " AND tv2.fecha_venta >= ? "
            : ""
        ) .
        (
            $fechaFin !== ''
            ? " AND tv2.fecha_venta <= ? "
            : ""
        ) .
        "
                ),
                0
            ) AS productos,

            COALESCE(
                SUM(
                    DISTINCT tv.total_venta
                ),
                0
            ) AS monto

        FROM empleados e

        LEFT JOIN rol r
            ON r.id_rol =
               e.id_rol

            AND r.id_user =
                e.id_user

        LEFT JOIN ticket_ventas tv
            ON tv.id_empleado =
               e.id_empleado

            AND tv.id_user =
                e.id_user

            AND (
                tv.estado_venta IS NULL
                OR UPPER(tv.estado_venta)
                   NOT LIKE '%CANCEL%'
            )

            " .
        (
            $fechaInicio !== ''
            ? " AND tv.fecha_venta >= ? "
            : ""
        ) .
        (
            $fechaFin !== ''
            ? " AND tv.fecha_venta <= ? "
            : ""
        ) .
        "

        WHERE
            $filtrosEmpleado

        GROUP BY

            e.id_empleado,
            e.nombre,
            e.apellido,
            r.nombre

        HAVING
            monto > 0

        ORDER BY

            monto DESC,
            ventas DESC,
            productos DESC

        LIMIT 50

    ";

    $tiposRanking = '';

    $parametrosRanking = [];

    // Parámetros JOIN ventas
    if ($fechaInicio !== '') {

        $tiposRanking .= "s";

        $parametrosRanking[] =
            $fechaInicio;
    }

    if ($fechaFin !== '') {

        $tiposRanking .= "s";

        $parametrosRanking[] =
            $fechaFin;
    }

    // Parámetros subquery
    if ($fechaInicio !== '') {

        $tiposRanking .= "s";

        $parametrosRanking[] =
            $fechaInicio;
    }

    if ($fechaFin !== '') {

        $tiposRanking .= "s";

        $parametrosRanking[] =
            $fechaFin;
    }

    $tiposRanking .=
        $tiposEmpleado;

    foreach (
        $parametrosEmpleado
        as $parametro
    ) {

        $parametrosRanking[] =
            $parametro;
    }

    $ranking =
        ejecutarConsulta(
            $conexion,
            $sqlRanking,
            $tiposRanking,
            $parametrosRanking
        );

    //=================================================
    // TOTAL RANKING
    //=================================================

    $totalMontoRanking = 0;

    foreach (
        $ranking as $fila
    ) {

        $totalMontoRanking +=
            (float) (
                $fila['monto'] ?? 0
            );
    }

    //=================================================
    // PARTICIPACIÓN
    //=================================================

    foreach (
        $ranking as &$fila
    ) {

        $monto =
            (float) (
                $fila['monto'] ?? 0
            );

        if (
            $totalMontoRanking > 0
        ) {

            $fila['participacion'] =
                (
                    $monto /
                    $totalMontoRanking
                ) * 100;
        } else {

            $fila['participacion'] = 0;
        }
    }

    unset($fila);

    $datos['ranking'] =
        $ranking;


    //=================================================
    // NORMALIZAR RENDIMIENTO
    //=================================================

    foreach (
        $datos['rendimiento'] as &$fila
    ) {

        $fila['id_empleado'] =
            (int) (
                $fila['id_empleado'] ?? 0
            );

        $fila['ventas'] =
            (int) (
                $fila['ventas'] ?? 0
            );

        $fila['productos'] =
            (int) (
                $fila['productos'] ?? 0
            );

        $fila['monto'] =
            (float) (
                $fila['monto'] ?? 0
            );
    }

    unset($fila);


    //=================================================
    // NORMALIZAR ESTADO
    //=================================================

    foreach (
        $datos['estado'] as &$fila
    ) {

        $fila['cantidad'] =
            (int) (
                $fila['cantidad'] ?? 0
            );
    }

    unset($fila);


    //=================================================
    // NORMALIZAR EVOLUCIÓN
    //=================================================

    foreach (
        $datos['evolucionVentas'] as &$fila
    ) {

        $fila['monto'] =
            (float) (
                $fila['monto'] ?? 0
            );
    }

    unset($fila);


    //=================================================
    // NORMALIZAR ROLES
    //=================================================

    foreach (
        $datos['roles'] as &$fila
    ) {

        $fila['cantidad'] =
            (int) (
                $fila['cantidad'] ?? 0
            );
    }

    unset($fila);


    //=================================================
    // NORMALIZAR PAGOS
    //=================================================

    foreach (
        $datos['pagos'] as &$fila
    ) {

        $fila['monto'] =
            (float) (
                $fila['monto'] ?? 0
            );

        $fila['periodo'] =
            (string) (
                $fila['periodo'] ?? ''
            );
    }

    unset($fila);


    //=================================================
    // NORMALIZAR RANKING
    //=================================================

    foreach (
        $datos['ranking'] as &$fila
    ) {

        $fila['ventas'] =
            (int) (
                $fila['ventas'] ?? 0
            );

        $fila['productos'] =
            (int) (
                $fila['productos'] ?? 0
            );

        $fila['monto'] =
            (float) (
                $fila['monto'] ?? 0
            );

        $fila['participacion'] =
            (float) (
                $fila['participacion'] ?? 0
            );
    }

    unset($fila);


    //=================================================
    // RESPUESTA
    //=================================================

    echo json_encode(
        [
            'success' => true,

            'mensaje' =>
            'Gráficos obtenidos correctamente.',

            'data' => $datos
        ],

        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_NUMERIC_CHECK
    );
} catch (Throwable $e) {

    //=================================================
    // REGISTRAR ERROR
    //=================================================

    error_log(
        'obtener_graficos_estadisticas_empleados.php: ' .
            $e->getMessage()
    );

    //=================================================
    // RESPUESTA ERROR
    //=================================================

    echo json_encode(
        [
            'success' => false,

            'mensaje' =>
            'Ocurrió un error al obtener las estadísticas de empleados.',

            'error' =>
            $e->getMessage()
        ],

        JSON_UNESCAPED_UNICODE
    );
}
