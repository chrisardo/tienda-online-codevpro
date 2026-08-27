<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_kpis_notificaciones.php
// Módulo: Notificaciones
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

header("Content-Type: application/json; charset=utf-8");

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");


//=====================================================
// CONEXIÓN
//=====================================================

require_once __DIR__ . "/../controladores/conexion.php";


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !($conexion instanceof mysqli)
) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "estado"  => false,
        "mensaje" => "No se pudo establecer la conexión con la base de datos.",
        "kpis"    => [
            "total_notificaciones" => 0,
            "no_leidas"            => 0,
            "leidas"               => 0,
            "notificaciones_hoy"   => 0
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// CONFIGURAR UTF-8
//=====================================================

$conexion->set_charset("utf8mb4");


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;


if ($idUser <= 0) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "estado"  => false,
        "mensaje" => "La sesión de usuario no es válida.",
        "kpis"    => [
            "total_notificaciones" => 0,
            "no_leidas"            => 0,
            "leidas"               => 0,
            "notificaciones_hoy"   => 0
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// CONSULTA KPI
//
// IMPORTANTE:
// notificaciones_cliente no tiene id_user.
//
// La relación se realiza mediante:
//
// notificaciones_cliente.idCliente
//              ↓
// clientes.idCliente
//              ↓
// clientes.id_user
//
// También se excluyen notificaciones eliminadas.
//=====================================================

$sql = "

    SELECT

        COUNT(*) AS total_notificaciones,

        COALESCE(
            SUM(
                CASE
                    WHEN COALESCE(n.leido, 0) = 0
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS no_leidas,

        COALESCE(
            SUM(
                CASE
                    WHEN COALESCE(n.leido, 0) = 1
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS leidas,

        COALESCE(
            SUM(
                CASE
                    WHEN DATE(n.fecha) = CURDATE()
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS notificaciones_hoy

    FROM notificaciones_cliente n

    INNER JOIN clientes c
        ON c.idCliente = n.idCliente

    WHERE c.id_user = ?

    AND COALESCE(c.Eliminado, 0) = 0

    AND COALESCE(n.Eliminado, 0) = 0

";


//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "estado"  => false,
        "mensaje" => "No se pudo preparar la consulta de KPIs.",
        "kpis"    => [
            "total_notificaciones" => 0,
            "no_leidas"            => 0,
            "leidas"               => 0,
            "notificaciones_hoy"   => 0
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// BIND
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

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "estado"  => false,
        "mensaje" => "No se pudieron obtener los KPIs.",
        "kpis"    => [
            "total_notificaciones" => 0,
            "no_leidas"            => 0,
            "leidas"               => 0,
            "notificaciones_hoy"   => 0
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// RESULTADO
//=====================================================

$resultado =
    mysqli_stmt_get_result($stmt);


if (!$resultado) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "estado"  => false,
        "mensaje" => "No se pudo obtener el resultado de los KPIs.",
        "kpis"    => [
            "total_notificaciones" => 0,
            "no_leidas"            => 0,
            "leidas"               => 0,
            "notificaciones_hoy"   => 0
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// DATOS
//=====================================================

$data =
    mysqli_fetch_assoc($resultado);


//=====================================================
// NORMALIZAR VALORES
//=====================================================

$total =
    isset($data["total_notificaciones"])
    ? (int) $data["total_notificaciones"]
    : 0;


$noLeidas =
    isset($data["no_leidas"])
    ? (int) $data["no_leidas"]
    : 0;


$leidas =
    isset($data["leidas"])
    ? (int) $data["leidas"]
    : 0;


$hoy =
    isset($data["notificaciones_hoy"])
    ? (int) $data["notificaciones_hoy"]
    : 0;


//=====================================================
// RESPUESTA
//=====================================================

echo json_encode([

    "success" => true,

    "estado" => true,

    "mensaje" => "KPIs obtenidos correctamente.",

    "kpis" => [

        "total_notificaciones" =>
        $total,

        "no_leidas" =>
        $noLeidas,

        "leidas" =>
        $leidas,

        "notificaciones_hoy" =>
        $hoy,

        // Alias adicionales
        // por compatibilidad.

        "total" =>
        $total,

        "noLeidas" =>
        $noLeidas,

        "hoy" =>
        $hoy

    ]

], JSON_UNESCAPED_UNICODE);


//=====================================================
// CERRAR
//=====================================================

mysqli_stmt_close($stmt);

mysqli_close($conexion);
