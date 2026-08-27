<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/eliminar_cuenta_bancaria.php
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
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'Método de solicitud no permitido.'
    ]);
}

//=====================================================
// OBTENER ID DE LA CUENTA
//=====================================================

$idCuenta = isset($_POST['id_cuenta_bancaria'])
    ? (int) $_POST['id_cuenta_bancaria']
    : 0;

//=====================================================
// VALIDAR ID
//=====================================================

if ($idCuenta <= 0) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'El ID de la cuenta bancaria no es válido.'
    ]);
}

//=====================================================
// VERIFICAR QUE LA CUENTA PERTENEZCA AL USUARIO
//=====================================================

$sqlVerificar = "
    SELECT
        id_cuenta_bancaria,
        nombre,
        balance,
        Eliminado
    FROM cuenta_banco
    WHERE id_cuenta_bancaria = ?
      AND id_user = ?
    LIMIT 1
";

//=====================================================
// PREPARAR
//=====================================================

$stmtVerificar = mysqli_prepare(
    $conexion,
    $sqlVerificar
);

if (!$stmtVerificar) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible verificar la cuenta bancaria.'
    ]);
}

//=====================================================
// VINCULAR
//=====================================================

mysqli_stmt_bind_param(
    $stmtVerificar,
    'ii',
    $idCuenta,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmtVerificar)) {

    $error = mysqli_stmt_error($stmtVerificar);

    mysqli_stmt_close($stmtVerificar);

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible verificar la cuenta bancaria.',
        'error' => $error
    ]);
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultadoVerificar = mysqli_stmt_get_result(
    $stmtVerificar
);

if (!$resultadoVerificar) {

    $error = mysqli_stmt_error($stmtVerificar);

    mysqli_stmt_close($stmtVerificar);

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible obtener la información de la cuenta.',
        'error' => $error
    ]);
}

//=====================================================
// VERIFICAR EXISTENCIA
//=====================================================

$cuenta = mysqli_fetch_assoc(
    $resultadoVerificar
);

mysqli_free_result(
    $resultadoVerificar
);

mysqli_stmt_close(
    $stmtVerificar
);

if (!$cuenta) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'La cuenta bancaria no existe o no pertenece al usuario.'
    ]);
}

//=====================================================
// VERIFICAR ESTADO ACTUAL
//=====================================================

$estadoActual = isset($cuenta['Eliminado'])
    ? (int) $cuenta['Eliminado']
    : 0;

//=====================================================
// YA ESTÁ INACTIVA
//=====================================================

if ($estadoActual === 1) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'La cuenta bancaria ya se encuentra inactiva.'
    ]);
}

//=====================================================
// CAMBIAR ESTADO
//=====================================================
//
// Eliminado = 1
// La cuenta NO se elimina físicamente.
//

$sqlActualizar = "
    UPDATE cuenta_banco
    SET Eliminado = 1
    WHERE id_cuenta_bancaria = ?
      AND id_user = ?
      AND Eliminado = 0
";

//=====================================================
// PREPARAR
//=====================================================

$stmtActualizar = mysqli_prepare(
    $conexion,
    $sqlActualizar
);

if (!$stmtActualizar) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible preparar la desactivación de la cuenta.'
    ]);
}

//=====================================================
// VINCULAR
//=====================================================

mysqli_stmt_bind_param(
    $stmtActualizar,
    'ii',
    $idCuenta,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmtActualizar)) {

    $error = mysqli_stmt_error($stmtActualizar);

    mysqli_stmt_close($stmtActualizar);

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible desactivar la cuenta bancaria.',
        'error' => $error
    ]);
}

//=====================================================
// VERIFICAR ACTUALIZACIÓN
//=====================================================

$filasAfectadas = mysqli_stmt_affected_rows(
    $stmtActualizar
);

mysqli_stmt_close(
    $stmtActualizar
);

if ($filasAfectadas <= 0) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'La cuenta bancaria no pudo ser desactivada.'
    ]);
}

//=====================================================
// RESPUESTA
//=====================================================

respuestaJSON([

    'success' => true,

    'mensaje' => 'La cuenta bancaria fue desactivada correctamente.',

    'cuenta' => [

        'id_cuenta_bancaria' => $idCuenta,

        'nombre' => $cuenta['nombre'],

        'balance' => (float) $cuenta['balance'],

        'Eliminado' => 1

    ]

]);
