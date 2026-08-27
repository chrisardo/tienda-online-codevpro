<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_cuentas_bancarias.php
// Módulo: Cuentas Bancarias
// Sistema: Inventa
//=====================================================

//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CABECERA JSON
//=====================================================

header('Content-Type: application/json; charset=utf-8');

//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function respuestaJSON($datos)
{
    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR USUARIO
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ]);
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible conectar con la base de datos.'
    ]);
}

//=====================================================
// OBTENER PARÁMETROS
//=====================================================

// Página
$pagina = isset($_GET['pagina'])
    ? (int) $_GET['pagina']
    : 1;

if ($pagina < 1) {
    $pagina = 1;
}

// Registros por página
$registrosPorPagina = isset($_GET['registrosPorPagina'])
    ? (int) $_GET['registrosPorPagina']
    : 5;

if ($registrosPorPagina < 1) {
    $registrosPorPagina = 5;
}

// Limitar por seguridad
if ($registrosPorPagina > 100) {
    $registrosPorPagina = 100;
}

// Buscar
$buscar = isset($_GET['buscar'])
    ? trim($_GET['buscar'])
    : '';

// Estado
$estado = isset($_GET['estado'])
    ? trim($_GET['estado'])
    : '';

// Orden
$orden = isset($_GET['orden'])
    ? trim($_GET['orden'])
    : 'nombre_asc';

//=====================================================
// NORMALIZAR ESTADO
//=====================================================
//
// "" = Todos
// 0  = Activas
// 1  = Inactivas
//

if ($estado !== '0' && $estado !== '1') {
    $estado = '';
}

//=====================================================
// NORMALIZAR ORDEN
//=====================================================

$ordenesPermitidos = [

    'nombre_asc' => 'nombre ASC',

    'nombre_desc' => 'nombre DESC',

    'balance_desc' => 'balance DESC, nombre ASC',

    'balance_asc' => 'balance ASC, nombre ASC'

];

if (!isset($ordenesPermitidos[$orden])) {
    $orden = 'nombre_asc';
}

$ordenSQL = $ordenesPermitidos[$orden];

//=====================================================
// CONSTRUIR WHERE
//=====================================================

$where = [];

$parametros = [];

$tipos = 'i';

//=====================================================
// FILTRO USUARIO
//=====================================================

$where[] = 'id_user = ?';

$parametros[] = $idUser;

//=====================================================
// FILTRO BÚSQUEDA
//=====================================================

if ($buscar !== '') {

    $where[] = 'nombre LIKE ?';

    $parametros[] = '%' . $buscar . '%';

    $tipos .= 's';
}

//=====================================================
// FILTRO ESTADO
//=====================================================

if ($estado !== '') {

    $where[] = 'Eliminado = ?';

    $parametros[] = (int) $estado;

    $tipos .= 'i';
}

//=====================================================
// WHERE FINAL
//=====================================================

$whereSQL = implode(' AND ', $where);

//=====================================================
// CONTAR REGISTROS
//=====================================================

$sqlTotal = "
    SELECT COUNT(*) AS total
    FROM cuenta_banco
    WHERE {$whereSQL}
";

//=====================================================
// PREPARAR TOTAL
//=====================================================

$stmtTotal = mysqli_prepare($conexion, $sqlTotal);

if (!$stmtTotal) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible preparar el conteo de cuentas.',
        'error' => mysqli_error($conexion)
    ]);
}

//=====================================================
// VINCULAR PARÁMETROS TOTAL
//=====================================================

mysqli_stmt_bind_param(
    $stmtTotal,
    $tipos,
    ...$parametros
);

//=====================================================
// EJECUTAR TOTAL
//=====================================================

if (!mysqli_stmt_execute($stmtTotal)) {

    $error = mysqli_stmt_error($stmtTotal);

    mysqli_stmt_close($stmtTotal);

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible obtener el total de cuentas.',
        'error' => $error
    ]);
}

//=====================================================
// RESULTADO TOTAL
//=====================================================

$resultadoTotal = mysqli_stmt_get_result($stmtTotal);

if (!$resultadoTotal) {

    $error = mysqli_stmt_error($stmtTotal);

    mysqli_stmt_close($stmtTotal);

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible obtener el resultado del conteo.',
        'error' => $error
    ]);
}

$filaTotal = mysqli_fetch_assoc($resultadoTotal);

$totalRegistros = isset($filaTotal['total'])
    ? (int) $filaTotal['total']
    : 0;

mysqli_free_result($resultadoTotal);

mysqli_stmt_close($stmtTotal);

//=====================================================
// CALCULAR PAGINACIÓN
//=====================================================

$totalPaginas = $totalRegistros > 0
    ? (int) ceil($totalRegistros / $registrosPorPagina)
    : 1;

//=====================================================
// ASEGURAR PÁGINA VÁLIDA
//=====================================================

if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
}

//=====================================================
// CALCULAR OFFSET
//=====================================================

$offset = ($pagina - 1) * $registrosPorPagina;

//=====================================================
// CONSULTAR CUENTAS
//=====================================================

$sql = "
    SELECT
        id_cuenta_bancaria,
        nombre,
        balance,
        Eliminado
    FROM cuenta_banco
    WHERE {$whereSQL}
    ORDER BY {$ordenSQL}
    LIMIT ? OFFSET ?
";

//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible preparar la consulta de cuentas.',
        'error' => mysqli_error($conexion)
    ]);
}

//=====================================================
// PARÁMETROS PARA CONSULTA
//=====================================================

$parametrosConsulta = $parametros;

$parametrosConsulta[] = $registrosPorPagina;

$parametrosConsulta[] = $offset;

$tiposConsulta = $tipos . 'ii';

//=====================================================
// VINCULAR PARÁMETROS
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    $tiposConsulta,
    ...$parametrosConsulta
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    $error = mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible consultar las cuentas bancarias.',
        'error' => $error
    ]);
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado) {

    $error = mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible obtener las cuentas bancarias.',
        'error' => $error
    ]);
}

//=====================================================
// CONSTRUIR ARRAY
//=====================================================

$cuentas = [];

while ($fila = mysqli_fetch_assoc($resultado)) {

    $cuentas[] = [

        'id_cuenta_bancaria' => (int) $fila['id_cuenta_bancaria'],

        'nombre' => $fila['nombre'],

        'balance' => (float) $fila['balance'],

        'Eliminado' => (int) $fila['Eliminado']

    ];
}

//=====================================================
// CERRAR
//=====================================================

mysqli_free_result($resultado);

mysqli_stmt_close($stmt);

//=====================================================
// RESPUESTA
//=====================================================

respuestaJSON([

    'success' => true,

    'mensaje' => 'Cuentas bancarias obtenidas correctamente.',

    'cuentas' => $cuentas,

    'total_registros' => $totalRegistros,

    'total_paginas' => $totalPaginas,

    'pagina_actual' => $pagina,

    'registros_por_pagina' => $registrosPorPagina,

    'filtros' => [

        'buscar' => $buscar,

        'estado' => $estado,

        'orden' => $orden

    ]

]);
