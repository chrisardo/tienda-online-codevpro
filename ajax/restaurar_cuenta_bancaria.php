<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/restaurar_cuenta_bancaria.php
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

header(
    'Content-Type: application/json; charset=utf-8'
);

//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function respuestaJSON($datos)
{
    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
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
        'mensaje' =>
        'No fue posible conectar con la base de datos.'
    ]);
}

//=====================================================
// VALIDAR MÉTODO
//=====================================================

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {

    respuestaJSON([
        'success' => false,
        'mensaje' =>
        'Método de solicitud no permitido.'
    ]);
}

//=====================================================
// OBTENER ID DE LA CUENTA
//=====================================================

$idCuenta = isset(
    $_POST['id_cuenta_bancaria']
)
    ? (int) $_POST['id_cuenta_bancaria']
    : 0;

//=====================================================
// VALIDAR ID
//=====================================================

if ($idCuenta <= 0) {

    respuestaJSON([
        'success' => false,
        'mensaje' =>
        'El ID de la cuenta bancaria no es válido.'
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
// PREPARAR CONSULTA
//=====================================================

$stmtVerificar = mysqli_prepare(
    $conexion,
    $sqlVerificar
);

if (!$stmtVerificar) {

    respuestaJSON([
        'success' => false,
        'mensaje' =>
        'No fue posible verificar la cuenta bancaria.'
    ]);
}

//=====================================================
// VINCULAR PARÁMETROS
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

if (
    !mysqli_stmt_execute(
        $stmtVerificar
    )
) {

    $error =
        mysqli_stmt_error(
            $stmtVerificar
        );

    mysqli_stmt_close(
        $stmtVerificar
    );

    respuestaJSON([
        'success' => false,
        'mensaje' =>
        'No fue posible verificar la cuenta bancaria.',
        'error' => $error
    ]);
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultadoVerificar =
    mysqli_stmt_get_result(
        $stmtVerificar
    );

if (!$resultadoVerificar) {

    $error =
        mysqli_stmt_error(
            $stmtVerificar
        );

    mysqli_stmt_close(
        $stmtVerificar
    );

    respuestaJSON([
        'success' => false,
        'mensaje' =>
        'No fue posible obtener la información de la cuenta.',
        'error' => $error
    ]);
}

//=====================================================
// OBTENER CUENTA
//=====================================================

$cuenta =
    mysqli_fetch_assoc(
        $resultadoVerificar
    );

//=====================================================
// CERRAR RESULTADO
//=====================================================

mysqli_free_result(
    $resultadoVerificar
);

mysqli_stmt_close(
    $stmtVerificar
);

//=====================================================
// VALIDAR EXISTENCIA
//=====================================================

if (!$cuenta) {

    respuestaJSON([
        'success' => false,
        'mensaje' =>
        'La cuenta bancaria no existe o no pertenece al usuario.'
    ]);
}

//=====================================================
// OBTENER ESTADO ACTUAL
//=====================================================

$estadoActual =
    isset($cuenta['Eliminado'])
    ? (int) $cuenta['Eliminado']
    : 0;

//=====================================================
// YA ESTÁ ACTIVA
//=====================================================

if ($estadoActual === 0) {

    respuestaJSON([
        'success' => false,
        'mensaje' =>
        'La cuenta bancaria ya se encuentra activa.'
    ]);
}

//=====================================================
// RESTAURAR CUENTA
//=====================================================
//
// Eliminado = 0
//
// La cuenta vuelve a estar activa.
// No se elimina ni se crea ningún registro.
//

$sqlActualizar = "
    UPDATE cuenta_banco
    SET Eliminado = 0
    WHERE id_cuenta_bancaria = ?
      AND id_user = ?
      AND Eliminado = 1
";

//=====================================================
// PREPARAR ACTUALIZACIÓN
//=====================================================

$stmtActualizar = mysqli_prepare(
    $conexion,
    $sqlActualizar
);

if (!$stmtActualizar) {

    respuestaJSON([
        'success' => false,
        'mensaje' =>
        'No fue posible preparar la restauración de la cuenta.'
    ]);
}

//=====================================================
// VINCULAR PARÁMETROS
//=====================================================

mysqli_stmt_bind_param(
    $stmtActualizar,
    'ii',
    $idCuenta,
    $idUser
);

//=====================================================
// EJECUTAR ACTUALIZACIÓN
//=====================================================

if (
    !mysqli_stmt_execute(
        $stmtActualizar
    )
) {

    $error =
        mysqli_stmt_error(
            $stmtActualizar
        );

    mysqli_stmt_close(
        $stmtActualizar
    );

    respuestaJSON([
        'success' => false,
        'mensaje' =>
        'No fue posible restaurar la cuenta bancaria.',
        'error' => $error
    ]);
}

//=====================================================
// OBTENER FILAS AFECTADAS
//=====================================================

$filasAfectadas =
    mysqli_stmt_affected_rows(
        $stmtActualizar
    );

//=====================================================
// CERRAR STATEMENT
//=====================================================

mysqli_stmt_close(
    $stmtActualizar
);

//=====================================================
// VALIDAR ACTUALIZACIÓN
//=====================================================

if ($filasAfectadas <= 0) {

    respuestaJSON([
        'success' => false,
        'mensaje' =>
        'La cuenta bancaria no pudo ser restaurada.'
    ]);
}

//=====================================================
// RESPUESTA EXITOSA
//=====================================================

respuestaJSON([

    'success' => true,

    'mensaje' =>
    'La cuenta bancaria fue restaurada correctamente.',

    'cuenta' => [

        'id_cuenta_bancaria' =>
        $idCuenta,

        'nombre' =>
        $cuenta['nombre'],

        'balance' =>
        (float) $cuenta['balance'],

        'Eliminado' => 0

    ]

]);
