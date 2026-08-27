<?php
//======================================================
// CoDevPro Technology
// Archivo: ajax/adm_obtener_departamentos.php
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
// OBTENER ID DEL PAÍS
//======================================================
// El JavaScript envía:
// ajax/obtener_departamentos.php?id_pais=1
//
// Por eso debemos utilizar $_GET
//======================================================

$idPais = isset($_GET["id_pais"])
    ? (int) $_GET["id_pais"]
    : 0;


//======================================================
// VALIDAR ID PAÍS
//======================================================

if ($idPais <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Debe seleccionar un país válido.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// VERIFICAR QUE EL PAÍS PERTENEZCA AL USUARIO
//======================================================

$sqlPais = "
    SELECT
        id_pais
    FROM pais
    WHERE id_pais = ?
      AND id_user = ?
      AND Eliminado = 0
    LIMIT 1
";

$stmtPais = mysqli_prepare($conexion, $sqlPais);

if (!$stmtPais) {

    http_response_code(500);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo preparar la consulta del país.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

mysqli_stmt_bind_param(
    $stmtPais,
    "ii",
    $idPais,
    $idUser
);

mysqli_stmt_execute($stmtPais);

$resultadoPais = mysqli_stmt_get_result($stmtPais);

if (!$resultadoPais || mysqli_num_rows($resultadoPais) === 0) {

    mysqli_stmt_close($stmtPais);

    echo json_encode([
        "estado" => false,
        "mensaje" => "El país seleccionado no es válido.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

mysqli_stmt_close($stmtPais);


//======================================================
// OBTENER DEPARTAMENTOS
//======================================================

$sql = "
    SELECT
        id_departamento,
        nombre
    FROM departamento
    WHERE id_pais = ?
      AND Eliminado = 0
    ORDER BY nombre ASC
";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    http_response_code(500);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo preparar la consulta de departamentos.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idPais
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudieron obtener los departamentos.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$resultado = mysqli_stmt_get_result($stmt);

$departamentos = [];


if ($resultado) {

    while ($fila = mysqli_fetch_assoc($resultado)) {

        $departamentos[] = [
            "id_departamento" => (int) $fila["id_departamento"],
            "nombre" => $fila["nombre"]
        ];
    }
}


mysqli_stmt_close($stmt);


//======================================================
// VALIDAR RESULTADOS
//======================================================

if (empty($departamentos)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se encontraron departamentos para el país seleccionado.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//======================================================
// RESPUESTA EXITOSA
//======================================================

echo json_encode([
    "estado" => true,
    "mensaje" => "Departamentos obtenidos correctamente.",
    "data" => $departamentos
], JSON_UNESCAPED_UNICODE);

exit;