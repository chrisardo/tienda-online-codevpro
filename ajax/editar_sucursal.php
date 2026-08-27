<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/editar_sucursal.php
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
    "mensaje" => "No se pudo actualizar la sucursal."
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

$nombre = isset($_POST["nombre"])
    ? trim($_POST["nombre"])
    : "";

//=====================================================
// VALIDAR ID
//=====================================================

if ($idSucursal <= 0) {

    $respuesta["mensaje"] = "Sucursal no válida.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

//=====================================================
// VALIDAR NOMBRE
//=====================================================

if ($nombre === "") {

    $respuesta["mensaje"] = "Debe ingresar el nombre de la sucursal.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($nombre, "UTF-8") < 2) {

    $respuesta["mensaje"] = "El nombre de la sucursal debe tener al menos 2 caracteres.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($nombre, "UTF-8") > 150) {

    $respuesta["mensaje"] = "El nombre de la sucursal no puede superar los 150 caracteres.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

//=====================================================
// NORMALIZAR NOMBRE
//=====================================================

$nombreBusqueda = mb_strtolower($nombre, "UTF-8");

//=====================================================
// VERIFICAR QUE LA SUCURSAL PERTENEZCA AL USUARIO
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

    $respuesta["mensaje"] = "La sucursal no existe o no pertenece al usuario.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_close($stmtSucursal);

//=====================================================
// VERIFICAR NOMBRE DUPLICADO
//
// Se excluye la sucursal que estamos editando.
//=====================================================

$sqlExiste = "
    SELECT id_sucursal
    FROM sucursal
    WHERE id_user = ?
      AND Eliminado = 0
      AND LOWER(nombre) = ?
      AND id_sucursal <> ?
    LIMIT 1
";

$stmtExiste = mysqli_prepare($conexion, $sqlExiste);

if (!$stmtExiste) {

    $respuesta["mensaje"] = "Error al validar el nombre de la sucursal.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param(
    $stmtExiste,
    "isi",
    $idUser,
    $nombreBusqueda,
    $idSucursal
);

mysqli_stmt_execute($stmtExiste);

$resultadoExiste = mysqli_stmt_get_result($stmtExiste);

if ($resultadoExiste && mysqli_num_rows($resultadoExiste) > 0) {

    mysqli_stmt_close($stmtExiste);

    $respuesta["mensaje"] = "Ya existe otra sucursal activa con ese nombre.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_close($stmtExiste);

//=====================================================
// ACTUALIZAR
//=====================================================

$sqlActualizar = "
    UPDATE sucursal
    SET nombre = ?
    WHERE id_sucursal = ?
      AND id_user = ?
      AND Eliminado = 0
";

$stmtActualizar = mysqli_prepare($conexion, $sqlActualizar);

if (!$stmtActualizar) {

    $respuesta["mensaje"] = "Error al preparar la actualización.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param(
    $stmtActualizar,
    "sii",
    $nombre,
    $idSucursal,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmtActualizar)) {

    $respuesta["mensaje"] = "No se pudo actualizar la sucursal.";

    mysqli_stmt_close($stmtActualizar);

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_close($stmtActualizar);

//=====================================================
// RESPUESTA
//=====================================================

$respuesta = [
    "success" => true,
    "mensaje" => "Sucursal actualizada correctamente.",
    "id_sucursal" => $idSucursal,
    "nombre" => $nombre
];

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
