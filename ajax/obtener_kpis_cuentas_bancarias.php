<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_kpis_cuentas_bancarias.php
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
// CONSULTAR KPI
//=====================================================
//
// Eliminado = 0  -> Cuenta activa
// Eliminado = 1  -> Cuenta inactiva
//
// El saldo total solamente considera
// las cuentas activas.
//

$sql = "
    SELECT

        COUNT(*) AS total_cuentas,

        SUM(
            CASE
                WHEN Eliminado = 0 THEN 1
                ELSE 0
            END
        ) AS cuentas_activas,

        SUM(
            CASE
                WHEN Eliminado = 1 THEN 1
                ELSE 0
            END
        ) AS cuentas_inactivas,

        COALESCE(
            SUM(
                CASE
                    WHEN Eliminado = 0
                    THEN balance
                    ELSE 0
                END
            ),
            0
        ) AS saldo_total

    FROM cuenta_banco

    WHERE id_user = ?
";

//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible preparar la consulta de los KPI.',
        'error' => mysqli_error($conexion)
    ]);
}

//=====================================================
// VINCULAR USUARIO
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

//=====================================================
// EJECUTAR CONSULTA
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    $error = mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);

    respuestaJSON([
        'success' => false,
        'mensaje' => 'No fue posible obtener los KPI de las cuentas bancarias.',
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
        'mensaje' => 'No fue posible obtener el resultado de los KPI.',
        'error' => $error
    ]);
}

//=====================================================
// OBTENER FILA
//=====================================================

$fila = mysqli_fetch_assoc($resultado);

//=====================================================
// CERRAR RESULTADO
//=====================================================

mysqli_free_result($resultado);

mysqli_stmt_close($stmt);

//=====================================================
// VALORES SEGUROS
//=====================================================

$totalCuentas = isset($fila['total_cuentas'])
    ? (int) $fila['total_cuentas']
    : 0;

$cuentasActivas = isset($fila['cuentas_activas'])
    ? (int) $fila['cuentas_activas']
    : 0;

$cuentasInactivas = isset($fila['cuentas_inactivas'])
    ? (int) $fila['cuentas_inactivas']
    : 0;

$saldoTotal = isset($fila['saldo_total'])
    ? (float) $fila['saldo_total']
    : 0.00;

//=====================================================
// RESPUESTA FINAL
//=====================================================

respuestaJSON([

    'success' => true,

    'mensaje' => 'KPI de cuentas bancarias obtenidos correctamente.',

    'total_cuentas' => $totalCuentas,

    'cuentas_activas' => $cuentasActivas,

    'cuentas_inactivas' => $cuentasInactivas,

    'saldo_total' => $saldoTotal

]);
