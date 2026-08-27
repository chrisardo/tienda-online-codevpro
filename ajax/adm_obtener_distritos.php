<?php
//======================================================
// CoDevPro Technology
// Archivo: ajax/adm_obtener_distritos.php
// Módulo: Registrar Empleado
//======================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


//======================================================
// VALIDAR SESIÓN
//======================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

if ($idUser <= 0) {

    http_response_code(401);

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// OBTENER ID DE LA PROVINCIA
//======================================================
// El JavaScript envía:
//
// ajax/obtener_distritos.php?id_provincia=1
//======================================================

$idProvincia = isset($_GET["id_provincia"])
    ? (int) $_GET["id_provincia"]
    : 0;


//======================================================
// VALIDAR ID PROVINCIA
//======================================================

if ($idProvincia <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Debe seleccionar una provincia válida.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// VERIFICAR QUE LA PROVINCIA PERTENEZCA
// A UN DEPARTAMENTO DEL USUARIO
//======================================================

$sqlProvincia = "
    SELECT
        pr.id_provincia
    FROM provincia pr
    INNER JOIN departamento d
        ON d.id_departamento = pr.id_departamento
    INNER JOIN pais p
        ON p.id_pais = d.id_pais
    WHERE pr.id_provincia = ?
      AND pr.Eliminado = 0
      AND d.Eliminado = 0
      AND p.id_user = ?
      AND p.Eliminado = 0
    LIMIT 1
";


$stmtProvincia = mysqli_prepare(
    $conexion,
    $sqlProvincia
);


if (!$stmtProvincia) {

    http_response_code(500);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo validar la provincia.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


mysqli_stmt_bind_param(
    $stmtProvincia,
    "ii",
    $idProvincia,
    $idUser
);


mysqli_stmt_execute($stmtProvincia);

$resultadoProvincia = mysqli_stmt_get_result(
    $stmtProvincia
);


if (
    !$resultadoProvincia ||
    mysqli_num_rows($resultadoProvincia) === 0
) {

    mysqli_stmt_close($stmtProvincia);

    echo json_encode([
        "estado" => false,
        "mensaje" => "La provincia seleccionada no es válida.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


mysqli_stmt_close($stmtProvincia);


//======================================================
// OBTENER DISTRITOS
//======================================================

$sql = "
    SELECT
        id_distrito,
        nombre
    FROM distrito
    WHERE id_provincia = ?
      AND Eliminado = 0
    ORDER BY nombre ASC
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    http_response_code(500);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo preparar la consulta de distritos.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idProvincia
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudieron obtener los distritos.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$resultado = mysqli_stmt_get_result($stmt);

$distritos = [];


if ($resultado) {

    while ($fila = mysqli_fetch_assoc($resultado)) {

        $distritos[] = [
            "id_distrito" => (int) $fila["id_distrito"],
            "nombre" => $fila["nombre"]
        ];
    }
}


mysqli_stmt_close($stmt);


//======================================================
// SIN RESULTADOS
//======================================================

if (empty($distritos)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se encontraron distritos para la provincia seleccionada.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// RESPUESTA EXITOSA
//======================================================

echo json_encode([
    "estado" => true,
    "mensaje" => "Distritos obtenidos correctamente.",
    "data" => $distritos
], JSON_UNESCAPED_UNICODE);

exit;
