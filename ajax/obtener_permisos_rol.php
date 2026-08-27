<?php
//======================================================
// CoDevPro Technology
// Archivo: ajax/obtener_permisos_rol.php
// Módulo: Obtener permisos de un rol
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


//======================================================
// VALIDAR CONEXIÓN
//======================================================

if (!$conexion) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo establecer conexión con la base de datos.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// OBTENER USUARIO DE LA SESIÓN
//======================================================

$idUser = 0;

if (isset($_SESSION["id_user"])) {

    $idUser = (int) $_SESSION["id_user"];
} elseif (isset($_SESSION["idUser"])) {

    $idUser = (int) $_SESSION["idUser"];
} elseif (isset($_SESSION["idUsuario"])) {

    $idUser = (int) $_SESSION["idUsuario"];
}


//======================================================
// VALIDAR SESIÓN
//======================================================

if ($idUser <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La sesión ha expirado. Inicie sesión nuevamente.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// OBTENER ID DEL ROL
//======================================================

$idRol = isset($_GET["id_rol"])
    ? (int) $_GET["id_rol"]
    : 0;


//======================================================
// VALIDAR ID DEL ROL
//======================================================

if ($idRol <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se recibió un rol válido.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// VALIDAR QUE EL ROL PERTENEZCA AL USUARIO
//======================================================

$sqlRol = "
    SELECT
        id_rol,
        nombre
    FROM rol
    WHERE id_rol = ?
      AND id_user = ?
    LIMIT 1
";


$stmtRol = mysqli_prepare($conexion, $sqlRol);


if (!$stmtRol) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo preparar la consulta del rol.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


mysqli_stmt_bind_param(
    $stmtRol,
    "ii",
    $idRol,
    $idUser
);


mysqli_stmt_execute($stmtRol);


$resultadoRol = mysqli_stmt_get_result($stmtRol);


if (!$resultadoRol || mysqli_num_rows($resultadoRol) === 0) {

    mysqli_stmt_close($stmtRol);

    echo json_encode([
        "estado" => false,
        "mensaje" => "El rol seleccionado no existe o no pertenece a esta empresa.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$rol = mysqli_fetch_assoc($resultadoRol);


mysqli_stmt_close($stmtRol);


//======================================================
// OBTENER PERMISOS DEL ROL
//======================================================
//
// Se relacionan:
//
// rol
//   ↓
// permisos_rol
//   ↓
// modulos
//
// Se muestran únicamente los módulos activos
// pertenecientes al mismo usuario.
//======================================================

$sql = "
    SELECT

        m.id_modulo,

        m.nombre AS nombre_modulo,

        m.codigo AS codigo_modulo,

        m.icono,

        COALESCE(pr.ver, 0) AS ver,

        COALESCE(pr.crear, 0) AS crear,

        COALESCE(pr.editar, 0) AS editar,

        COALESCE(pr.eliminar, 0) AS eliminar

    FROM modulos AS m

    LEFT JOIN permisos_rol AS pr
        ON pr.id_modulo = m.id_modulo
        AND pr.id_rol = ?
        AND pr.id_user = ?

    WHERE m.id_user = ?

      AND m.estado = 1

    ORDER BY
        m.orden ASC,
        m.nombre ASC
";


$stmt = mysqli_prepare($conexion, $sql);


if (!$stmt) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo preparar la consulta de permisos.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// PARÁMETROS
//======================================================
//
// 1. id_rol
// 2. id_user de permisos_rol
// 3. id_user de modulos
//======================================================

mysqli_stmt_bind_param(
    $stmt,
    "iii",
    $idRol,
    $idUser,
    $idUser
);


//======================================================
// EJECUTAR
//======================================================

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudieron consultar los permisos del rol.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// OBTENER RESULTADO
//======================================================

$resultado = mysqli_stmt_get_result($stmt);


if (!$resultado) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo obtener el resultado de los permisos.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// CONSTRUIR DATA
//======================================================

$permisos = [];


while ($fila = mysqli_fetch_assoc($resultado)) {

    $permisos[] = [

        "id_modulo" => (int) $fila["id_modulo"],

        "nombre_modulo" => $fila["nombre_modulo"],

        "codigo_modulo" => $fila["codigo_modulo"],

        "icono" => !empty($fila["icono"])
            ? $fila["icono"]
            : "bi-grid",

        "ver" => (int) $fila["ver"],

        "crear" => (int) $fila["crear"],

        "editar" => (int) $fila["editar"],

        "eliminar" => (int) $fila["eliminar"]

    ];
}


mysqli_stmt_close($stmt);


//======================================================
// VALIDAR SI EXISTEN MÓDULOS
//======================================================

if (empty($permisos)) {

    echo json_encode([
        "estado" => true,
        "mensaje" => "El rol no tiene módulos disponibles.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// RESPUESTA EXITOSA
//======================================================

echo json_encode([

    "estado" => true,

    "mensaje" => "Permisos del rol cargados correctamente.",

    "id_rol" => $idRol,

    "nombre_rol" => $rol["nombre"],

    "total" => count($permisos),

    "data" => $permisos

], JSON_UNESCAPED_UNICODE);

exit;
