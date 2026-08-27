<?php
//======================================================
// CoDevPro Technology
// Archivo: ajax/adm_obtener_provincias.php
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
// OBTENER ID DEL DEPARTAMENTO
//======================================================
// El JavaScript envía:
// ajax/obtener_provincias.php?id_departamento=1
//======================================================

$idDepartamento = isset($_GET["id_departamento"])
    ? (int) $_GET["id_departamento"]
    : 0;


//======================================================
// VALIDAR ID DEPARTAMENTO
//======================================================

if ($idDepartamento <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Debe seleccionar un departamento válido.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// VERIFICAR QUE EL DEPARTAMENTO PERTENEZCA
// A UN PAÍS DEL USUARIO
//======================================================

$sqlDepartamento = "
    SELECT
        d.id_departamento
    FROM departamento d
    INNER JOIN pais p
        ON p.id_pais = d.id_pais
    WHERE d.id_departamento = ?
      AND d.Eliminado = 0
      AND p.id_user = ?
      AND p.Eliminado = 0
    LIMIT 1
";

$stmtDepartamento = mysqli_prepare(
    $conexion,
    $sqlDepartamento
);

if (!$stmtDepartamento) {

    http_response_code(500);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo validar el departamento.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


mysqli_stmt_bind_param(
    $stmtDepartamento,
    "ii",
    $idDepartamento,
    $idUser
);


mysqli_stmt_execute($stmtDepartamento);

$resultadoDepartamento = mysqli_stmt_get_result(
    $stmtDepartamento
);


if (
    !$resultadoDepartamento ||
    mysqli_num_rows($resultadoDepartamento) === 0
) {

    mysqli_stmt_close($stmtDepartamento);

    echo json_encode([
        "estado" => false,
        "mensaje" => "El departamento seleccionado no es válido.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


mysqli_stmt_close($stmtDepartamento);


//======================================================
// OBTENER PROVINCIAS
//======================================================

$sql = "
    SELECT
        id_provincia,
        nombre
    FROM provincia
    WHERE id_departamento = ?
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
        "mensaje" => "No se pudo preparar la consulta de provincias.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idDepartamento
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudieron obtener las provincias.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$resultado = mysqli_stmt_get_result($stmt);

$provincias = [];


if ($resultado) {

    while ($fila = mysqli_fetch_assoc($resultado)) {

        $provincias[] = [
            "id_provincia" => (int) $fila["id_provincia"],
            "nombre" => $fila["nombre"]
        ];
    }
}


mysqli_stmt_close($stmt);


//======================================================
// SIN RESULTADOS
//======================================================

if (empty($provincias)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se encontraron provincias para el departamento seleccionado.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// RESPUESTA EXITOSA
//======================================================

echo json_encode([
    "estado" => true,
    "mensaje" => "Provincias obtenidas correctamente.",
    "data" => $provincias
], JSON_UNESCAPED_UNICODE);

exit;
