<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/listar_pagos_empleado.php
// Módulo: Pagos a Empleados
// Sistema: Inventa
//=====================================================

declare(strict_types=1);

//=====================================================
// SESIÓN
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

if (!isset($conexion) || !$conexion) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se pudo establecer la conexión con la base de datos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$conexion->set_charset("utf8mb4");

//=====================================================
// PARÁMETROS
//=====================================================

// Página
$pagina = isset($_GET['pagina'])
    ? (int) $_GET['pagina']
    : 1;

if ($pagina < 1) {
    $pagina = 1;
}

// Registros por página
$registros = isset($_GET['registros'])
    ? (int) $_GET['registros']
    : 10;

if ($registros < 1) {
    $registros = 10;
}

// Límite de seguridad
if ($registros > 100) {
    $registros = 100;
}

// Buscar
$buscar = isset($_GET['buscar'])
    ? trim((string) $_GET['buscar'])
    : '';

// Estado
$estado = isset($_GET['estado'])
    ? strtoupper(trim((string) $_GET['estado']))
    : '';

// Fecha inicio
$fechaInicio = isset($_GET['fecha_inicio'])
    ? trim((string) $_GET['fecha_inicio'])
    : '';

// Fecha fin
$fechaFin = isset($_GET['fecha_fin'])
    ? trim((string) $_GET['fecha_fin'])
    : '';

//=====================================================
// VALIDAR ESTADO
//=====================================================

$estadosPermitidos = [
    'PENDIENTE',
    'PAGADO',
    'ANULADO'
];

if ($estado !== '' && !in_array($estado, $estadosPermitidos, true)) {

    $estado = '';
}

//=====================================================
// VALIDAR FECHAS
//=====================================================

function validarFecha(string $fecha): bool
{
    if ($fecha === '') {
        return false;
    }

    $objetoFecha = DateTime::createFromFormat('Y-m-d', $fecha);

    return $objetoFecha !== false
        && $objetoFecha->format('Y-m-d') === $fecha;
}

if ($fechaInicio !== '' && !validarFecha($fechaInicio)) {
    $fechaInicio = '';
}

if ($fechaFin !== '' && !validarFecha($fechaFin)) {
    $fechaFin = '';
}

// Si la fecha inicial es posterior a la final,
// intercambiamos las fechas para evitar una consulta inválida.
if (
    $fechaInicio !== ''
    && $fechaFin !== ''
    && $fechaInicio > $fechaFin
) {

    $fechaTemporal = $fechaInicio;

    $fechaInicio = $fechaFin;

    $fechaFin = $fechaTemporal;
}

//=====================================================
// OFFSET
//=====================================================

$offset = ($pagina - 1) * $registros;

//=====================================================
// CONSTRUIR WHERE
//=====================================================

$where = [];

$parametros = [];

$tipos = '';

//=====================================================
// USUARIO
//=====================================================

$where[] = "p.id_user = ?";

$tipos .= 'i';

$parametros[] = $idUser;

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
// ESTADO
//=====================================================

if ($estado !== '') {

    $where[] = "UPPER(p.estado) = ?";

    $tipos .= 's';

    $parametros[] = $estado;
}

//=====================================================
// FECHA DESDE
//=====================================================

if ($fechaInicio !== '') {

    $where[] = "DATE(p.fecha_pago) >= ?";

    $tipos .= 's';

    $parametros[] = $fechaInicio;
}

//=====================================================
// FECHA HASTA
//=====================================================

if ($fechaFin !== '') {

    $where[] = "DATE(p.fecha_pago) <= ?";

    $tipos .= 's';

    $parametros[] = $fechaFin;
}

//=====================================================
// WHERE FINAL
//=====================================================

$whereSQL = implode("\n AND ", $where);

//=====================================================
// CONTAR REGISTROS
//=====================================================

$sqlTotal = "
    SELECT
        COUNT(*) AS total_registros

    FROM pago_empleado p

    INNER JOIN empleados e
        ON e.id_empleado = p.id_empleado

    WHERE
        $whereSQL
";

$stmtTotal = $conexion->prepare($sqlTotal);

if (!$stmtTotal) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al preparar la consulta de total de registros.',
        'error' => $conexion->error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// BIND DINÁMICO TOTAL
//=====================================================

if (!empty($parametros)) {

    $referencias = [];

    $referencias[] = $tipos;

    foreach ($parametros as $indice => $valor) {
        $referencias[] = &$parametros[$indice];
    }

    call_user_func_array(
        [$stmtTotal, 'bind_param'],
        $referencias
    );
}

//=====================================================
// EJECUTAR TOTAL
//=====================================================

if (!$stmtTotal->execute()) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No fue posible obtener el total de registros.',
        'error' => $stmtTotal->error
    ], JSON_UNESCAPED_UNICODE);

    $stmtTotal->close();

    exit;
}

$resultadoTotal = $stmtTotal->get_result();

$filaTotal = $resultadoTotal->fetch_assoc();

$totalRegistros = (int) (
    $filaTotal['total_registros'] ?? 0
);

$stmtTotal->close();

//=====================================================
// CONSULTAR PAGOS
//=====================================================

$sqlPagos = "
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

        e.nombre,

        e.apellido,

        e.dni

    FROM pago_empleado p

    INNER JOIN empleados e
        ON e.id_empleado = p.id_empleado

    WHERE
        $whereSQL

    ORDER BY
        p.fecha_pago DESC,
        p.id_pago DESC

    LIMIT ?
    OFFSET ?
";

//=====================================================
// PREPARAR
//=====================================================

$stmtPagos = $conexion->prepare($sqlPagos);

if (!$stmtPagos) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al preparar la consulta de pagos.',
        'error' => $conexion->error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// PARÁMETROS PAGINACIÓN
//=====================================================

$tiposPagos = $tipos . 'ii';

$parametrosPagos = $parametros;

$parametrosPagos[] = $registros;

$parametrosPagos[] = $offset;

//=====================================================
// BIND DINÁMICO
//=====================================================

$referenciasPagos = [];

$referenciasPagos[] = $tiposPagos;

foreach ($parametrosPagos as $indice => $valor) {

    $referenciasPagos[] = &$parametrosPagos[$indice];
}

call_user_func_array(
    [$stmtPagos, 'bind_param'],
    $referenciasPagos
);

//=====================================================
// EJECUTAR
//=====================================================

if (!$stmtPagos->execute()) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No fue posible obtener los pagos.',
        'error' => $stmtPagos->error
    ], JSON_UNESCAPED_UNICODE);

    $stmtPagos->close();

    exit;
}

//=====================================================
// RESULTADO
//=====================================================

$resultadoPagos = $stmtPagos->get_result();

$pagos = [];

while ($fila = $resultadoPagos->fetch_assoc()) {

    $pagos[] = [

        'id_pago' => (int) $fila['id_pago'],

        'id_empleado' => (int) $fila['id_empleado'],

        'id_sueldo' => (int) $fila['id_sueldo'],

        'empleado' => trim(
            ($fila['nombre'] ?? '') . ' ' .
                ($fila['apellido'] ?? '')
        ),

        'nombre' => $fila['nombre'] ?? '',

        'apellido' => $fila['apellido'] ?? '',

        'dni' => $fila['dni'] ?? '',

        'periodo_inicio' =>
        $fila['periodo_inicio'] ?? null,

        'periodo_fin' =>
        $fila['periodo_fin'] ?? null,

        'monto_base' =>
        (float) ($fila['monto_base'] ?? 0),

        'bonificaciones' =>
        (float) ($fila['bonificaciones'] ?? 0),

        'descuentos' =>
        (float) ($fila['descuentos'] ?? 0),

        'monto_total' =>
        (float) ($fila['monto_total'] ?? 0),

        'fecha_pago' =>
        $fila['fecha_pago'] ?? null,

        'id_cuenta_bancaria' =>
        isset($fila['id_cuenta_bancaria'])
            ? (int) $fila['id_cuenta_bancaria']
            : null,

        'id_metodo_pago' =>
        isset($fila['id_metodo_pago'])
            ? (int) $fila['id_metodo_pago']
            : null,

        'estado' =>
        strtoupper((string) ($fila['estado'] ?? '')),

        'observacion' =>
        $fila['observacion'] ?? ''
    ];
}

$stmtPagos->close();

//=====================================================
// GENERAR TABLA HTML
//=====================================================

$tabla = '';

if (empty($pagos)) {

    $tabla = '

        <tr>

            <td
                colspan="9"
                class="text-center py-5">

                <i
                    class="bi bi-wallet2
                           fs-1
                           text-muted">
                </i>

                <div class="mt-2 text-muted">

                    No se encontraron pagos.

                </div>

            </td>

        </tr>

    ';
} else {

    foreach ($pagos as $pago) {

        $empleado = htmlspecialchars(
            $pago['empleado'],
            ENT_QUOTES,
            'UTF-8'
        );

        $dni = htmlspecialchars(
            $pago['dni'],
            ENT_QUOTES,
            'UTF-8'
        );

        $estado = strtoupper(
            (string) $pago['estado']
        );

        //=================================================
        // BADGE
        //=================================================

        if ($estado === 'PAGADO') {

            $badgeEstado = '

                <span class="badge bg-success">

                    <i class="bi bi-check-circle me-1"></i>

                    Pagado

                </span>

            ';
        } elseif ($estado === 'PENDIENTE') {

            $badgeEstado = '

                <span
                    class="badge
                           bg-warning
                           text-dark">

                    <i class="bi bi-clock me-1"></i>

                    Pendiente

                </span>

            ';
        } elseif ($estado === 'ANULADO') {

            $badgeEstado = '

                <span class="badge bg-danger">

                    <i class="bi bi-x-circle me-1"></i>

                    Anulado

                </span>

            ';
        } else {

            $badgeEstado = '

                <span class="badge bg-secondary">

                    ' .
                htmlspecialchars(
                    $estado ?: 'DESCONOCIDO',
                    ENT_QUOTES,
                    'UTF-8'
                )
                . '

                </span>

            ';
        }

        //=================================================
        // BOTÓN PAGAR
        //=================================================

        $botonPagar = '';

        if ($estado === 'PENDIENTE') {

            $botonPagar = '

                <button
                    type="button"
                    class="btn
                           btn-sm
                           btn-outline-success"
                    title="Marcar como pagado"
                    onclick="marcarPagoPagado(' .
                (int) $pago['id_pago']
                . ')">

                    <i class="bi bi-check-circle"></i>

                </button>

            ';
        }

        //=================================================
        // FECHAS
        //=================================================

        $periodoInicio = $pago['periodo_inicio'] ?? '';

        $periodoFin = $pago['periodo_fin'] ?? '';

        $fechaPago = $pago['fecha_pago'] ?? '';

        //=================================================
        // FILA
        //=================================================

        $tabla .= '

            <tr>

                <!-- EMPLEADO -->

                <td>

                    <div class="fw-semibold">

                        ' . $empleado . '

                    </div>

                    ' .

            (
                $dni !== ''
                ?
                '<small class="text-muted">
                            DNI: ' . $dni . '
                        </small>'
                :
                ''
            )

            . '

                </td>


                <!-- PERIODO -->

                <td>

                    <small>

                        ' .
            htmlspecialchars(
                $periodoInicio,
                ENT_QUOTES,
                'UTF-8'
            )
            . '

                        <br>

                        ' .
            htmlspecialchars(
                $periodoFin,
                ENT_QUOTES,
                'UTF-8'
            )
            . '

                    </small>

                </td>


                <!-- MONTO BASE -->

                <td>

                    S/ ' .
            number_format(
                (float) $pago['monto_base'],
                2,
                '.',
                ','
            )
            . '

                </td>


                <!-- BONIFICACIONES -->

                <td class="text-success">

                    +S/ ' .
            number_format(
                (float) $pago['bonificaciones'],
                2,
                '.',
                ','
            )
            . '

                </td>


                <!-- DESCUENTOS -->

                <td class="text-danger">

                    -S/ ' .
            number_format(
                (float) $pago['descuentos'],
                2,
                '.',
                ','
            )
            . '

                </td>


                <!-- TOTAL -->

                <td class="fw-bold">

                    S/ ' .
            number_format(
                (float) $pago['monto_total'],
                2,
                '.',
                ','
            )
            . '

                </td>


                <!-- FECHA PAGO -->

                <td>

                    ' .
            htmlspecialchars(
                $fechaPago,
                ENT_QUOTES,
                'UTF-8'
            )
            . '

                </td>


                <!-- ESTADO -->

                <td>

                    ' . $badgeEstado . '

                </td>


                <!-- ACCIONES -->

                <td class="text-center">

                    <div class="btn-group">

                        <button
                            type="button"
                            class="btn
                                   btn-sm
                                   btn-outline-primary"
                            title="Ver pago"
                            onclick="verPago(' .
            (int) $pago['id_pago']
            . ')">

                            <i class="bi bi-eye"></i>

                        </button>

                        ' .
            $botonPagar
            . '

                    </div>

                </td>

            </tr>

        ';
    }
}

//=====================================================
// TOTAL PÁGINAS
//=====================================================

$totalPaginas = $registros > 0
    ? (int) ceil($totalRegistros / $registros)
    : 0;

//=====================================================
// RESPUESTA
//=====================================================

echo json_encode([

    'success' => true,

    'mensaje' => 'Pagos obtenidos correctamente.',

    'tabla' => $tabla,

    'datos' => [

        'pagos' => $pagos,

        'total_registros' => $totalRegistros,

        'pagina' => $pagina,

        'registros' => $registros,

        'total_paginas' => $totalPaginas

    ],

    'total_registros' => $totalRegistros,

    'pagina' => $pagina,

    'registros' => $registros,

    'total_paginas' => $totalPaginas

], JSON_UNESCAPED_UNICODE);

exit;
