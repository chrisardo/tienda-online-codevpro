<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_marcas_ofertas.php
// Módulo: Ofertas y Descuentos
// Sistema: Inventa
//=====================================================

session_start();

//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// VALIDAR SESIÓN
//=====================================================

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "success" => false,
        "mensaje" => "Sesión no válida."
    ], JSON_UNESCAPED_UNICODE);

    exit();
}


//=====================================================
// ID USUARIO
//=====================================================

$idUser = (int)$_SESSION["idUser"];


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!$conexion) {

    echo json_encode([
        "success" => false,
        "mensaje" => "No se pudo establecer la conexión con la base de datos."
    ], JSON_UNESCAPED_UNICODE);

    exit();
}


//=====================================================
// CONSULTAR MARCAS
//=====================================================

$sql = "
    SELECT
        id_marca,
        nombre
    FROM marcas
    WHERE
        Eliminado = 0
        AND id_user = ?
    ORDER BY nombre ASC
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


//=====================================================
// VALIDAR PREPARACIÓN
//=====================================================

if (!$stmt) {

    echo json_encode([
        "success" => false,
        "mensaje" => "No se pudo preparar la consulta de marcas.",
        "error" => mysqli_error($conexion)
    ], JSON_UNESCAPED_UNICODE);

    exit();
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
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "success" => false,
        "mensaje" => "No se pudo ejecutar la consulta de marcas.",
        "error" => mysqli_stmt_error($stmt)
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmt);

    exit();
}


//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado =
    mysqli_stmt_get_result($stmt);


//=====================================================
// VALIDAR RESULTADO
//=====================================================

if (!$resultado) {

    echo json_encode([
        "success" => false,
        "mensaje" => "No se pudo obtener el resultado de las marcas."
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmt);

    exit();
}


//=====================================================
// CONSTRUIR ARRAY
//=====================================================

$marcas = [];


while (
    $fila =
    mysqli_fetch_assoc($resultado)
) {

    $marcas[] = [

        "id_marca" =>
        (int)$fila["id_marca"],

        "nombre" =>
        $fila["nombre"]

    ];
}


//=====================================================
// CERRAR
//=====================================================

mysqli_stmt_close($stmt);


//=====================================================
// RESPUESTA
//=====================================================

echo json_encode([

    "success" => true,

    "marcas" => $marcas

], JSON_UNESCAPED_UNICODE);

exit();
