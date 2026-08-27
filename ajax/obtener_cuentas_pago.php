<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_cuentas_bancarias.php
// Módulo: Pagos a Empleados
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        'mensaje' => 'No fue posible conectar con la base de datos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// CONSULTAR CUENTAS
//=====================================================

$sql = "
    SELECT
        id_cuenta_bancaria,
        nombre,
        balance
    FROM cuenta_banco
    WHERE id_user = ?
      AND Eliminado = 0
    ORDER BY nombre ASC
";

//=====================================================
// PREPARAR
//=====================================================

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al preparar la consulta de cuentas bancarias.',
        'error' => mysqli_error($conexion)
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// PARAMETRO
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al consultar las cuentas bancarias.',
        'error' => mysqli_stmt_error($stmt)
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmt);

    exit;
}

//=====================================================
// RESULTADO
//=====================================================

$resultado = mysqli_stmt_get_result($stmt);

$cuentas = [];

if ($resultado) {

    while ($fila = mysqli_fetch_assoc($resultado)) {

        $cuentas[] = [
            'id_cuenta_bancaria' => (int) $fila['id_cuenta_bancaria'],
            'nombre' => $fila['nombre'],
            'balance' => (float) $fila['balance']
        ];
    }
}

//=====================================================
// CERRAR
//=====================================================

mysqli_stmt_close($stmt);

//=====================================================
// RESPUESTA
//=====================================================

echo json_encode([
    'success' => true,
    'mensaje' => 'Cuentas bancarias obtenidas correctamente.',
    'cuentas' => $cuentas,
    'total' => count($cuentas)
], JSON_UNESCAPED_UNICODE);

exit;
