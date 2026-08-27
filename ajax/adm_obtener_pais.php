<?php
//======================================================
// CoDevPro Technology
// Archivo: ajax/adm_obtener_pais.php
// Módulo: Lista de Empleados / Editar Empleado
//======================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");

//======================================================
// RESPUESTA BASE
//======================================================

$respuesta = [
    "estado" => false,
    "mensaje" => "",
    "data" => []
];

//======================================================
// VALIDAR MÉTODO
//======================================================

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    $respuesta["mensaje"] =
        "Método de solicitud no permitido.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// CONEXIÓN
//======================================================

require_once "../controladores/conexion.php";

//======================================================
// VALIDAR CONEXIÓN
//======================================================

if (!isset($conexion) || !$conexion) {

    http_response_code(500);

    $respuesta["mensaje"] =
        "No se pudo establecer la conexión con la base de datos.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// OBTENER USUARIO DE LA SESIÓN
//======================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

//======================================================
// VALIDAR SESIÓN
//======================================================

if ($idUser <= 0) {

    http_response_code(401);

    $respuesta["mensaje"] =
        "Sesión no válida.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// CONSULTAR PAÍSES
//======================================================
//
// Se obtienen únicamente los países pertenecientes
// al usuario actual.
//
//======================================================

$sql = "
    SELECT
        id_pais,
        nombre
    FROM pais
    WHERE id_user = ?
      AND Eliminado = 0
    ORDER BY nombre ASC
";

//======================================================
// PREPARAR CONSULTA
//======================================================

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

if (!$stmt) {

    http_response_code(500);

    $respuesta["mensaje"] =
        "No se pudo preparar la consulta de países.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// VINCULAR PARÁMETROS
//======================================================

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

//======================================================
// EJECUTAR CONSULTA
//======================================================

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    $respuesta["mensaje"] =
        "No se pudieron obtener los países.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// OBTENER RESULTADO
//======================================================

$resultado = mysqli_stmt_get_result($stmt);

$paises = [];

//======================================================
// RECORRER PAÍSES
//======================================================

if ($resultado) {

    while ($fila = mysqli_fetch_assoc($resultado)) {

        $paises[] = [
            "id_pais" => (int) $fila["id_pais"],
            "nombre"  => $fila["nombre"]
        ];
    }
}

//======================================================
// CERRAR STATEMENT
//======================================================

mysqli_stmt_close($stmt);

//======================================================
// SIN PAÍSES
//======================================================

if (empty($paises)) {

    $respuesta["estado"] = false;

    $respuesta["mensaje"] =
        "No se encontraron países disponibles.";

    $respuesta["data"] = [];

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// RESPUESTA EXITOSA
//======================================================

$respuesta["estado"] = true;

$respuesta["mensaje"] =
    "Países obtenidos correctamente.";

$respuesta["data"] = $paises;

//======================================================
// SALIDA JSON
//======================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
