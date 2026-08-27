<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_kpi_proveedores.php
// Módulo: Lista de Proveedores
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
// FUNCIÓN RESPUESTA ERROR
//=====================================================

function responderError($mensaje)
{
    echo json_encode([
        'success' => false,
        'message' => $mensaje
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;


if ($idUser <= 0) {

    responderError('La sesión no es válida o ha expirado.');
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    responderError(
        'No se pudo establecer la conexión con la base de datos.'
    );
}


//=====================================================
// VARIABLES KPI
//=====================================================

$totalProveedores     = 0;
$proveedoresActivos   = 0;
$proveedoresInactivos = 0;
$proveedoresProductos = 0;


//=====================================================
// 1. TOTAL DE PROVEEDORES
//
// Tabla real:
// provedores
//
// Se cuentan todos los proveedores pertenecientes
// al usuario actual.
//=====================================================

$sqlTotal = "
    SELECT COUNT(*) AS total
    FROM provedores
    WHERE id_user = ?
";


$stmtTotal = mysqli_prepare(
    $conexion,
    $sqlTotal
);


if (!$stmtTotal) {

    responderError(
        'Error al preparar la consulta del total de proveedores: '
            . mysqli_error($conexion)
    );
}


mysqli_stmt_bind_param(
    $stmtTotal,
    "i",
    $idUser
);


if (!mysqli_stmt_execute($stmtTotal)) {

    $error = mysqli_stmt_error($stmtTotal);

    mysqli_stmt_close($stmtTotal);

    responderError(
        'Error al obtener el total de proveedores: ' . $error
    );
}


$resultadoTotal = mysqli_stmt_get_result($stmtTotal);


if (!$resultadoTotal) {

    $error = mysqli_stmt_error($stmtTotal);

    mysqli_stmt_close($stmtTotal);

    responderError(
        'No se pudo obtener el resultado del total de proveedores: '
            . $error
    );
}


$filaTotal = mysqli_fetch_assoc($resultadoTotal);


$totalProveedores = (int) (
    $filaTotal['total'] ?? 0
);


mysqli_stmt_close($stmtTotal);


//=====================================================
// 2. PROVEEDORES ACTIVOS
//
// Eliminado = 0
//=====================================================

$sqlActivos = "
    SELECT COUNT(*) AS total
    FROM provedores
    WHERE id_user = ?
      AND Eliminado = 0
";


$stmtActivos = mysqli_prepare(
    $conexion,
    $sqlActivos
);


if (!$stmtActivos) {

    responderError(
        'Error al preparar la consulta de proveedores activos: '
            . mysqli_error($conexion)
    );
}


mysqli_stmt_bind_param(
    $stmtActivos,
    "i",
    $idUser
);


if (!mysqli_stmt_execute($stmtActivos)) {

    $error = mysqli_stmt_error($stmtActivos);

    mysqli_stmt_close($stmtActivos);

    responderError(
        'Error al obtener los proveedores activos: ' . $error
    );
}


$resultadoActivos = mysqli_stmt_get_result($stmtActivos);


if (!$resultadoActivos) {

    $error = mysqli_stmt_error($stmtActivos);

    mysqli_stmt_close($stmtActivos);

    responderError(
        'No se pudo obtener el resultado de proveedores activos: '
            . $error
    );
}


$filaActivos = mysqli_fetch_assoc($resultadoActivos);


$proveedoresActivos = (int) (
    $filaActivos['total'] ?? 0
);


mysqli_stmt_close($stmtActivos);


//=====================================================
// 3. PROVEEDORES INACTIVOS
//
// Eliminado = 1
//=====================================================

$sqlInactivos = "
    SELECT COUNT(*) AS total
    FROM provedores
    WHERE id_user = ?
      AND Eliminado = 1
";


$stmtInactivos = mysqli_prepare(
    $conexion,
    $sqlInactivos
);


if (!$stmtInactivos) {

    responderError(
        'Error al preparar la consulta de proveedores inactivos: '
            . mysqli_error($conexion)
    );
}


mysqli_stmt_bind_param(
    $stmtInactivos,
    "i",
    $idUser
);


if (!mysqli_stmt_execute($stmtInactivos)) {

    $error = mysqli_stmt_error($stmtInactivos);

    mysqli_stmt_close($stmtInactivos);

    responderError(
        'Error al obtener los proveedores inactivos: ' . $error
    );
}


$resultadoInactivos = mysqli_stmt_get_result($stmtInactivos);


if (!$resultadoInactivos) {

    $error = mysqli_stmt_error($stmtInactivos);

    mysqli_stmt_close($stmtInactivos);

    responderError(
        'No se pudo obtener el resultado de proveedores inactivos: '
            . $error
    );
}


$filaInactivos = mysqli_fetch_assoc($resultadoInactivos);


$proveedoresInactivos = (int) (
    $filaInactivos['total'] ?? 0
);


mysqli_stmt_close($stmtInactivos);


//=====================================================
// 4. PROVEEDORES CON PRODUCTOS
//
// Tabla proveedores:
//     provedores
//
// Tabla productos:
//     producto
//
// Relación:
//     provedores.id_provedor
//              =
//     producto.id_provedor
//
// Solo contamos proveedores activos que tengan
// al menos un producto activo asociado.
//
// DISTINCT evita contar varias veces al mismo
// proveedor cuando tiene varios productos.
//=====================================================

$sqlConProductos = "
    SELECT COUNT(DISTINCT pr.id_provedor) AS total
    FROM provedores pr
    INNER JOIN producto p
        ON p.id_provedor = pr.id_provedor
    WHERE pr.id_user = ?
      AND pr.Eliminado = 0
      AND p.id_user = ?
      AND p.Eliminado = 0
      AND p.id_provedor IS NOT NULL
      AND p.id_provedor > 0
";


$stmtConProductos = mysqli_prepare(
    $conexion,
    $sqlConProductos
);


if (!$stmtConProductos) {

    responderError(
        'Error al preparar la consulta de proveedores con productos: '
            . mysqli_error($conexion)
    );
}


mysqli_stmt_bind_param(
    $stmtConProductos,
    "ii",
    $idUser,
    $idUser
);


if (!mysqli_stmt_execute($stmtConProductos)) {

    $error = mysqli_stmt_error($stmtConProductos);

    mysqli_stmt_close($stmtConProductos);

    responderError(
        'Error al obtener los proveedores con productos: ' . $error
    );
}


$resultadoConProductos = mysqli_stmt_get_result(
    $stmtConProductos
);


if (!$resultadoConProductos) {

    $error = mysqli_stmt_error($stmtConProductos);

    mysqli_stmt_close($stmtConProductos);

    responderError(
        'No se pudo obtener el resultado de proveedores con productos: '
            . $error
    );
}


$filaConProductos = mysqli_fetch_assoc(
    $resultadoConProductos
);


$proveedoresProductos = (int) (
    $filaConProductos['total'] ?? 0
);


mysqli_stmt_close($stmtConProductos);


//=====================================================
// RESPUESTA FINAL
//=====================================================

echo json_encode([

    'success' => true,

    'message' => 'KPI de proveedores obtenidos correctamente.',

    'total' => $totalProveedores,

    'activos' => $proveedoresActivos,

    'inactivos' => $proveedoresInactivos,

    'con_productos' => $proveedoresProductos

], JSON_UNESCAPED_UNICODE);

exit;
