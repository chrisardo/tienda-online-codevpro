<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/registrar_sucursal.php
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
    "mensaje" => "No se pudo registrar la sucursal."
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
// RECIBIR DATOS
//=====================================================

$nombre = isset($_POST["nombre"])
    ? trim($_POST["nombre"])
    : "";

//=====================================================
// VALIDAR NOMBRE
//=====================================================

if ($nombre === "") {

    $respuesta["mensaje"] = "Debe ingresar el nombre de la sucursal.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

//=====================================================
// LONGITUD
//=====================================================

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
// NORMALIZAR PARA COMPARACIÓN
//=====================================================

$nombreBusqueda = mb_strtolower($nombre, "UTF-8");

//=====================================================
// VERIFICAR SI YA EXISTE
//
// Se verifica únicamente dentro del usuario actual.
// Además, solo se consideran sucursales activas.
//=====================================================

$sqlExiste = "
    SELECT id_sucursal, nombre
    FROM sucursal
    WHERE id_user = ?
      AND Eliminado = 0
      AND LOWER(nombre) = ?
    LIMIT 1
";

$stmtExiste = mysqli_prepare($conexion, $sqlExiste);

if (!$stmtExiste) {

    $respuesta["mensaje"] = "Error al preparar la consulta de validación.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param(
    $stmtExiste,
    "is",
    $idUser,
    $nombreBusqueda
);

mysqli_stmt_execute($stmtExiste);

$resultadoExiste = mysqli_stmt_get_result($stmtExiste);

if ($resultadoExiste && mysqli_num_rows($resultadoExiste) > 0) {

    mysqli_stmt_close($stmtExiste);

    $respuesta["mensaje"] = "Ya existe una sucursal activa con ese nombre.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_close($stmtExiste);

//=====================================================
// INSERTAR SUCURSAL
//=====================================================

$sqlInsertar = "
    INSERT INTO sucursal
    (
        nombre,
        id_user,
        Eliminado
    )
    VALUES
    (
        ?,
        ?,
        0
    )
";

$stmtInsertar = mysqli_prepare($conexion, $sqlInsertar);

if (!$stmtInsertar) {

    $respuesta["mensaje"] = "Error al preparar el registro de la sucursal.";

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param(
    $stmtInsertar,
    "si",
    $nombre,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmtInsertar)) {

    $respuesta["mensaje"] = "No se pudo registrar la sucursal.";

    mysqli_stmt_close($stmtInsertar);

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

//=====================================================
// ID GENERADO
//=====================================================

$idSucursal = mysqli_insert_id($conexion);

mysqli_stmt_close($stmtInsertar);

//=====================================================
// RESPUESTA EXITOSA
//=====================================================

$respuesta = [
    "success" => true,
    "mensaje" => "Sucursal registrada correctamente.",
    "id_sucursal" => $idSucursal,
    "nombre" => $nombre
];

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
