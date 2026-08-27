<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/eliminar_sucursal.php
// Módulo: Sucursales
// Sistema: Inventa
//=====================================================

header('Content-Type: application/json; charset=utf-8');

//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// RESPUESTA
//=====================================================

$respuesta = [
    "success" => false,
    "mensaje" => "No se pudo eliminar la sucursal."
];

//=====================================================
// VALIDAR USUARIO
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

if ($idUser <= 0) {

    $respuesta["mensaje"] = "Sesión no válida. Debe iniciar sesión nuevamente.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

//=====================================================
// RECIBIR ID
//=====================================================

$idSucursal = isset($_POST["id_sucursal"])
    ? (int) $_POST["id_sucursal"]
    : 0;

if ($idSucursal <= 0) {

    $respuesta["mensaje"] = "Sucursal no válida.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

//=====================================================
// VERIFICAR QUE LA SUCURSAL EXISTA
//=====================================================

$sqlSucursal = "
    SELECT id_sucursal, nombre
    FROM sucursal
    WHERE id_sucursal = ?
      AND id_user = ?
      AND Eliminado = 0
    LIMIT 1
";

$stmtSucursal = mysqli_prepare($conexion, $sqlSucursal);

if (!$stmtSucursal) {

    $respuesta["mensaje"] = "Error al preparar la consulta de la sucursal.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param(
    $stmtSucursal,
    "ii",
    $idSucursal,
    $idUser
);

mysqli_stmt_execute($stmtSucursal);

$resultadoSucursal = mysqli_stmt_get_result($stmtSucursal);

if (!$resultadoSucursal || mysqli_num_rows($resultadoSucursal) === 0) {

    mysqli_stmt_close($stmtSucursal);

    $respuesta["mensaje"] = "La sucursal no existe, ya fue eliminada o no pertenece al usuario.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

$sucursal = mysqli_fetch_assoc($resultadoSucursal);

$nombreSucursal = $sucursal["nombre"];

mysqli_stmt_close($stmtSucursal);

//=====================================================
// VERIFICAR SI TIENE PRODUCTOS ASIGNADOS
//=====================================================
//
// No impedimos la eliminación lógica.
//
// Los productos conservarán su id_sucursal,
// pero la sucursal dejará de aparecer en las
// listas activas.
//
// Esto permite conservar el historial.
//=====================================================

$sqlProductos = "
    SELECT COUNT(*) AS total
    FROM producto
    WHERE id_sucursal = ?
      AND id_user = ?
      AND Eliminado = 0
";

$stmtProductos = mysqli_prepare($conexion, $sqlProductos);

$totalProductos = 0;

if ($stmtProductos) {

    mysqli_stmt_bind_param(
        $stmtProductos,
        "ii",
        $idSucursal,
        $idUser
    );

    mysqli_stmt_execute($stmtProductos);

    $resultadoProductos = mysqli_stmt_get_result($stmtProductos);

    if ($resultadoProductos) {

        $filaProductos = mysqli_fetch_assoc($resultadoProductos);

        $totalProductos = (int) ($filaProductos["total"] ?? 0);
    }

    mysqli_stmt_close($stmtProductos);
}

//=====================================================
// ELIMINACIÓN LÓGICA
//=====================================================

$sqlEliminar = "
    UPDATE sucursal
    SET Eliminado = 1
    WHERE id_sucursal = ?
      AND id_user = ?
      AND Eliminado = 0
";

$stmtEliminar = mysqli_prepare($conexion, $sqlEliminar);

if (!$stmtEliminar) {

    $respuesta["mensaje"] = "Error al preparar la eliminación.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param(
    $stmtEliminar,
    "ii",
    $idSucursal,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmtEliminar)) {

    $respuesta["mensaje"] = "No se pudo eliminar la sucursal.";

    mysqli_stmt_close($stmtEliminar);

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

//=====================================================
// VERIFICAR ACTUALIZACIÓN
//=====================================================

$filasAfectadas = mysqli_stmt_affected_rows($stmtEliminar);

mysqli_stmt_close($stmtEliminar);

if ($filasAfectadas <= 0) {

    $respuesta["mensaje"] = "No se pudo eliminar la sucursal.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

//=====================================================
// RESPUESTA EXITOSA
//=====================================================

$respuesta = [
    "success" => true,
    "mensaje" => "Sucursal eliminada correctamente.",
    "id_sucursal" => $idSucursal,
    "nombre" => $nombreSucursal,
    "productos_asignados" => $totalProductos
];

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
