<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_kpi_estadisticas_empleados.php
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


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!$conexion) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se pudo establecer la conexión con la base de datos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// CONFIGURAR MYSQLI
//=====================================================

mysqli_set_charset($conexion, "utf8mb4");


//=====================================================
// OBTENER FILTROS
//=====================================================

$idEmpleado = isset($_GET['id_empleado'])
    ? (int) $_GET['id_empleado']
    : 0;

$idRol = isset($_GET['id_rol'])
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
    $estado !== '' &&
    $estado !== 'ACTIVO' &&
    $estado !== 'INACTIVO'
) {

    $estado = '';
}


//=====================================================
// VALIDAR FECHAS
//=====================================================

function fechaValida($fecha)
{
    if ($fecha === '') {
        return false;
    }

    $d = DateTime::createFromFormat('Y-m-d', $fecha);

    return $d && $d->format('Y-m-d') === $fecha;
}


if (!fechaValida($fechaInicio)) {
    $fechaInicio = '';
}

if (!fechaValida($fechaFin)) {
    $fechaFin = '';
}


//=====================================================
// CORREGIR RANGO DE FECHAS
//=====================================================

if (
    $fechaInicio !== '' &&
    $fechaFin !== '' &&
    $fechaInicio > $fechaFin
) {

    $temporal = $fechaInicio;

    $fechaInicio = $fechaFin;

    $fechaFin = $temporal;
}


//=====================================================
// FILTRO DE EMPLEADO
//=====================================================

$filtroEmpleado = '';

if ($idEmpleado > 0) {

    $filtroEmpleado = "
        AND e.id_empleado = {$idEmpleado}
    ";
}


//=====================================================
// FILTRO DE ROL
//=====================================================

$filtroRol = '';

if ($idRol > 0) {

    $filtroRol = "
        AND e.id_rol = {$idRol}
    ";
}


//=====================================================
// FILTRO DE ESTADO
//=====================================================

$filtroEstado = '';

if ($estado !== '') {

    $estadoSeguro = mysqli_real_escape_string(
        $conexion,
        $estado
    );

    $filtroEstado = "
        AND e.estado = '{$estadoSeguro}'
    ";
}


//=====================================================
// FILTRO DE FECHA - EMPLEADOS
//=====================================================
//
// Se utiliza:
//
// empleados.fecha_registro
//
//=====================================================

$filtroFechaEmpleados = '';

if ($fechaInicio !== '') {

    $fechaInicioSeguro = mysqli_real_escape_string(
        $conexion,
        $fechaInicio
    );

    $filtroFechaEmpleados .= "
        AND DATE(e.fecha_registro) >= '{$fechaInicioSeguro}'
    ";
}

if ($fechaFin !== '') {

    $fechaFinSeguro = mysqli_real_escape_string(
        $conexion,
        $fechaFin
    );

    $filtroFechaEmpleados .= "
        AND DATE(e.fecha_registro) <= '{$fechaFinSeguro}'
    ";
}


//=====================================================
// FILTRO DE FECHA - VENTAS
//=====================================================
//
// Se utiliza:
//
// ticket_ventas.fecha_venta
//
//=====================================================

$filtroFechaVentas = '';

if ($fechaInicio !== '') {

    $fechaInicioSeguro = mysqli_real_escape_string(
        $conexion,
        $fechaInicio
    );

    $filtroFechaVentas .= "
        AND tv.fecha_venta >= '{$fechaInicioSeguro}'
    ";
}

if ($fechaFin !== '') {

    $fechaFinSeguro = mysqli_real_escape_string(
        $conexion,
        $fechaFin
    );

    $filtroFechaVentas .= "
        AND tv.fecha_venta <= '{$fechaFinSeguro}'
    ";
}


//=====================================================
// FILTRO DE FECHA - PAGOS
//=====================================================
//
// PAGADO:
//
//     Se utiliza pe.fecha_pago
//
// PENDIENTE:
//
//     Se utiliza DATE(pe.fecha_registro)
//
// Esto permite que los pagos pendientes sigan
// apareciendo aunque fecha_pago sea NULL.
//
//=====================================================

$filtroFechaPagos = '';

if (
    $fechaInicio !== '' &&
    $fechaFin !== ''
) {

    $fechaInicioSeguro = mysqli_real_escape_string(
        $conexion,
        $fechaInicio
    );

    $fechaFinSeguro = mysqli_real_escape_string(
        $conexion,
        $fechaFin
    );

    $filtroFechaPagos .= "
        AND (
            (
                pe.estado = 'PAGADO'
                AND pe.fecha_pago >= '{$fechaInicioSeguro}'
                AND pe.fecha_pago <= '{$fechaFinSeguro}'
            )

            OR

            (
                pe.estado = 'PENDIENTE'
                AND DATE(pe.fecha_registro) >= '{$fechaInicioSeguro}'
                AND DATE(pe.fecha_registro) <= '{$fechaFinSeguro}'
            )
        )
    ";
} elseif ($fechaInicio !== '') {

    $fechaInicioSeguro = mysqli_real_escape_string(
        $conexion,
        $fechaInicio
    );

    $filtroFechaPagos .= "
        AND (
            (
                pe.estado = 'PAGADO'
                AND pe.fecha_pago >= '{$fechaInicioSeguro}'
            )

            OR

            (
                pe.estado = 'PENDIENTE'
                AND DATE(pe.fecha_registro) >= '{$fechaInicioSeguro}'
            )
        )
    ";
} elseif ($fechaFin !== '') {

    $fechaFinSeguro = mysqli_real_escape_string(
        $conexion,
        $fechaFin
    );

    $filtroFechaPagos .= "
        AND (
            (
                pe.estado = 'PAGADO'
                AND pe.fecha_pago <= '{$fechaFinSeguro}'
            )

            OR

            (
                pe.estado = 'PENDIENTE'
                AND DATE(pe.fecha_registro) <= '{$fechaFinSeguro}'
            )
        )
    ";
}


//=====================================================
// FUNCIÓN CONSULTA SIMPLE
//=====================================================

function ejecutarConsulta($conexion, $sql)
{
    $resultado = mysqli_query(
        $conexion,
        $sql
    );

    if (!$resultado) {

        error_log(
            "Error SQL estadísticas empleados: " .
                mysqli_error($conexion)
        );

        return null;
    }

    return $resultado;
}


//=====================================================
// FUNCIÓN OBTENER VALOR
//=====================================================

function obtenerValorConsulta(
    $conexion,
    $sql,
    $tipo = 'numero'
) {

    $resultado = ejecutarConsulta(
        $conexion,
        $sql
    );

    if (!$resultado) {

        return $tipo === 'decimal'
            ? 0.00
            : 0;
    }

    $fila = mysqli_fetch_assoc($resultado);

    if (!$fila) {

        return $tipo === 'decimal'
            ? 0.00
            : 0;
    }

    $valor = array_values($fila)[0] ?? 0;

    if ($tipo === 'decimal') {

        return (float) $valor;
    }

    return (int) $valor;
}


//=====================================================
// 1. TOTAL EMPLEADOS
//=====================================================

$sqlTotalEmpleados = "
    SELECT COUNT(*) AS total

    FROM empleados e

    WHERE e.id_user = {$idUser}

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroEstado}
    {$filtroFechaEmpleados}
";

$totalEmpleados = obtenerValorConsulta(
    $conexion,
    $sqlTotalEmpleados
);


//=====================================================
// 2. EMPLEADOS ACTIVOS
//=====================================================

$sqlEmpleadosActivos = "
    SELECT COUNT(*) AS total

    FROM empleados e

    WHERE e.id_user = {$idUser}

    AND e.estado = 'ACTIVO'

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroFechaEmpleados}
";

$empleadosActivos = obtenerValorConsulta(
    $conexion,
    $sqlEmpleadosActivos
);


//=====================================================
// 3. EMPLEADOS INACTIVOS
//=====================================================

$sqlEmpleadosInactivos = "
    SELECT COUNT(*) AS total

    FROM empleados e

    WHERE e.id_user = {$idUser}

    AND e.estado = 'INACTIVO'

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroFechaEmpleados}
";

$empleadosInactivos = obtenerValorConsulta(
    $conexion,
    $sqlEmpleadosInactivos
);


//=====================================================
// 4. ROLES ASIGNADOS
//=====================================================

$sqlRoles = "
    SELECT COUNT(DISTINCT e.id_rol) AS total

    FROM empleados e

    WHERE e.id_user = {$idUser}

    AND e.id_rol IS NOT NULL

    AND e.id_rol > 0

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroEstado}
    {$filtroFechaEmpleados}
";

$rolesAsignados = obtenerValorConsulta(
    $conexion,
    $sqlRoles
);


//=====================================================
// 5. VENTAS REALIZADAS
//=====================================================
//
// Cada ticket representa una venta.
//
// Se excluyen:
//
// - CANCELADO
// - ANULADO
//
//=====================================================

$sqlVentas = "
    SELECT COUNT(*) AS total

    FROM ticket_ventas tv

    INNER JOIN empleados e
        ON e.id_empleado = tv.id_empleado

    WHERE tv.id_user = {$idUser}

    AND e.id_user = {$idUser}

    AND tv.id_empleado IS NOT NULL

    AND tv.id_empleado > 0

    AND (
        tv.estado_venta IS NULL

        OR UPPER(tv.estado_venta)
            NOT IN ('CANCELADO', 'ANULADO')
    )

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroEstado}
    {$filtroFechaVentas}
";

$ventasEmpleados = obtenerValorConsulta(
    $conexion,
    $sqlVentas
);


//=====================================================
// 6. MONTO VENDIDO
//=====================================================

$sqlMontoVentas = "
    SELECT COALESCE(
        SUM(tv.total_venta),
        0
    ) AS total

    FROM ticket_ventas tv

    INNER JOIN empleados e
        ON e.id_empleado = tv.id_empleado

    WHERE tv.id_user = {$idUser}

    AND e.id_user = {$idUser}

    AND tv.id_empleado IS NOT NULL

    AND tv.id_empleado > 0

    AND (
        tv.estado_venta IS NULL

        OR UPPER(tv.estado_venta)
            NOT IN ('CANCELADO', 'ANULADO')
    )

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroEstado}
    {$filtroFechaVentas}
";

$montoVentas = obtenerValorConsulta(
    $conexion,
    $sqlMontoVentas,
    'decimal'
);


//=====================================================
// 7. TICKET PROMEDIO
//=====================================================

$ticketPromedio = 0;

if ($ventasEmpleados > 0) {

    $ticketPromedio =
        $montoVentas / $ventasEmpleados;
}


//=====================================================
// 8. PRODUCTOS VENDIDOS
//=====================================================

$sqlProductosVendidos = "
    SELECT COALESCE(
        SUM(dtv.cantidad_pedido_producto),
        0
    ) AS total

    FROM detalle_ticket_ventas dtv

    INNER JOIN ticket_ventas tv
        ON tv.id_ticket_ventas =
           dtv.id_ticket_ventas

    INNER JOIN empleados e
        ON e.id_empleado =
           tv.id_empleado

    WHERE dtv.id_user = {$idUser}

    AND tv.id_user = {$idUser}

    AND e.id_user = {$idUser}

    AND tv.id_empleado IS NOT NULL

    AND tv.id_empleado > 0

    AND (
        tv.estado_venta IS NULL

        OR UPPER(tv.estado_venta)
            NOT IN ('CANCELADO', 'ANULADO')
    )

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroEstado}
    {$filtroFechaVentas}
";

$productosVendidos = obtenerValorConsulta(
    $conexion,
    $sqlProductosVendidos
);


//=====================================================
// 9. PAGOS REALIZADOS
//=====================================================
//
// Se consideran únicamente pagos PAGADOS.
//
// Si monto_total es NULL:
//
// monto_total = monto_base
//             + bonificaciones
//             - descuentos
//
//=====================================================

$sqlPagosRealizados = "
    SELECT COALESCE(
        SUM(
            CASE

                WHEN pe.monto_total IS NOT NULL
                THEN pe.monto_total

                ELSE
                    COALESCE(pe.monto_base, 0)

                    + COALESCE(
                        pe.bonificaciones,
                        0
                    )

                    - COALESCE(
                        pe.descuentos,
                        0
                    )

            END
        ),
        0
    ) AS total

    FROM pago_empleado pe

    INNER JOIN empleados e
        ON e.id_empleado = pe.id_empleado

    WHERE pe.id_user = {$idUser}

    AND e.id_user = {$idUser}

    AND pe.estado = 'PAGADO'

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroEstado}
    {$filtroFechaPagos}
";

$pagosRealizados = obtenerValorConsulta(
    $conexion,
    $sqlPagosRealizados,
    'decimal'
);


//=====================================================
// 10. PAGOS PENDIENTES
//=====================================================
//
// Se consideran únicamente pagos PENDIENTES.
//
// IMPORTANTE:
//
// Un pago pendiente puede tener fecha_pago NULL.
//
// Por eso el filtro de fecha utiliza:
//
//     pe.fecha_registro
//
//=====================================================

$sqlPagosPendientes = "
    SELECT COALESCE(
        SUM(
            CASE

                WHEN pe.monto_total IS NOT NULL
                THEN pe.monto_total

                ELSE
                    COALESCE(pe.monto_base, 0)

                    + COALESCE(
                        pe.bonificaciones,
                        0
                    )

                    - COALESCE(
                        pe.descuentos,
                        0
                    )

            END
        ),
        0
    ) AS total

    FROM pago_empleado pe

    INNER JOIN empleados e
        ON e.id_empleado = pe.id_empleado

    WHERE pe.id_user = {$idUser}

    AND e.id_user = {$idUser}

    AND pe.estado = 'PENDIENTE'

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroEstado}
    {$filtroFechaPagos}
";

$pagosPendientes = obtenerValorConsulta(
    $conexion,
    $sqlPagosPendientes,
    'decimal'
);


//=====================================================
// 11. BONIFICACIONES
//=====================================================
//
// Se suman las bonificaciones de pagos:
//
// - PENDIENTE
// - PAGADO
//
// Se excluyen:
//
// - ANULADO
//
//=====================================================

$sqlBonificaciones = "
    SELECT COALESCE(
        SUM(
            COALESCE(
                pe.bonificaciones,
                0
            )
        ),
        0
    ) AS total

    FROM pago_empleado pe

    INNER JOIN empleados e
        ON e.id_empleado = pe.id_empleado

    WHERE pe.id_user = {$idUser}

    AND e.id_user = {$idUser}

    AND pe.estado IN (
        'PENDIENTE',
        'PAGADO'
    )

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroEstado}
    {$filtroFechaPagos}
";

$bonificaciones = obtenerValorConsulta(
    $conexion,
    $sqlBonificaciones,
    'decimal'
);


//=====================================================
// 12. DESCUENTOS
//=====================================================
//
// Se suman los descuentos de pagos:
//
// - PENDIENTE
// - PAGADO
//
// Se excluyen:
//
// - ANULADO
//
//=====================================================

$sqlDescuentos = "
    SELECT COALESCE(
        SUM(
            COALESCE(
                pe.descuentos,
                0
            )
        ),
        0
    ) AS total

    FROM pago_empleado pe

    INNER JOIN empleados e
        ON e.id_empleado = pe.id_empleado

    WHERE pe.id_user = {$idUser}

    AND e.id_user = {$idUser}

    AND pe.estado IN (
        'PENDIENTE',
        'PAGADO'
    )

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroEstado}
    {$filtroFechaPagos}
";

$descuentos = obtenerValorConsulta(
    $conexion,
    $sqlDescuentos,
    'decimal'
);


//=====================================================
// 13. NÓMINA ACTIVA
//=====================================================
//
// Se obtiene el sueldo base activo de los empleados
// que cumplen con los filtros.
//
//=====================================================

$sqlNominaActiva = "
    SELECT COALESCE(
        SUM(se.sueldo_base),
        0
    ) AS total

    FROM sueldo_empleado se

    INNER JOIN empleados e
        ON e.id_empleado = se.id_empleado

    WHERE se.id_user = {$idUser}

    AND e.id_user = {$idUser}

    AND se.estado = 'ACTIVO'

    {$filtroEmpleado}
    {$filtroRol}
    {$filtroEstado}
";

$nominaActiva = obtenerValorConsulta(
    $conexion,
    $sqlNominaActiva,
    'decimal'
);


//=====================================================
// 14. PORCENTAJE ACTIVOS
//=====================================================

$porcentajeActivos = 0;

if ($totalEmpleados > 0) {

    $porcentajeActivos =
        (
            $empleadosActivos /
            $totalEmpleados
        ) * 100;
}


//=====================================================
// 15. PORCENTAJE INACTIVOS
//=====================================================

$porcentajeInactivos = 0;

if ($totalEmpleados > 0) {

    $porcentajeInactivos =
        (
            $empleadosInactivos /
            $totalEmpleados
        ) * 100;
}


//=====================================================
// 16. RESPUESTA JSON
//=====================================================

echo json_encode([

    'success' => true,

    'data' => [

        //=================================================
        // EMPLEADOS
        //=================================================

        'totalEmpleados' =>
        $totalEmpleados,

        'empleadosActivos' =>
        $empleadosActivos,

        'empleadosInactivos' =>
        $empleadosInactivos,

        'rolesAsignados' =>
        $rolesAsignados,


        //=================================================
        // VENTAS
        //=================================================

        'ventasEmpleados' =>
        $ventasEmpleados,

        'montoVentas' =>
        round(
            $montoVentas,
            2
        ),

        'ticketPromedio' =>
        round(
            $ticketPromedio,
            2
        ),

        'productosVendidos' =>
        $productosVendidos,


        //=================================================
        // NÓMINA
        //=================================================

        'nominaActiva' =>
        round(
            $nominaActiva,
            2
        ),


        //=================================================
        // PAGOS
        //=================================================

        'pagosRealizados' =>
        round(
            $pagosRealizados,
            2
        ),

        'pagosPendientes' =>
        round(
            $pagosPendientes,
            2
        ),


        //=================================================
        // RESUMEN ECONÓMICO
        //=================================================

        'bonificaciones' =>
        round(
            $bonificaciones,
            2
        ),

        'descuentos' =>
        round(
            $descuentos,
            2
        ),


        //=================================================
        // PORCENTAJES
        //=================================================

        'porcentajeActivos' =>
        round(
            $porcentajeActivos,
            2
        ),

        'porcentajeInactivos' =>
        round(
            $porcentajeInactivos,
            2
        )

    ]

], JSON_UNESCAPED_UNICODE);


//=====================================================
// CERRAR CONEXIÓN
//=====================================================

mysqli_close($conexion);
