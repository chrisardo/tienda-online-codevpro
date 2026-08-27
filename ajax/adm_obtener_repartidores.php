<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_obtener_repartidores.php
// Módulo: Gestión de Pedidos de Clientes
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../controladores/conexion.php";

header("Content-Type: application/json; charset=UTF-8");


//=====================================================
// OBTENER USUARIO DE LA SESIÓN
//=====================================================

$idUser = intval($_SESSION["idUser"] ?? 0);


if ($idUser <= 0) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Sesión no válida.",
        "repartidores" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// CONSULTAR REPARTIDORES
//=====================================================

$sql = "SELECT

            e.id_empleado,
            e.nombre,
            e.apellido,
            e.celular,
            e.email,
            e.estado,

            r.id_rol,
            r.nombre AS rol,
            r.id_user AS rol_id_user

        FROM empleados e

        INNER JOIN rol r
            ON r.id_rol = e.id_rol

        WHERE e.id_user = ?
        AND r.id_user = ?
        AND e.estado = 'ACTIVO'
        AND UPPER(TRIM(r.nombre)) = 'REPARTIDOR'

        ORDER BY
            e.nombre ASC,
            e.apellido ASC";


//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se pudo preparar la consulta.",
        "repartidores" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// PARÁMETROS
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idUser,
    $idUser
);


//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se pudo ejecutar la consulta.",
        "repartidores" => []
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmt);

    exit;
}


//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = mysqli_stmt_get_result($stmt);


if (!$resultado) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se pudo obtener el resultado.",
        "repartidores" => []
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmt);

    exit;
}


//=====================================================
// CONSTRUIR LISTA
//=====================================================

$repartidores = [];


while ($fila = mysqli_fetch_assoc($resultado)) {

    $nombreCompleto = trim(
        ($fila["nombre"] ?? "") .
            " " .
            ($fila["apellido"] ?? "")
    );


    $repartidores[] = [

        "id" => intval(
            $fila["id_empleado"]
        ),

        "nombre" => $nombreCompleto,

        "celular" => $fila["celular"] ?? "",

        "email" => $fila["email"] ?? "",

        "estado" => $fila["estado"] ?? "",

        "rol" => $fila["rol"] ?? ""

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

    "estado" => "ok",

    "mensaje" =>
    count($repartidores) > 0
        ? "Repartidores encontrados."
        : "No existen repartidores activos para este usuario.",

    "total" => count($repartidores),

    "repartidores" => $repartidores

], JSON_UNESCAPED_UNICODE);
